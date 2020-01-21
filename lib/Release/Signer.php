<?php
namespace ParagonIE\Gossamer\Release;

use ParagonIE\Gossamer\GossamerException;

/**
 * Class Signer
 * @package ParagonIE\Gossamer\Tools
 */
class Signer extends Common
{
    /**
     * Sign an update file with your secret key.
     *
     * @param string $filePath
     * @param string $secretKey
     * @return string
     *
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function signFile($filePath, $secretKey)
    {
        $signed = $this->backend->sign(
            $this->preHashFile($filePath),
            $secretKey
        );
        return sodium_bin2hex($this->alg . $signed);
    }
}
