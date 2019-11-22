<?php
namespace ParagonIE\Gossamer\Release;

use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\Util;

/**
 * Class Signer
 * @package ParagonIE\Gossamer\Tools
 */
class Signer extends Common
{
    /**
     * @param string $filePath
     * @param string $secretKey
     * @return string
     *
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function signFile($filePath, $secretKey)
    {
        $signed = sodium_crypto_sign_detached(
            $this->preHashFile($filePath),
            Util::rawBinary($secretKey, 64)
        );
        return sodium_bin2hex($this->alg . $signed);
    }
}
