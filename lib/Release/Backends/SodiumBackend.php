<?php
namespace ParagonIE\Gossamer\Release\Backends;

use ParagonIE\Gossamer\Release\CryptoBackendInterface;
use ParagonIE\Gossamer\Util;

/**
 * Class SodiumBackend
 * @package ParagonIE\Gossamer\Release\Backends
 */
class SodiumBackend implements CryptoBackendInterface
{
    /**
     * @param string $message
     * @param string $secretKey
     * @return string
     * @throws \SodiumException
     */
    public function sign($message, $secretKey = '')
    {
        $sk = Util::rawBinary($secretKey, 64);
        return sodium_crypto_sign_detached($message, $sk);
    }

    /**
     * @param string $message
     * @param string $signature
     * @param string $publicKey
     * @return bool
     * @throws \SodiumException
     */
    public function verify($message, $signature, $publicKey = '')
    {
        return sodium_crypto_sign_verify_detached(
            $signature,
            $message,
            Util::rawBinary($publicKey, 32)
        );
    }
}
