<?php
namespace ParagonIE\Gossamer;

/**
 * Class Packet
 *
 * Encapsulates a signed HTTP message (request or response)
 */
class Packet
{
    /** @var string $contents */
    protected $contents = '';

    /** @var string $publicKey */
    protected $publicKey = '';

    /** @var string $signature */
    protected $signature = '';

    /**
     * Packet constructor.
     * @param string $string
     */
    public function __construct($string)
    {
        $this->contents = $string;
    }

    /**
     * @param array $contents
     * @param string $secretKey
     * @return self
     * @throws GossamerException
     * @throws \SodiumException
     */
    public static function createSigned(array $contents, $secretKey)
    {
        $sk = Util::rawBinary($secretKey, 64);
        $encoded = json_encode($contents);
        if (!is_string($encoded)) {
            throw new GossamerException(
                'Could not encode JSON message: ' . json_last_error_msg(),
                json_last_error()
            );
        }
        $packet = new Packet($encoded);

        $sig = sodium_crypto_sign_detached($encoded, $sk);
        $packet->signature = sodium_bin2hex($sig);

        $pk = sodium_crypto_sign_publickey_from_secretkey($sk);
        $packet->publicKey = sodium_bin2hex($pk);

        Util::memzero($sk);
        return $packet;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @return bool
     */
    public function isSigned()
    {
        return !empty($this->signature) && !empty($this->publicKey);
    }

    /**
     * @param string $publicKey
     * @return self
     * @throws \SodiumException
     */
    public function setPublicKey($publicKey)
    {
        // Normalize to hex-encoded, always.
        $pk = Util::rawBinary($publicKey, 32);
        $this->publicKey = sodium_bin2hex($pk);
        return $this;
    }

    /**
     * @param string $signature
     * @return self
     * @throws \SodiumException
     */
    public function setSignature($signature)
    {
        // Normalize to hex-encoded, always.
        $sig = Util::rawBinary($signature, 64);
        $this->signature = sodium_bin2hex($sig);
        return $this;
    }

    /**
     * @return bool
     * @throws \SodiumException
     */
    public function signatureIsValid()
    {
        if (!$this->isSigned()) {
            return false;
        }
        return sodium_crypto_sign_verify_detached(
            Util::rawBinary($this->signature, 64),
            $this->contents,
            Util::rawBinary($this->publicKey, 32)
        );
    }
}
