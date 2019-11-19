<?php
namespace ParagonIE\Gossamer\Tests;

use PHPUnit\Framework\TestCase;
use ParagonIE\Gossamer\Message;
use Exception;
use SodiumException;

/**
 * Class MessageTest
 */
class MessageTest extends TestCase
{
    public $signing_key;
    public $verification_key;

    /**
     * @throws SodiumException
     */
    public function setUp()
    {
        $keypair = sodium_crypto_sign_keypair();
        $this->signing_key = sodium_crypto_sign_secretkey($keypair);
        $this->verification_key = sodium_bin2hex(
            sodium_crypto_sign_publickey($keypair)
        );
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
        $testString = json_encode($testArray, JSON_PRETTY_PRINT );
        $signature = sodium_bin2hex(
            sodium_crypto_sign_detached($testString, $this->signing_key)
        );
        $message = new Message($testString, $signature);
        $this->assertTrue(
            $message->signatureValidForPublicKey($this->verification_key)
        );

        // Unhappy path:
        $badArray = $testArray;
        $badArray['foo'] = false;
        $badString = json_encode($badArray, JSON_PRETTY_PRINT);
        $badMessage = new Message($badString, $signature);
        $this->assertFalse(
            $badMessage->signatureValidForPublicKey($this->verification_key)
        );
    }
}
