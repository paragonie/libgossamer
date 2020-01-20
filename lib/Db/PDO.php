<?php
namespace ParagonIE\Gossamer\Db;

use ParagonIE\EasyDB\EasyDB;
use ParagonIE\Gossamer\DbInterface;
use ParagonIE\Gossamer\GossamerException;
use PDO as BasePDO;

/**
 * Class PDO
 *
 * Actually wraps EasyDB so we don't have to reinvent the wheel.
 *
 * @package ParagonIE\Gossamer\Db
 */
class PDO implements DbInterface
{
    /** @var ?callable $attestCallback */
    private $attestCallback = null;

    /** @var EasyDB $db */
    private $db;

    public function __construct(BasePDO $pdo)
    {
        $this->db = new EasyDB($pdo);
    }

    /**
     * @return string
     */
    public function getCheckpointHash()
    {
        return (string) $this->db->cell(
            "SELECT lasthash FROM gossamer_meta WHERE version = ?",
            self::GOSSAMER_PROTOCOL_VERSION
        );
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function updateMeta($hash = '')
    {
        if (!$hash) {
            return false;
        }
        if ($this->db->exists(
            "SELECT count(*) FROM gossamer_meta WHERE version = ?",
            self::GOSSAMER_PROTOCOL_VERSION
        )) {
            $this->db->update(
                self::TABLE_META,
                array(
                    'lasthash' => $hash
                ),
                array('version' => self::GOSSAMER_PROTOCOL_VERSION)
            );
        } else {
            $this->db->insert(
                self::TABLE_META,
                array(
                    'lasthash' => $hash,
                    'version' => self::GOSSAMER_PROTOCOL_VERSION
                )
            );
        }
        return true;
    }

    /**
     * @param string $provider
     * @param string $publicKey
     * @param bool $limited
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws GossamerException
     */
    public function appendKey($provider, $publicKey, $limited = false, array $meta = array(), $hash = '')
    {
        $providerId = $this->getProviderId($provider);
        if ($limited) {
            // Get non-limited keys
            $existingKeys = $this->getPublicKeysForProvider($provider, false);
            if (count($existingKeys) < 1) {
                throw new GossamerException(
                    'Attempting to append a limited key without a pre-existing non-limited key.'
                );
            }
        }

        $inserts = [
            'provider' => $providerId,
            'publickey' => $publicKey,
            'limited' => $limited,
            'metadata' => json_encode($meta)
        ];
        if (!empty($hash)) {
            $inserts['ledgerhash'] = $hash;
        }

        $this->db->beginTransaction();
        $this->db->insert(
            self::TABLE_PUBLIC_KEYS,
            $inserts
        );
        $this->updateMeta($hash);
        return $this->db->commit();
    }

    /**
     * @param string $provider
     * @param string $publicKey
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws GossamerException
     */
    public function revokeKey($provider, $publicKey, array $meta = array(), $hash = '')
    {
        $providerId = $this->getProviderId($provider);

        $updates = ['revoked' => true];
        if (!empty($hash)) {
            $updates['revokehash'] = $hash;
        }

        $this->db->beginTransaction();
        $this->db->update(
            self::TABLE_PUBLIC_KEYS,
            $updates,
            [
                'provider' => $providerId,
                'publickey' => $publicKey
            ]
        );
        $this->updateMeta($hash);
        return $this->db->commit();
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function setAttestCallback($callback)
    {
        $this->attestCallback = $callback;
        return $this;
    }

    /**
     * @param string $provider
     * @param string $package
     * @param string $release
     * @param string $attestation
     * @param array $meta
     * @param string $hash
     * @return bool
     */
    public function attestUpdate(
        $provider,
        $package,
        $release,
        $attestation,
        array $meta = array(),
        $hash = ''
    ) {
        if (is_callable($this->attestCallback)) {
            $cb = $this->attestCallback;
            return (bool) $cb($provider, $package, $release, $attestation, $meta, $hash);
        }
        return false;
    }

    /**
     * @param string $provider
     * @param string $package
     * @param string $publicKey
     * @param string $release
     * @param string $signature
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws GossamerException
     */
    public function appendUpdate(
        $provider,
        $package,
        $publicKey,
        $release,
        $signature,
        array $meta = array(),
        $hash = ''
    ) {
        $providerId = $this->getProviderId($provider);
        $packageId = $this->getPackageId($package, $providerId);
        $publicKeyId = $this->getPublicKeyId($publicKey, $providerId);

        $inserts = [
            'package' => $packageId,
            'version' => $release,
            'publickey' => $publicKeyId,
            'signature' => $signature,
            'metadata' => json_encode($meta)
        ];
        if (!empty($hash)) {
            $inserts['ledgerhash'] = $hash;
        }

        $this->db->beginTransaction();
        $this->db->insert(
            self::TABLE_PACKAGE_RELEASES,
            $inserts
        );
        $this->updateMeta($hash);
        return $this->db->commit();
    }

    /**
     * @param string $provider
     * @param string $package
     * @param string $publicKey
     * @param string $release
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws GossamerException
     */
    public function revokeUpdate(
        $provider,
        $package,
        $publicKey,
        $release,
        array $meta = array(),
        $hash = ''
    ) {
        $providerId = $this->getProviderId($provider);
        $packageId = $this->getPackageId($package, $providerId);

        $updates = ['revoked' => true];
        if (!empty($hash)) {
            $updates['revokehash'] = $hash;
        }

        $this->db->beginTransaction();
        $this->db->update(
            self::TABLE_PACKAGE_RELEASES,
            $updates,
            [
                'version' => $release,
                'package' => $packageId
            ]
        );
        $this->updateMeta($hash);
        return $this->db->commit();
    }

    /**
     * @param string $providerName
     * @return bool
     */
    public function providerExists($providerName)
    {
        return $this->db->exists(
            "SELECT count(id) FROM " . self::TABLE_PROVIDERS . " WHERE name = ?",
            $providerName
        );
    }

    /**
     * @param string $providerName
     * @param ?bool $limited
     * @return array<array-key, string>
     */
    public function getPublicKeysForProvider($providerName, $limited = null)
    {
        $suffix = '';
        if (!is_null($limited)) {
            $suffix = $limited ? ' AND pk.limited' : ' AND NOT pk.limited';
        }
        /** @var array<array-key, string> $pubKeys */
        $pubKeys = $this->db->col(
            "SELECT pk.publickey FROM gossamer_provider_publickeys pk
             JOIN gossamer_providers prov ON pk.provider = prov.id
             WHERE prov.name = ? AND NOT pk.revoked" . $suffix,
            0,
            $providerName
        );
        if (empty($pubKeys)) {
            return array();
        }
        return $pubKeys;
    }

    /**
     * @param string $packageName
     * @param int $providerId
     * @return int
     * @throws GossamerException
     */
    public function getPackageId($packageName, $providerId)
    {
        try {
            /** @var int|bool $packageId */
            $packageId = $this->db->cell(
                "SELECT id FROM " . self::TABLE_PACKAGES . " WHERE name = ? AND provider = ?",
                $packageName,
                $providerId
            );
            if (!$packageId) {
                $this->db->beginTransaction();
                /** @var int $packageId */
                $packageId = $this->db->insertGet(
                    self::TABLE_PACKAGES,
                    ['name' => $packageName, 'provider' => $providerId],
                    'id'
                );
                $this->db->commit();
            }
        } catch (\Exception $ex) {
            throw new GossamerException("Database error: " . $ex->getMessage(), 0, $ex);
        }
        return (int) $packageId;
    }

    /**
     * @param string $providerName
     * @return int
     * @throws GossamerException
     */
    public function getProviderId($providerName)
    {
        try {
            /** @var int|bool $providerId */
            $providerId = $this->db->cell(
                "SELECT id FROM " . self::TABLE_PROVIDERS . " WHERE name = ?",
                $providerName
            );
            if (!$providerId) {
                $this->db->beginTransaction();
                /** @var int $providerId */
                $providerId = $this->db->insertGet(
                    self::TABLE_PROVIDERS,
                    ['name' => $providerName],
                    'id'
                );
                $this->db->commit();
            }
        } catch (\Exception $ex) {
            throw new GossamerException("Database error: " . $ex->getMessage(), 0, $ex);
        }
        return (int) $providerId;
    }

    /**
     * @param string $publicKey
     * @param int $providerId
     * @return int
     * @throws GossamerException
     */
    public function getPublicKeyId($publicKey, $providerId)
    {
        /** @var int|bool $publicKeyId */
        $publicKeyId = $this->db->cell(
            "SELECT id FROM " . self::TABLE_PUBLIC_KEYS . " WHERE publickey = ? AND provider = ?",
            $publicKey,
            $providerId
        );
        if (!$publicKeyId) {
            throw new GossamerException(
                sprintf('Invalid public key %s for provider %d', $publicKey, $providerId)
            );
        }
        return (int) $publicKeyId;
    }

    /**
     * Is the "limited" flag set to TRUE on this key?
     *
     * @param string $providerName
     * @param string $publicKey
     * @return bool
     */
    public function isKeyLimited($providerName, $publicKey)
    {
        return (bool) $this->db->cell(
            "SELECT pk.limited FROM gossamer_provider_publickeys pk " .
            "JOIN gossamer_providers prov ON pk.provider = prov.id " .
            "WHERE prov.name = ? AND NOT pk.revoked AND pk.publickey = ?",
            $providerName,
            $publicKey
        );
    }
}
