<?php
namespace ParagonIE\Gossamer\Client\TrustMode;

use ParagonIE\Gossamer\Client\TrustModeInterface;
use ParagonIE\Gossamer\Client\UpdateFile;
use ParagonIE\Gossamer\DbInterface;

/**
 * Class LocalTrust
 *
 * Local trust means look at the local database.
 *
 * @package ParagonIE\Gossamer\Client\TrustMode
 */
class LocalTrust implements TrustModeInterface
{
    /** @var DbInterface $db */
    private $db;

    /**
     * LocalTrust constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $provider
     * @param ?string $purpose
     * @return array<array-key, string>
     */
    public function getVerificationKeys($provider, $purpose = null)
    {
        return $this->db->getPublicKeysForProvider($provider, null, $purpose);
    }

    /**
     * @param string $provider
     * @param string $package
     * @param string $version
     * @param ?string $artifact
     * @return UpdateFile
     */
    public function getUpdateInfo($provider, $package, $version, $artifact = null)
    {
        /** @var array{publickey: string, signature: string, metadata: array} $data */
        $data = $this->db->getRelease($provider, $package, $version, $artifact);

        /** @var array{attestor: string, attestation: string, ledgerhash: string}[] $attestations */
        $attestations = $this->db->getAttestations($provider, $package, $version, $artifact);

        return new UpdateFile(
            (string) $data['publickey'],
            (string) $data['signature'],
            (array) $data['metadata'],
            (array) $attestations
        );
    }
}
