<?php
namespace ParagonIE\Gossamer\Protocol;

use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\DbInterface;

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

    /** @var string $hash */
    private $hash = '';

    /** @var array $meta */
    private $meta = array();

    /** @var string $package */
    private $package = '';

    /** @var string $provider */
    private $provider = '';

    /** @var string $publicKey */
    private $publicKey = '';

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
     * @return Message
     */
    public function toMessage()
    {
        return new Message($this->toJsonString());
    }

    /**
     * @return string
     */
    public function toJsonString()
    {
        $array = array('verb' => $this->verb);
        if (!empty($this->meta)) {
            $array['meta'] = $this->meta;
        }
        if (!empty($this->package)) {
            $array['package'] = $this->package;
        }
        if (!empty($this->provider)) {
            $array['provider'] = $this->provider;
        }
        if (!empty($this->publicKey)) {
            $array['public-key'] = $this->publicKey;
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
     * @return SignedMessage
     * @throws \SodiumException
     */
    public function toSignedMessage($signingKey)
    {
        return SignedMessage::sign(
            $this->toJsonString(),
            $this->provider,
            $signingKey
        );
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
                    $this->meta,
                    $this->hash
                );
            default:
                return false;
        }
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
