<?php
namespace ParagonIE\Gossamer\Tests\Tools;

use ParagonIE\Gossamer\Release\Signer;
use ParagonIE\Gossamer\GossamerException;
use PHPUnit\Framework\TestCase;

/**
 * Class SignerTest
 * @covers \ParagonIE\Gossamer\Release\Signer
 * @package ParagonIE\Gossamer\Tests\Tools
 */
class SignerTest extends TestCase
{

    /** @var string $sk */
    private $sk;

    /** @var string $pk */
    private $pk;

    /**
     * @throws \SodiumException
     * @before
     */
    public function setUpNoConflict()
    {
        $this->sk = sodium_hex2bin(
            'ed8e80be578b817157d916549580c8fea8c125a23a95e4ab6ca5c96d84e76f30' .
            '8c189d63e4fc43dfa3361f0d808aa60c210a759a1dd258ebdc196c2e9e710f1d'
        );
    }

    /**
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function testSignFile()
    {
        $filepath = __DIR__ . '/test2.txt';
        file_put_contents($filepath, hash('sha512', 'libgossamer'));
        $signer = new Signer();
        $signature = $signer->signFile($filepath, $this->sk);
        $this->assertSame(
            '5750652f' .
            'ef49cf3ae7757f36b65c652716cec2f9ee90dcefd0c80b0c323235a6382d12fd61bc909020884d0c62cc54ad1c68af915ae2ecb735d0d186cb0236ceb6875201',
            $signature
        );
    }
}
