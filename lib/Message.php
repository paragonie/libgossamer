<?php
namespace ParagonIE\Gossamer;

/**
 * Class Message
 *
 * Encapsulates a cryptographically signed message
 */
class Message
{
    /**
     * @var string $contents
     */
    private $contents;

    /**
     * @var string $signature
     */
    private $signature;

    /**
     * Message constructor.
     * @param string $contents
     * @param string $signature
     */
    public function __construct($contents, $signature)
    {
        $this->contents = $contents;
        $this->signature = $signature;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param string $publicKey
     * @return bool
     * @throws \SodiumException
     */
    public function signatureValidForPublicKey($publicKey)
    {
        return sodium_crypto_sign_verify_detached(
            Util::rawBinary($this->signature, 64),
            $this->contents,
            Util::rawBinary($publicKey, 32)
        );
    }
}
