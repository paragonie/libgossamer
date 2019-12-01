<?php
namespace ParagonIE\Gossamer\Tests\Dummy;

use ParagonIE\Gossamer\Protocol\SignedMessage;
use ParagonIE\Gossamer\ScribeInterface;
use ParagonIE\Gossamer\Util;

/**
 * Class DummyScribe
 * @package ParagonIE\Gossamer\Tests\Dummy
 */
class DummyScribe implements ScribeInterface
{
    /** @var DummyChronicle $chronicle */
    private $chronicle;

    /** @var string $clientId */
    private $clientId;

    /** @var string $clientSecretKey */
    private $clientSecretKey;

    /** @var string $serverPublicKey */
    private $serverPublicKey;

    /**
     * DummyScribe constructor.
     * @param DummyChronicle|null $chronicle
     * @param string $clientId
     * @param string $clientSecretKey
     * @param string $serverPublicKey
     * @throws \SodiumException
     */
    public function __construct(
        DummyChronicle $chronicle = null,
        $clientId = '',
        $clientSecretKey = '',
        $serverPublicKey = ''
    ) {
        if (!$chronicle) {
            $chronicle = new DummyChronicle();
        }
        $this->chronicle = $chronicle;
        $this->clientId = $clientId;
        $this->clientSecretKey = $clientSecretKey;
        $this->serverPublicKey = $serverPublicKey;
    }

    /**
     * @param string $serialized
     * @return string
     * @throws \SodiumException
     */
    public function signMessageBody($serialized)
    {
        return sodium_bin2base64(
            sodium_crypto_sign_detached(
                $serialized,
                Util::rawBinary($this->clientSecretKey, 64)
            ),
            SODIUM_BASE64_VARIANT_URLSAFE
        );
    }

    /**
     * @return string
     * @throws \SodiumException
     */
    public function getClientPublicKey()
    {
        $raw = Util::rawBinary($this->clientSecretKey, 64);
        return \ParagonIE_Sodium_Core_Util::substr($raw, 32, 32);
    }

    /**
     * Write a record onto the configured ledger.
     *
     * @param SignedMessage $message
     * @return bool
     */
    public function publish(SignedMessage $message)
    {
        $contents = $message->toString();
        $signature = $this->signMessageBody($contents);
        $this->chronicle->append(
            $contents,
            $this->getClientPublicKey(),
            $signature
        );
        $retrieved = $this->chronicle->latest();

        return hash_equals(
            Util::rawBinary($signature, 64),
            Util::rawBinary($retrieved['signature'], 64)
        );
    }
}
