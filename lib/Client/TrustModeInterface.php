<?php
namespace ParagonIE\Gossamer\Client;

/**
 * Interface TrustModeInterface
 * @package ParagonIE\Gossamer\Client
 */
interface TrustModeInterface
{
    /**
     * @param string $provider
     * @param ?string $purpose
     * @return string[]
     */
    public function getVerificationKeys($provider, $purpose = null);

    /**
     * @param string $provider
     * @param string $package
     * @param string $version
     * @param ?string $artifact
     * @return UpdateFile
     */
    public function getUpdateInfo($provider, $package, $version, $artifact = null);
}
