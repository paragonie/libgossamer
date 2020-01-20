<?php
namespace ParagonIE\Gossamer\Release;

use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\Release\Backends\SodiumBackend;
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
        $signed = $this->backend->sign(
            $this->preHashFile($filePath),
            $secretKey
        );
        return sodium_bin2hex($this->alg . $signed);
    }
}
