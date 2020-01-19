<?php
namespace ParagonIE\Gossamer\Db;
use wpdb;
use ParagonIE\Gossamer\DbInterface;
use ParagonIE\Gossamer\GossamerException;

/**
 * Class Wp
 * 
 * An implementation of `\ParagonIE\Gossamer\DbInterface` that wraps `\wpdb`
 * 
 * @package ParagonIE\Gossamer\Db
 */
class Wp implements DbInterface
{
    /** @var ?callable $attestCallback */
    private $attestCallback = null;

    /** @var \wpdb */
    private $db;

    function __construct(wpdb $db)
    {
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function getCheckpointHash()
    {
        $query = $this->db->prepare(
            'SELECT lasthash FROM gossamer_meta WHERE version = ?',
            self::GOSSAMER_PROTOCOL_VERSION
        );

        $results = $this->db->get_col($query);

        return (string) $results[0];
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

        $exists = $this->db->get_var(
            'SELECT COUNT(*) FROM gossamer_meta WHERE version = ' .
            self::GOSSAMER_PROTOCOL_VERSION
        );

        if ($exists) {
            $count = $this->db->update(
                self::TABLE_META,
                array(
                    'lasthash' => $hash
                ),
                array(
                    'version' => self::GOSSAMER_PROTOCOL_VERSION
                )
            );
        } else {
            $count = $this->db->insert(
                self::TABLE_META,
                array(
                    'lasthash' => $hash,
                    'version'  => self::GOSSAMER_PROTOCOL_VERSION
                )
            );
        }

        return (int) $count >= 1;
    }

    /**
     * @param string $provider
     * @param string $publicKey
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws \ParagonIE\Gossamer\GossamerException
     */
    public function appendKey($provider, $publicKey, array $meta = array(), $hash = '')
    {
        $providerId = $this->getProviderId($provider);

        $inserts = array(
            'provider'  => $providerId,
            'publickey' => $publicKey,
            'metadata'  => json_encode($meta)
        );
        
        if (!empty($hash)) {
            $inserts['ledgerhash'] = $hash;
        }

        $inserted = $this->db->insert(self::TABLE_PUBLIC_KEYS, $inserts);
        $updated = $this->updateMeta($hash);

        return $inserted && $updated;
    }

    /**
     * @param string $provider
     * @param string $publicKey
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws \ParagonIE\Gossamer\GossamerException
     */
    public function revokeKey($provider, $publicKey, array $meta = array(), $hash = '')
    {
        $providerId = $this->getProviderId($provider);

        $updates = array('revoked' => true);
        
        if (!empty($hash)) {
            $updates['revokehash'] = $hash;
        }

        $updatedKey = $this->db->update(self::TABLE_PUBLIC_KEYS, $updates, array(
            'provider'  => $providerId,
            'publickey' => $publicKey
        ));

        $updatedHash = $this->updateMeta($hash);

        return $updatedKey && $updatedHash;
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
     * @throws \ParagonIE\Gossamer\GossamerException
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

        $inserts = array(
            'package' => $packageId,
            'version' => $release,
            'publickey' => $publicKeyId,
            'signature' => $signature,
            'metadata' => json_encode($meta)
        );
        
        if (!empty($hash)) {
            $inserts['ledgerhash'] = $hash;
        }

        $inserted = $this->db->insert(self::TABLE_PACKAGE_RELEASES, $inserts);
        $updated = $this->updateMeta($hash);

        return $inserted && $updated;
    }

    /**
     * @param string $provider
     * @param string $package
     * @param string $publicKey
     * @param string $release
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws \ParagonIE\Gossamer\GossamerException
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

        $updates = array('revoked' => true);
        
        if (!empty($hash)) {
            $updates['revokehash'] = $hash;
        }

        $updatedPackage = $this->db->update(
            self::TABLE_PACKAGE_RELEASES,
            $updates,
            array(
                'version' => $release,
                'package' => $packageId
            )
        );

        $updatedHash = $this->updateMeta($hash);

        return $updatedPackage && $updatedHash;
    }

    /**
     * @param string $providerName
     * @return bool
     */
    public function providerExists($providerName)
    {
        $query = $this->db->prepare('SELECT COUNT(id) FROM ? WHERE name = ?', array(
            self::TABLE_PROVIDERS,
            $providerName
        ));

        $count = $this->db->get_var($query);

        return (int) $count >= 1;
    }

    /**
     * @param string $providerName
     * @return array<array-key, string>
     */
    public function getPublicKeysForProvider($providerName)
    {
        $query = $this->db->prepare(
            "SELECT pk.publickey FROM gossamer_provider_publickeys pk " .
            "JOIN gossamer_providers prov ON pk.provider = prov.id " .
            "WHERE prov.name = ? AND NOT pk.revoked",
            $providerName
        );
        
        /** @var array<array-key, string> $pubKeys */
        $pubKeys = $this->db->get_col($query);
        
        if (empty($pubKeys)) {
            return array();
        }
        
        return $pubKeys;
    }

    /**
     * @param string $packageName
     * @param int $providerId
     * @return int
     * @throws \ParagonIE\Gossamer\GossamerException
     */
    public function getPackageId($packageName, $providerId)
    {
        $query = $this->db->prepare('SELECT id FROM ? WHERE name = ? AND provider = ?', array(
            $packageName,
            $providerId
        ));

        $packageId = $this->db->get_col($query);

        if (!$packageId) {
            $inserted = $this->db->insert(self::TABLE_PACKAGES, array(
                'name'     => $packageName,
                'provider' => $providerId
            ));

            if (!$inserted) {
                throw new GossamerException('Database error: Could not insert new package.');
            }

            $packageId = $this->db->insert_id;
        } else {
            /** @var int */
            $packageId = $packageId[0];
        }

        return (int) $packageId;
    }

    /**
     * @param string $providerName
     * @return int
     * @throws \ParagonIE\Gossamer\GossamerException
     */
    public function getProviderId($providerName)
    {
        $query = $this->db->prepare('SELECT id FROM ? WHERE name = ?', array(
            self::TABLE_PROVIDERS,
            $providerName
        ));

        $providerId = $this->db->get_col($query);

        if (!$providerId) {
            $inserted = $this->db->insert(self::TABLE_PROVIDERS, array(
                'name' => $providerName
            ));

            if (!$inserted) {
                throw new GossamerException('Database error: Could not insert new provider.');
            }

            $providerId = $this->db->insert_id;
        } else {
            /** @var int */
            $providerId = $providerId[0];
        }

        return (int) $providerId;
    }

    /**
     * @param string $publicKey
     * @param int $providerId
     * @return int
     * @throws \ParagonIE\Gossamer\GossamerException
     */
    public function getPublicKeyId($publicKey, $providerId)
    {
        $query = $this->db->prepare('SELECT id FROM ? WHERE publickey = ? AND provider = ?', array(
            self::TABLE_PUBLIC_KEYS,
            $publicKey,
            $providerId
        ));

        $publicKeyId = $this->db->get_col($query);

        if (!$publicKeyId) {
            throw new GossamerException("Invalid public key $publicKey for provider $providerId");
        }

        return (int) $publicKeyId[0];
    }
}
