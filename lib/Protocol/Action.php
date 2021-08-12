<?php
namespace ParagonIE\Gossamer\Protocol;

use ParagonIE\Gossamer\CryptoBackendInterface;
use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\DbInterface;
use SodiumException;

/**
 * Class Action
 *
 * An action is something to perform on a local data store.
 */
class Action
{
    const VERB_APPEND_KEY = 'AppendKey';
    const VERB_REVOKE_KEY = 'RevokeKey';
    const VERB_APPEND_UPDATE = 'AppendUpdate';
    const VERB_REVOKE_UPDATE = 'RevokeUpdate';
    const VERB_ATTEST_UPDATE = 'AttestUpdate';

    /** @var ?string $artifact */
    private $artifact = null;

    /** @var string $attestation */
    private $attestation = '';

    /** @var string $targetProvider */
    private $targetProvider = '';

    /** @var string $hash */
    private $hash = '';

    /** @var bool $limited */
    private $limited = false;

    /** @var array $meta */
    private $meta = array();

    /** @var string $package */
    private $package = '';

    /** @var string $provider */
    private $provider = '';

    /** @var string $publicKey */
    private $publicKey = '';

    /** @var string $purpose */
    private $purpose = '';

    /** @var string $release */
    private $release = '';

    /** @var string $signature */
    private $signature = '';

    /** @var string $verb */
    private $verb = '';

    /**
     * Action constructor.
     * @param string $verb
     */
    public function __construct($verb = '')
    {
        $this->verb = $verb;
    }

    /**
     * Extract the action details from a given message.
     *
     * @param Message $message
     * @return Action
     * @throws GossamerException
     */
    public static function fromMessage(Message $message)
    {
        /** @var array<string, mixed> $json */
        $json = json_decode($message->getContents(), true);
        $action = new Action();
        if (!isset($json['verb'])) {
            throw new GossamerException('No verb attached to this message.');
        }
        $action->verb = (string) $json['verb'];
        switch ($json['verb']) {
            case self::VERB_APPEND_KEY:
            case self::VERB_REVOKE_KEY:
                $action->provider = (string) $json['provider'];
                $action->publicKey = (string) $json['public-key'];
                if (!empty($json['limited'])) {
                    $action->limited = true;
                }
                if (!empty($json['purpose'])) {
                    $action->purpose = (string) $json['purpose'];
                }
                break;
            case self::VERB_ATTEST_UPDATE:
                $action->provider = (string) $json['attestor'];
                $action->attestation = (string) $json['attestation'];
                $action->targetProvider = (string) $json['provider'];
                $action->package = (string) $json['package'];
                $action->release = (string) $json['release'];
                $action->artifact = isset($json['artifact'])
                    ? (string) $json['artifact']
                    : null;
                break;
            case self::VERB_APPEND_UPDATE:
            case self::VERB_REVOKE_UPDATE:
                $action->provider = (string) $json['provider'];
                // Public key and signature are tied to the file contents:
                $action->publicKey = (string) $json['public-key'];
                if (isset($json['signature'])) {
                    $action->signature = (string) $json['signature'];
                }
                $action->package = (string) $json['package'];
                $action->release = (string) $json['release'];
                $action->artifact = isset($json['artifact'])
                    ? (string) $json['artifact']
                    : null;
                break;
        }
        if (!empty($json['meta'])) {
            if (is_string($json['meta'])) {
                $action->meta = (array) json_decode($json['meta'], true);
            } else {
                $action->meta = (array) $json['meta'];
            }
        }
        if (!empty($json['hash'])) {
            $action->hash = (string) $json['hash'];
        }
        return $action;
    }

    /**
     * Serialize to a Message object. The opposite of fromMessage().
     *
     * @return Message
     */
    public function toMessage()
    {
        return new Message($this->toJsonString());
    }

    /**
     * Convert to a JSON-encoded string. Used to build Message objects.
     *
     * @return string
     */
    public function toJsonString()
    {
        $array = array('verb' => $this->verb);
        if (!is_null($this->artifact)) {
            $array['artifact'] = $this->artifact;
        }
        if (!empty($this->limited)) {
            $array['limited'] = true;
        }
        if (!empty($this->meta)) {
            $array['meta'] = $this->meta;
        }
        if (!empty($this->package)) {
            $array['package'] = $this->package;
        }
        if ($this->verb === self::VERB_ATTEST_UPDATE) {
            if (!empty($this->provider)) {
                $array['attestor'] = $this->provider;
            }
            if (!empty($this->attestation)) {
                $array['attestation'] = $this->attestation;
            }
            if (!empty($this->targetProvider)) {
                $array['provider'] = $this->targetProvider;
            }
        } elseif (!empty($this->provider)) {
            $array['provider'] = $this->provider;
        }
        if (!empty($this->publicKey)) {
            $array['public-key'] = $this->publicKey;
        }
        if (!empty($this->purpose)) {
            $array['purpose'] = $this->purpose;
        }
        if (!empty($this->release)) {
            $array['release'] = $this->release;
        }
        if (!empty($this->signature)) {
            $array['signature'] = $this->signature;
        }
        return (string) json_encode($array);
    }

