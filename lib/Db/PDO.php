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
    /** @var EasyDB $db */
    private $db;

    public function __construct(BasePDO $pdo)
    {
        $this->db = new EasyDB($pdo);
    }

    /**
     * @param string $provider
     * @param string $publicKey
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws GossamerException
     */
    public function appendKey($provider, $publicKey, array $meta = array(), $hash = '')
    {
        $providerId = $this->getProviderId($provider);

        $inserts = [
            'provider' => $providerId,
            'publickey' => $publicKey,
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
        return $this->db->commit();
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
        return $this->db->commit();
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
}
