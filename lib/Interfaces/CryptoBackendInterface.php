<?php
namespace ParagonIE\Gossamer\Interfaces;

/**
 * Interface CryptoBackendInterface
 * @package ParagonIE\Gossamer\Release
 */
interface CryptoBackendInterface
{
    /**
     * Sign a message with Ed25519. Must return raw binary.
     *
     * The secret key parameter can also be a key identifier (e.g. with HSMs or
     * cloud-based key management services).
     *
     * @param string $message
     * @param string $secretKey
     * @return string
     */
    public function sign($message, $secretKey = '');

    /**
     * Verify the signature for a message against a given Ed25519 public key.
     *
     * The public key parameter can also be a key identifier (e.g. with HSMs or
     * cloud-based key management services).
     *
     * @param string $message
     * @param string $signature
     * @param string $publicKey
     * @return bool
     */
    public function verify($message, $signature, $publicKey = '');
}