    /**
     * @param string $signingKey
     * @param CryptoBackendInterface|null $backend
     * @return SignedMessage
     * @throws SodiumException
     */
    public function toSignedMessage($signingKey, CryptoBackendInterface $backend = null)
    {
        return SignedMessage::sign(
            $this->toJsonString(),
            $this->provider,
            $signingKey,
            $backend
        );
    }

    /**
     * @return ?string
     */
    public function getArtifact()
    {
        return $this->artifact;
    }

    /**
     * @return string
     */
    public function getAttestation()
    {
        return $this->attestation;
    }

    /**
     * @return string
     */
    public function getTargetProvider()
    {
        return $this->targetProvider;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @return string
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
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
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * @return string
     */
    public function getRelease()
    {
        return $this->release;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @return string
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     * Actually do the thing! This accepts a database interface for handling
     * the persistent data storage.
     *
     * The action should only be performed if the action has been determined
     * to be legitimate.
     *
     * Note: Access controls are enforced at the Synchronizer level, not here.
     *
     * @param DbInterface $db
     * @return bool
     */
    public function perform(DbInterface $db)
    {
        switch ($this->verb) {
            case self::VERB_APPEND_KEY:
                return $db->appendKey(
                    $this->provider,
                    $this->publicKey,
                    $this->limited,
                    $this->purpose,
                    $this->meta,
                    $this->hash
                );
            case self::VERB_REVOKE_KEY:
                return $db->revokeKey(
                    $this->provider,
                    $this->publicKey,
                    $this->meta,
                    $this->hash
                );
            case self::VERB_APPEND_UPDATE:
                return $db->appendUpdate(
                    $this->provider,
                    $this->package,
                    $this->publicKey,
                    $this->release,
                    $this->artifact,
                    $this->signature,
                    $this->meta,
                    $this->hash
                );
            case self::VERB_REVOKE_UPDATE:
                return $db->revokeUpdate(
                    $this->provider,
                    $this->package,
                    $this->publicKey,
                    $this->release,
                    $this->artifact,
                    $this->meta,
                    $this->hash
                );
            case self::VERB_ATTEST_UPDATE:
                return $db->attestUpdate(
                    $this->targetProvider,
                    $this->package,
                    $this->release,
                    $this->artifact,
                    $this->provider,
                    $this->attestation,
                    $this->meta,
                    $this->hash
                );
            default:
                return false;
        }
    }

    /**
     * @param string $attestation
     * @return self
     */
    public function withAttestation($attestation)
    {
        $self = clone $this;
        $self->attestation = $attestation;
        return $self;
    }

    /**
     * @param string $targetProvider
     * @return self
     */
    public function withTargetProvider($targetProvider)
    {
        $self = clone $this;
        $self->targetProvider = $targetProvider;
        return $self;
    }

    /**
     * @param string $hash
     * @return self
     */
    public function withHash($hash)
    {
        $self = clone $this;
        $self->hash = $hash;
        return $self;
    }

    /**
     * @param bool $limited
     * @return self
     */
    public function withLimited($limited)
    {
        $self = clone $this;
        $self->limited = $limited;
        return $self;
    }

    /**
     * @param array $meta
     * @return self
     */
    public function withMeta(array $meta = array())
    {
        $self = clone $this;
        $self->meta = $meta;
        return $self;
    }
    /**
     * @param string $package
     * @return self
     */
    public function withPackage($package)
    {
        $self = clone $this;
        $self->package = $package;
        return $self;
    }

    /**
     * @param string $provider
     * @return self
     */
    public function withProvider($provider)
    {
        $self = clone $this;
        $self->provider = $provider;
        return $self;
    }

    /**
     * @param string $publicKey
     * @return self
     */
    public function withPublicKey($publicKey)
    {
        $self = clone $this;
        $self->publicKey = $publicKey;
        return $self;
    }

    /**
     * @param string $release
     * @return self
     */
    public function withRelease($release)
    {
        $self = clone $this;
        $self->release = $release;
        return $self;
    }

    /**
     * @param string $signature
     * @return self
     */
    public function withSignature($signature)
    {
        $self = clone $this;
        $self->signature = $signature;
        return $self;
    }

    /**
     * @param string $verb
     * @return self
     */
    public function withVerb($verb)
    {
        $self = clone $this;
        $self->verb = $verb;
        return $self;
    }
}
