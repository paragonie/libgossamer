<?php
namespace ParagonIE\Gossamer;

/**
 * Interface CryptoBackendInterface
 * @package ParagonIE\Gossamer\Release
 */
interface CryptoBackendInterface
{
    /**
     * @param string $message
     * @param string $secretKey
     * @return string
     */
    public function sign($message, $secretKey = '');

    /**
     * @param string $message
     * @param string $signature
     * @param string $publicKey
     * @return bool
     */
    public function verify($message, $signature, $publicKey = '');
}
