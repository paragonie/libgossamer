<?php
namespace ParagonIE\Gossamer\Release;

use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\Util;
use ParagonIE\Gossamer\VerifierInterface;

/**
 * Class Verifier
 * @package ParagonIE\Gossamer\Verifier
 */
class Verifier extends Common implements VerifierInterface
{
    /**
     * @param string $filePath
     * @param string $signature
     * @param array<array-key, string> $publicKeys
     * @return bool
     * @throws GossamerException
     * @throws \SodiumException
     * @psalm-suppress InternalMethod
     */
    public function verify($filePath = '', $signature = '', array $publicKeys = array())
    {
        $prehash = $this->preHashFile($filePath);
        $rawSig = Util::rawBinary($signature, 68);
        $header = \ParagonIE_Sodium_Core_Util::substr($rawSig, 0, 4);
        if (!hash_equals($this->alg, $header)) {
            throw new GossamerException('Invalid signature header');
        }
        $sig = \ParagonIE_Sodium_Core_Util::substr($rawSig, 4);
        $valid = false;

        foreach ($publicKeys as $pk) {
            $valid = $valid || sodium_crypto_sign_verify_detached(
                $sig,
                $prehash,
                Util::rawBinary($pk, 32)
            );
        }
        return $valid;
    }
}
