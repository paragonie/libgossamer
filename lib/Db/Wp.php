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
     * Get the last successful checkpoint hash from the metadata table.
     *
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
     * Update the metadata table with the last successful checkpoint hash.
     *
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
     * Perform an AppendKey action against this database.
     *
     * @param string $provider
     * @param string $publicKey
     * @param bool $limited
     * @param string $purpose
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws GossamerException
     */
    public function appendKey($provider, $publicKey, $limited = false, $purpose = '', array $meta = array(), $hash = '')
    {
        $providerId = $this->getProviderId($provider);
        if ($limited) {
            // Get non-limited keys
            $existingKeys = $this->getPublicKeysForProvider($provider, false, '');
            if (count($existingKeys) < 1) {
                throw new GossamerException(
                    'Attempting to append a limited key without a pre-existing non-limited key.'
                );
            }
        }

        $inserts = array(
            'provider'  => $providerId,
            'publickey' => $publicKey,
            'limited' => $limited,
            'purpose' => $purpose,
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
     * Perform a RevokeKey action against this database.
     *
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
     * Perform an AttestUpdate action against this database.
     *
     * Does nothing unless setAttestCallback() has been called.
     *
     * @param string $provider
     * @param string $package
     * @param string $release
     * @param string $attestor
     * @param string $attestation
     * @param array $meta
     * @param string $hash
     * @return bool
     */
    public function attestUpdate(
        $provider,
        $package,
        $release,
        $attestor,
        $attestation,
        array $meta = array(),
        $hash = ''
    ) {
        $attestorId = $this->getProviderId($attestor);
        $releaseId = $this->getRelease($provider, $package, $release);
        $inserted = $this->db->insert(
            self::TABLE_ATTESTATIONS,
            array(
                'release_id' => $releaseId,
                'attestor' => $attestorId,
                'attestation' => $attestation,
                'ledgerhash' => $hash,
                'metadata' => json_encode($meta)
            )
        );
        $updated = $this->updateMeta($hash);
        return $inserted && $updated;
    }

    /**
     * Perform an AppendUpdate action against this database.
     *
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
        // Security: getPublicKeyId() must throw if the public key belongs to a different provider
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
     * Perform a RevokeUpdate action against this database.
     *
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
     * Does this provider exist in this database?
     *
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
     * Get a list of non-revoked public keys for this provider.
     *
     * If you pass $limited as TRUE, this method only returns limited keys.
     * If you pass $limited as FALSE, this method only returns non-limited keys.
     * If you pass $limited as NULL (default), it returns both kinds.
     *
     * If you pass $purpose as an empty string, this method disregards purpose.
     * If you pass $purpose as a non-empty string, this method only returns keys that match that purpose.
     * If you pass $purpose as NULL (default), it only returns keys without a purpose.
     *
     * @param string $providerName
     * @param ?bool $limited
     * @param ?string $purpose
     * @return array<array-key, string>
     */
    public function getPublicKeysForProvider($providerName, $limited = null, $purpose = null)
    {
        $suffix = '';
        if (!is_null($limited)) {
            $suffix = $limited ? ' AND pk.limited' : ' AND NOT pk.limited';
        }

        $usePurpose = false;
        if (is_null($purpose)) {
            $suffix .= ' AND pk.purpose IS NULL';
        } elseif ($purpose !== '') {
            $suffix .= ' AND pk.purpose = ?';
            $usePurpose = true;
        }
        $queryString = "SELECT pk.publickey FROM gossamer_provider_publickeys pk " .
            "JOIN gossamer_providers prov ON pk.provider = prov.id " .
            "WHERE prov.name = ? AND NOT pk.revoked" . $suffix;

        if ($usePurpose) {
            $query = $this->db->prepare($queryString, $providerName, $purpose);
        } else {
            $query = $this->db->prepare($queryString, $providerName);
        }

        /** @var array<array-key, string> $pubKeys */
        $pubKeys = $this->db->get_col($query);

        if (empty($pubKeys)) {
            return array();
        }
        return $pubKeys;
    }

    /**
     * Get the database row ID for a given package.
     *
     * If the package does not exist, it will be created.
     *
     * @param string $packageName
     * @param int $providerId
     * @return int
     * @throws GossamerException
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
     * Get the database row ID for a given provider.
     *
     * If the provider does not exist, it will be created.
     *
     * @param string $providerName
     * @return int
     * @throws GossamerException
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
     * Get the database row ID for a given public key.
     *
     * If the public key does not exist, it will be created.
     *
     * @param string $publicKey
     * @param int $providerId
     * @return int
     * @throws GossamerException
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

    /**
     * Is the "limited" flag set to TRUE on this key?
     *
     * @param string $providerName
     * @param string $publicKey
     * @return bool
     */
    public function isKeyLimited($providerName, $publicKey)
    {
        $query = $this->db->prepare(
            "SELECT pk.limited FROM " . self::TABLE_PUBLIC_KEYS . " pk " .
            "JOIN " . self::TABLE_PROVIDERS . " prov ON pk.provider = prov.id " .
            "WHERE prov.name = ? AND NOT pk.revoked AND pk.publickey = ?",
            $providerName,
            $publicKey
        );
        return (bool) $this->db->get_col($query);
    }

    /**
     * @param string $providerName
     * @param string $packageName
     * @param string $version
     * @param int $offset
     * @return array
     * @throws \TypeError
     * @throws GossamerException
     *
     * @psalm-suppress UndefinedMethod
     */
    public function getRelease($providerName, $packageName, $version, $offset = 0)
    {
        $query = $this->db->prepare(
            "SELECT r.*
             FROM " . self::TABLE_PACKAGE_RELEASES . " r
             JOIN " . self::TABLE_PACKAGES . " p ON r.package = p.id
             JOIN " . self::TABLE_PROVIDERS ." v ON p.provider = v.id
             WHERE v.name = ? AND p.name = ? AND r.version = ?
             OFFSET $offset LIMIT 1",
            array(
                $providerName,
                $packageName,
                $version
            )
        );
        $results = (array) $this->db->get_row($query, 'ARRAY_A');
        if (empty($results)) {
            throw new GossamerException("Version {$version} not found for package {$providerName}/{$packageName}");
        }
        if (!empty($results['metadata'])) {
            /** @psalm-suppress MixedAssignment */
            $results['metadata'] = json_decode((string) $results['metadata'], true);
        }
        return $results;
    }


    /**
     * @param string $providerName
     * @param string $packageName
     * @param string $version
     * @return array<array-key, array{attestor: string, attestation: string, ledgerhash: string}>
     *
     * @psalm-suppress UndefinedMethod
     */
    public function getAttestations($providerName, $packageName, $version)
    {
        $query = $this->db->prepare(
            "SELECT r.id
             FROM " . self::TABLE_PACKAGE_RELEASES . " r
             JOIN " . self::TABLE_PACKAGES . " p ON r.package = p.id
             JOIN " . self::TABLE_PROVIDERS ." v ON p.provider = v.id
             WHERE v.name = ? AND p.name = ? AND r.version = ?",
            array(
                $providerName,
                $packageName,
                $version
            )
        );
        /** @var int $releaseId */
        $releaseId = (int) $this->db->get_col($query);
        if (empty($releaseId)) {
            throw new GossamerException('Release not found');
        }
        $query = $this->db->prepare(
            "SELECT attestor, attestation, ledgerhash
             FROM " . self::TABLE_ATTESTATIONS . "
             WHERE release_id = ?",
            array(
                $releaseId
            )
        );
        /** @var array<array-key, array{attestor: string, attestation: string, ledgerhash: string}> $results */
        $results = (array) $this->db->get_results($query, 'ARRAY_A');
        if (empty($results)) {
            return array();
        }
        return $results;
    }
}
