<?php
namespace ParagonIE\Gossamer\Tests\Protocol;

use ParagonIE\Gossamer\Protocol\Packet;
use PHPUnit\Framework\TestCase;
use Exception;
use SodiumException;

/**
 * Class PacketTest
 * @covers \ParagonIE\Gossamer\Protocol\Packet
 * @package ParagonIE\Gossamer\Tests\Protocol
 */
class PacketTest extends TestCase
{
    public $signing_key;
    public $verification_key;

    /**
     * @throws SodiumException
     * @before
     */
    public function setUpNoConflict()
    {
        $keypair = sodium_crypto_sign_keypair();
        $this->signing_key = sodium_crypto_sign_secretkey($keypair);
        $this->verification_key = sodium_crypto_sign_publickey($keypair);
    }

    /**
     * @throws Exception
     * @throws SodiumException
     */
    public function testSignature()
    {
        // Happy path:
        $testArray = array(
            'id' => 'phpunit test case #1',
            'random' => sodium_bin2hex(random_bytes(32)),
            'foo' => true
        );
        $packet = Packet::createSigned($testArray, $this->signing_key);
        $this->assertTrue($packet->isSigned(), 'Expected a signed packet.');
        $this->assertTrue($packet->signatureIsValid(), 'Expected signature to be valid.');
        $this->assertSame(sodium_bin2hex($this->verification_key), $packet->getPublicKey());

        // Unhappy path:
        $invalidArray = $testArray;
        $invalidArray['foo'] = false;
        $invalid = Packet::createSigned($invalidArray, $this->signing_key);
        $this->assertNotEquals(
            $invalid->getSignature(),
            $packet->getSignature()
        );
        $invalid->setSignature($packet->getSignature());
        $this->assertTrue($invalid->isSigned(), 'Expected a signed packet.');
        $this->assertFalse($invalid->signatureIsValid(), 'Expected signature to be invalid.');
    }
}
