<?php
namespace ParagonIE\Gossamer\Protocol;

use ParagonIE\Gossamer\Interfaces\DbInterface;
use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\CryptoBackends\SodiumBackend;
use ParagonIE\Gossamer\Interfaces\CryptoBackendInterface;
use ParagonIE\Gossamer\Util;
use SodiumException;

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
     *
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
     * Deserialize a SignedMessage from a string. Wraps init().
     *
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
     * Initialize from string components.
     *
     * Creates a Message object internally.
     *
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
     * Sign an arbitrary string, for a given provider, with a given secret key.
     *
     * Performs no database lookups or access controls validation.
     *
     * @param string $contents
     * @param string $provider
     * @param string $secretKey
     * @param CryptoBackendInterface $backend = null
     * @return SignedMessage
     * @throws SodiumException
     */
    public static function sign($contents, $provider, $secretKey, CryptoBackendInterface $backend = null)
    {
        if (empty($backend)) {
            $backend = new SodiumBackend();
        }
        $sig = $backend->sign($contents, $secretKey);
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
     * @param bool $pretty
     * @return string
     * @throws SodiumException
     */
    public function toString($pretty = false)
    {
        $code = $pretty ? JSON_PRETTY_PRINT : 0;
        return (string) json_encode(array(
            'signature' => sodium_bin2base64(
                Util::rawBinary($this->message->getSignature(), 64),
                SODIUM_BASE64_VARIANT_URLSAFE
            ),
            'message' => $this->message->getContents(),
            'provider' => $this->provider,
            'public-key' => $this->publicKey
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
        $action = Action::fromMessage($this->message);
        if (!$db->providerExists($this->provider)) {
            if (hash_equals(Action::VERB_APPEND_KEY, $action->getVerb())) {
                return true;
            } else {
                throw new GossamerException(
                    'New providers cannot be expected to do anything but add a key'
                );
            }
        }

        if (!empty($this->publicKey)) {
            if ($this->message->signatureValidForPublicKey($this->publicKey)) {
                return $this->publicKeyCanSignAction($db, $this->publicKey, $action);
            }
        } else {
            $providerKeys = $db->getPublicKeysForProvider($this->provider);
            foreach ($providerKeys as $publicKey) {
                if (!$this->publicKeyCanSignAction($db, $publicKey, $action)) {
                    continue;
                }
                if ($this->message->signatureValidForPublicKey($publicKey)) {
                    $this->publicKey = $publicKey;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Is this public key permitted to sign this kind of action?
     *
     * Returns FALSE if the key is limited and the action is not AppendUpdate.
     *
     * @param DbInterface $db
     * @param string $publicKey
     * @param Action $action
     * @return bool
     */
    protected function publicKeyCanSignAction(DbInterface $db, $publicKey, Action $action)
    {
        if ($action->getVerb() === Action::VERB_APPEND_UPDATE) {
            // This is always allowed, even for limited keys.
            return true;
        }
        // The remaining actions are only permitted if the key is not limited.
        return !$db->isKeyLimited($this->provider, $publicKey);
    }

    /**
     * Verifies that this message was signed by the responsible provider (or,
     * failing that, by the Super Provider), and then returns the Message.
     *
     * Throws a GossamerException if the signature is not valid for either
     * possible provider.
     *
     * @param DbInterface $db
     * @param string $superProvider
     * @return Message
     * @throws GossamerException
     * @throws SodiumException
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
     * Only meant to be used for unit testing.
     *
     * @return Message
     */
    public function insecureExtract()
    {
        return $this->message;
    }
}
