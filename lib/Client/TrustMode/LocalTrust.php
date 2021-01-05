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
     * @return array<array-key, string>
     */
    public function getVerificationKeys($provider)
    {
        return $this->db->getPublicKeysForProvider($provider);
    }

    /**
     * @param string $provider
     * @param string $package
     * @param string $version
     * @return UpdateFile
     */
    public function getUpdateInfo($provider, $package, $version)
    {
        /** @var array{publickey: string, signature: string, metadata: array} $data */
        $data = $this->db->getRelease($provider, $package, $version);

        /** @var array{attestor: string, attestation: string, ledgerhash: string}[] $attestations */
        $attestations = $this->db->getAttestations($provider, $package, $version);

        return new UpdateFile(
            (string) $data['publickey'],
            (string) $data['signature'],
            (array) $data['metadata'],
            (array) $attestations
        );
    }
}
