<?php
namespace ParagonIE\Gossamer\Tests\Protocol;

use ParagonIE\Gossamer\Protocol\Action;
use ParagonIE\Gossamer\Protocol\Message;
use ParagonIE\Gossamer\Protocol\SignedMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class SignedMessageTest
 * @covers \ParagonIE\Gossamer\Protocol\SignedMessage
 * @package ParagonIE\Gossamer\Tests\Protocol
 */
class SignedMessageTest extends TestCase
{
    const DUMMY_USERNAME = 'phpunit-dummy-user';

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
        if (!extension_loaded('sodium')) {
            $this->markTestSkipped('ext/sodium not installed or enabled');
        }
        $this->sk = sodium_hex2bin(
            'ed8e80be578b817157d916549580c8fea8c125a23a95e4ab6ca5c96d84e76f30' .
            '8c189d63e4fc43dfa3361f0d808aa60c210a759a1dd258ebdc196c2e9e710f1d'
        );
        $this->pk = sodium_hex2bin(
            '8c189d63e4fc43dfa3361f0d808aa60c210a759a1dd258ebdc196c2e9e710f1d'
        );
    }

    /**
     * @throws \SodiumException
     */
    public function testSignedMessage()
    {
        /*
        $dummySk = sodium_hex2bin(
            'f1ca4a4ea33c14fa0a696b911dc30917117de316f1cec3602aabb01c3ce5fbde' .
            'd4f66e7158589d5ff9ebc6a693f22ca0cb8c30b978e5c2a4a4aba3203c9a37b1'
        );
        */
        $dummyPk = sodium_hex2bin(
            'd4f66e7158589d5ff9ebc6a693f22ca0cb8c30b978e5c2a4a4aba3203c9a37b1'
        );

        $action = (new Action())
            ->withVerb(Action::VERB_APPEND_KEY)
            ->withProvider(self::DUMMY_USERNAME)
            ->withPublicKey(sodium_bin2hex($dummyPk));

        $signedMessage = $action->toSignedMessage($this->sk);
        $asString = $signedMessage->toString();
        $expected = json_encode(array(
            'signature' => 'Eiubp_5zCEQidCkDqZIT_rqPs34pvCgVVOtn4uZE9dVjb5qH1eegjdbsiEvVDXalLwwb_JPkS4WXSl8mbd9AAw==',
            'message' => '{"verb":"AppendKey","provider":"phpunit-dummy-user","public-key":"d4f66e7158589d5ff9ebc6a693f22ca0cb8c30b978e5c2a4a4aba3203c9a37b1"}',
            'provider' => 'phpunit-dummy-user',
            'public-key' => '8c189d63e4fc43dfa3361f0d808aa60c210a759a1dd258ebdc196c2e9e710f1d'
        ));
        $this->assertEquals($expected, $asString);

        $fromString = SignedMessage::fromString($asString);
        $this->assertInstanceOf(SignedMessage::class, $fromString);
        $this->assertSame($fromString->getProvider(), self::DUMMY_USERNAME);

        $extracted = $fromString->insecureExtract();
        $this->assertInstanceOf(Message::class, $extracted);
        $this->assertTrue($extracted->signatureValidForPublicKey($this->pk));
    }
}
