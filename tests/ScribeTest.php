<?php
namespace ParagonIE\Gossamer\Tests;

use ParagonIE\Gossamer\Protocol\Action;
use ParagonIE\Gossamer\Protocol\SignedMessage;
use ParagonIE\Gossamer\Tests\Dummy\DummyChronicle;
use ParagonIE\Gossamer\Tests\Dummy\DummyDB;
use ParagonIE\Gossamer\Tests\Dummy\DummyScribe;
use PHPUnit\Framework\TestCase;

/**
 * Class ScribeTest
 * @covers \ParagonIE\Gossamer\Scribe\Chronicle
 * @package ParagonIE\Gossamer\Tests
 */
class ScribeTest extends TestCase
{
    const DUMMY_USERNAME = 'phpunit-dummy-user';

    /** @var DummyChronicle $chronicle */
    private $chronicle;

    /** @var DummyDB $db */
    private $db;

    /** @var DummyScribe $scribe */
    private $scribe;

    /**
     * @throws \SodiumException
     */
    public function testAppend()
    {
        $sk = sodium_hex2bin(
            'ed8e80be578b817157d916549580c8fea8c125a23a95e4ab6ca5c96d84e76f30' .
            '8c189d63e4fc43dfa3361f0d808aa60c210a759a1dd258ebdc196c2e9e710f1d'
        );
        $pk = sodium_hex2bin(
            '8c189d63e4fc43dfa3361f0d808aa60c210a759a1dd258ebdc196c2e9e710f1d'
        );
        $this->chronicle = new DummyChronicle();
        $this->scribe = new DummyScribe(
            $this->chronicle,
            'test-client-id',
            $sk,
            $pk
        );
        $this->db = new DummyDB();
        $dummyPk = sodium_hex2bin(
            'd4f66e7158589d5ff9ebc6a693f22ca0cb8c30b978e5c2a4a4aba3203c9a37b1'
        );

        $action = (new Action())
            ->withVerb(Action::VERB_APPEND_KEY)
            ->withProvider(self::DUMMY_USERNAME)
            ->withPublicKey(sodium_bin2hex($dummyPk));
        $sm = $action->toSignedMessage($sk);
        $this->assertTrue(
            $this->scribe->publish($sm)
        );

        // Confirm the "latest" entry is the one we wrote.
        $latest = $this->chronicle->latest();
        $this->assertTrue(is_string($latest['contents']));
        $sm2 = SignedMessage::fromString($latest['contents']);
        $this->assertSame($sm2->getProvider(), $sm->getProvider());
        $this->assertSame(
            $sm->insecureExtract()->getContents(),
            $sm2->insecureExtract()->getContents()
        );
    }
}
