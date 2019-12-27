<?php
namespace ParagonIE\Gossamer\Tests\Release;

use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\Release\Verifier;
use PHPUnit\Framework\TestCase;

/**
 * Class VerifierTest
 * @package ParagonIE\Gossamer\Tests\Release
 */
class VerifierTest extends TestCase
{
    /** @var string $pk */
    private $pk;

    /**
     * @throws \SodiumException
     */
    public function setUp()
    {
        parent::setUp();
        $this->pk = sodium_hex2bin(
            '8c189d63e4fc43dfa3361f0d808aa60c210a759a1dd258ebdc196c2e9e710f1d'
        );
    }

    /**
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function testVerify()
    {
        $filepath = __DIR__ . '/test2.txt';
        if (!is_readable($filepath)) {
            file_put_contents($filepath, hash('sha512', 'libgossamer'));
        }
        // 4-byte header + signature
        $signature = '5750652f' .
            'ef49cf3ae7757f36b65c652716cec2f9ee90dcefd0c80b0c323235a6382d12fd61bc909020884d0c62cc54ad1c68af915ae2ecb735d0d186cb0236ceb6875201';
        $verifier = new Verifier();
        $this->assertTrue(
            $verifier->verify($filepath, $signature, array($this->pk))
        );

        $random = sodium_crypto_sign_keypair();
        $random_pk = sodium_crypto_sign_publickey($random);
        $this->assertFalse(
            $verifier->verify($filepath, $signature, array($random_pk))
        );

        $this->assertTrue(
            $verifier->verify($filepath, $signature, array($random_pk, $this->pk))
        );

        // Clean up
        unlink($filepath);
    }
}
