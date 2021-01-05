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
     * @return string[]
     */
    public function getVerificationKeys($provider);

    /**
     * @param string $provider
     * @param string $package
     * @param string $version
     * @return UpdateFile
     */
    public function getUpdateInfo($provider, $package, $version);
}
