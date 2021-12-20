<?php
namespace ParagonIE\Gossamer\Protocol;

use ParagonIE\Gossamer\Util;
use SodiumException;

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
     *
     * @param string $contents
     * @param string $signature
     */
    public function __construct($contents, $signature = '')
    {
        $this->contents = $contents;
        $this->signature = $signature;
    }

    /**
     * Get the contents of this message.
     *
     * @return string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Get the signature on this message.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Is the signature valid for this message, against a given public key?
     *
     * @param string $publicKey
     * @return bool
     * @throws SodiumException
     */
    public function signatureValidForPublicKey($publicKey)
    {
        if (empty($this->signature)) {
            return false;
        }
        return sodium_crypto_sign_verify_detached(
            Util::rawBinary($this->signature, 64),
            $this->contents,
            Util::rawBinary($publicKey, 32)
        );
    }

    /**
     * @param bool $pretty
     * @return string
     * @throws SodiumException
     */
    public function toString($pretty = false)
    {
        $code = $pretty ? JSON_PRETTY_PRINT : 0;
        if ($this->signature) {
            $signature = sodium_bin2base64(
                Util::rawBinary($this->getSignature(), 64),
                SODIUM_BASE64_VARIANT_URLSAFE
            );
        } else {
            $signature = '';
        }
        return (string) json_encode(array(
            'signature' => $signature,
            'message' => $this->getContents(),
        ), $code);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->toString();
        } catch (\Error $ex) {
            return '';
        } catch (\Exception $ex) {
            return '';
        }
    }
}
