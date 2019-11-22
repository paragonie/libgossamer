<?php
namespace ParagonIE\Gossamer\Protocol;

use ParagonIE\Gossamer\DbInterface;
use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\Util;

/**
 * Class SignedMessage
 * @package ParagonIE\Gossamer\Protocol
 */
class SignedMessage
{
    /**
     * @var Message $message
     */
    private $message;

    /**
     * @var array<array-key, string> $meta
     */
    private $meta = array();

    /**
     * @var string $publicKey
     */
    private $publicKey = '';

    /**
     * @var string $provider
     */
    private $provider;

    /**
     * SignedMessage constructor.
     * @param Message $message
     * @param string $provider
     * @param string $publicKey
     */
    public function __construct(Message $message, $provider, $publicKey = '')
    {
        $this->message = $message;
        $this->provider = $provider;
        $this->publicKey = $publicKey;
    }

    /**
     * @param string $contents
     * @param string $signature
     * @param string $provider
     * @param string $publicKey
     * @return self
     */
    public static function init($contents, $signature, $provider, $publicKey = '')
    {
        return new SignedMessage(
            new Message($contents, $signature),
            $provider,
            $publicKey
        );
    }

    /**
     * @param string $contents
     * @param string $provider
     * @param string $secretKey
     * @return SignedMessage
     * @throws \SodiumException
     */
    public static function sign($contents, $provider, $secretKey)
    {
        $secretKey = Util::rawBinary($secretKey, 64);
        $sig = sodium_crypto_sign_detached($contents, $secretKey);
        return SignedMessage::init(
            $contents,
            sodium_bin2hex($sig),
            $provider,
            sodium_bin2hex(
                sodium_crypto_sign_publickey_from_secretkey($secretKey)
            )
        );
    }

    /**
     * @param string $packed
     * @return self
     */
    public static function fromString($packed)
    {
        /** @var array{signature: string, message: string, provider: string, public-key: string} $decoded */
        $decoded = json_decode($packed, true);

        if (empty($decoded['public-key'])) {
            $decoded['public-key'] = '';
        }

        return self::init(
            $decoded['message'],
            $decoded['signature'],
            $decoded['provider'],
            $decoded['public-key']
        );
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param array-key $key
     * @return string
     */
    public function getMeta($key)
    {
        return $this->meta[$key];
    }

    /**
     * @param array-key $key
     * @param string $value
     * @return self
     */
    public function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
        return $this;
    }

    /**
     * @return string
     * @throws \SodiumException
     */
    public function toString()
    {
        return (string) json_encode(array(
            'signature' => sodium_bin2base64(
                Util::rawBinary($this->message->getSignature(), 64),
                SODIUM_BASE64_VARIANT_URLSAFE
            ),
            'message' => $this->message->getContents(),
            'provider' => $this->provider,
            'public-key' => $this->publicKey
        ));
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

    /**
     * Was this signed by the blessed super-provider?
     *
     * @param DbInterface $db
     * @param string $superProvider
     * @return bool
     * @throws \SodiumException
     */
    public function verifySuperProvider(DbInterface $db, $superProvider)
    {
        $providerKeys = $db->getPublicKeysForProvider($superProvider);
        foreach ($providerKeys as $publicKey) {
            if ($this->message->signatureValidForPublicKey($publicKey)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Was this signed by the provider responsible?
     *
     * @param DbInterface $db
     * @return bool
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function verify(DbInterface $db)
    {
        if (!$db->providerExists($this->provider)) {
            $action = Action::fromMessage($this->message);
            try {
                if (hash_equals(Action::VERB_APPEND_KEY, $action->getVerb())) {
                    return true;
                } else {
                    throw new GossamerException(
                        'New providers cannot be expected to do anything but add a key'
                    );
                }
            } finally {
                unset($action);
            }
        }

        if (!empty($this->publicKey)) {
            if ($this->message->signatureValidForPublicKey($this->publicKey)) {
                return true;
            }
        } else {
            $providerKeys = $db->getPublicKeysForProvider($this->provider);
            foreach ($providerKeys as $publicKey) {
                if ($this->message->signatureValidForPublicKey($publicKey)) {
                    $this->publicKey = $publicKey;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param DbInterface $db
     * @param string $superProvider
     * @return Message
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function verifyAndExtract(DbInterface $db, $superProvider)
    {
        if ($this->verify($db)) {
            return $this->message;
        }
        if ($this->verifySuperProvider($db, $superProvider)) {
            return $this->message;
        }
        throw new GossamerException(
            "Signed message was not valid for any of this provider's public keys ({$this->provider})"
        );
    }

    /**
     * @return Message
     */
    public function insecureExtract()
    {
        return $this->message;
    }
}
