<?php
namespace ParagonIE\Gossamer\Tests\Protocol;

use ParagonIE\Gossamer\Client\AttestPolicy;
use ParagonIE\Gossamer\Protocol\Action;
use ParagonIE\Gossamer\Protocol\Message;
use ParagonIE\Gossamer\Tests\Dummy\DummyDB;
use PHPUnit\Framework\TestCase;

/**
 * Class ActionTest
 * @covers \ParagonIE\Gossamer\Protocol\Action
 * @package ParagonIE\Gossamer\Tests\Protocol
 */
class ActionTest extends TestCase
{
    const DUMMY_USERNAME = 'phpunit-dummy-user';

    /** @var DummyDB $db */
    private $db;

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
        $this->db = new DummyDB();
        $this->sk = sodium_hex2bin(
            'ed8e80be578b817157d916549580c8fea8c125a23a95e4ab6ca5c96d84e76f30' .
            '8c189d63e4fc43dfa3361f0d808aa60c210a759a1dd258ebdc196c2e9e710f1d'
        );
        $this->pk = sodium_hex2bin(
            '8c189d63e4fc43dfa3361f0d808aa60c210a759a1dd258ebdc196c2e9e710f1d'
        );
    }

    /**
     * @throws \ParagonIE\Gossamer\GossamerException
     * @throws \SodiumException
     */
    public function testAppendRevokeKey()
    {
        $action = Action::fromMessage($this->getAppendKeyMessage());
        $action->perform($this->db);
        $state = $this->db->getState();

        // Test that it exists
        $this->assertGreaterThan(0, count($state[DummyDB::TABLE_PUBLIC_KEYS]));

        $providerId = $this->db->getProviderId(self::DUMMY_USERNAME);
        $index = $this->db->getPublicKeyId(
            sodium_bin2hex($this->pk),
            $providerId
        );

        // We should not be revoked:
        $this->assertFalse(
            $state[DummyDB::TABLE_PUBLIC_KEYS][$index]['revoked']
        );

        $action = Action::fromMessage($this->getRevokeKeyMessage());
        $action->perform($this->db);
        $state = $this->db->getState();

        // We should be revoked:
        $this->assertTrue(
            $state[DummyDB::TABLE_PUBLIC_KEYS][$index]['revoked']
        );
    }
    /**
     * @throws \ParagonIE\Gossamer\GossamerException
     * @throws \SodiumException
     */
    public function testAppendRevokeUpdate()
    {
        $action = Action::fromMessage($this->getAppendUpdateMessage('wordpress/core', '9.99.98'));
        $action->perform($this->db);
        $action = Action::fromMessage($this->getAppendUpdateMessage('wordpress/core', '9.99.99'));
        $action->perform($this->db);
        $state = $this->db->getState();

        $this->assertGreaterThan(1, count($state[DummyDB::TABLE_PACKAGE_RELEASES]));

        $providerId = $this->db->getProviderId(self::DUMMY_USERNAME);
        $packageId = $this->db->getPackageId('wordpress/core', $providerId);
        $update99 = $this->db->hashIndex(DummyDB::TABLE_PACKAGE_RELEASES, $packageId . '@@9.99.99');

        $this->assertFalse(
            $state[DummyDB::TABLE_PACKAGE_RELEASES][$update99]['revoked']
        );

        $action = Action::fromMessage($this->getRevokeUpdateMessage('wordpress/core', '9.99.99'));
        $action->perform($this->db);
        $state = $this->db->getState();
        $this->assertTrue(
            $state[DummyDB::TABLE_PACKAGE_RELEASES][$update99]['revoked']
        );
    }

    public function testAppendAttestUpdate()
    {
        $action = Action::fromMessage($this->getAppendUpdateMessage('wordpress/core', '9.99.99'));
        $action->perform($this->db);
        $action = Action::fromMessage($this->getAppendAttestMessage(
            AttestPolicy::REPRODUCED,
            'paragonie',
            'wordpress/core',
            '9.99.99'
        ));
        $action->perform($this->db);
        $action = Action::fromMessage($this->getAppendAttestMessage(
            AttestPolicy::SPOT_CHECK,
            'roave',
            'wordpress/core',
            '9.99.99'
        ));
        $action->perform($this->db);
        $state = $this->db->getState();
        $this->assertGreaterThan(0, count($state[DummyDB::TABLE_ATTESTATIONS]));

        $attestations = array_values(array_shift($state[DummyDB::TABLE_ATTESTATIONS]));
        $this->assertEquals('paragonie', $attestations[0]['attestor']);
        $this->assertEquals(AttestPolicy::REPRODUCED, $attestations[0]['attestation']);

        $this->assertEquals('roave', $attestations[1]['attestor']);
        $this->assertEquals(AttestPolicy::SPOT_CHECK, $attestations[1]['attestation']);
    }

    /**
     * @return Message
     * @throws \SodiumException
     */
    protected function getAppendKeyMessage()
    {
        $json = json_encode([
            'verb' => Action::VERB_APPEND_KEY,
            'provider' => self::DUMMY_USERNAME,
            'public-key' => sodium_bin2hex($this->pk)
        ]);
        $signature = sodium_bin2hex(
            sodium_crypto_sign_detached($json, $this->sk)
        );
        return new Message($json, $signature);
    }

    /**
     * @return Message
     * @throws \SodiumException
     */
    protected function getRevokeKeyMessage()
    {
        $json = json_encode([
            'verb' => Action::VERB_REVOKE_KEY,
            'provider' => self::DUMMY_USERNAME,
            'public-key' => sodium_bin2hex($this->pk)
        ]);
        $signature = sodium_bin2hex(
            sodium_crypto_sign_detached($json, $this->sk)
        );
        return new Message($json, $signature);
    }

    /**
     * @param string $package
     * @param string $version
     * @param array $meta
     * @return Message
     * @throws \SodiumException
     */
    protected function getAppendUpdateMessage($package = 'foo/bar', $version = '0.0.1', array $meta = array())
    {
        $json = json_encode([
            'verb' => Action::VERB_APPEND_UPDATE,
            'provider' => self::DUMMY_USERNAME,
            'package' => $package,
            'public-key' => sodium_bin2hex($this->pk),
            'release' => $version,
            'signature' => sodium_bin2hex(
                sodium_crypto_sign_detached(
                    hash('sha384', $version, true),
                    $this->sk
                )
            ),
            'hash' => sodium_bin2hex(
                sodium_crypto_generichash(
                    hash('sha384', $version, true) . $this->db->getCacheKey()
                )
            ),
            'meta' => $meta
        ]);
        $signature = sodium_bin2hex(
            sodium_crypto_sign_detached($json, $this->sk)
        );
        return new Message($json, $signature);
    }

    /**
     * @param string $package
     * @param string $version
     * @param array $meta
     * @return Message
     * @throws \SodiumException
     */
    protected function getRevokeUpdateMessage($package = 'foo/bar', $version = '0.0.1', array $meta = array())
    {
        $json = json_encode([
            'verb' => Action::VERB_REVOKE_UPDATE,
            'provider' => self::DUMMY_USERNAME,
            'package' => $package,
            'public-key' => sodium_bin2hex($this->pk),
            'release' => $version,
            'signature' => sodium_bin2hex(
                sodium_crypto_sign_detached(
                    hash('sha384', $version, true),
                    $this->sk
                )
            ),
            'hash' => sodium_bin2hex(
                sodium_crypto_generichash(
                    hash('sha384', $version, true) . $this->db->getCacheKey()
                )
            ),
            'meta' => $meta
        ]);
        $signature = sodium_bin2hex(
            sodium_crypto_sign_detached($json, $this->sk)
        );
        return new Message($json, $signature);
    }

    /**
     * @param string $msg
     * @param string $attestor
     * @param string $package
     * @param string $version
     * @param array $meta
     * @return Message
     */
    protected function getAppendAttestMessage(
        $msg = AttestPolicy::REPRODUCED,
        $attestor = self::DUMMY_USERNAME,
        $package = 'foo/bar',
        $version = '0.0.1',
        array $meta = array()
    ) {
        $json = json_encode([
            'verb' => Action::VERB_ATTEST_UPDATE,
            'provider' => self::DUMMY_USERNAME,
            'package' => $package,
            'release' => $version,
            'attestation' => $msg,
            'attestor' => $attestor,
            'meta' => $meta
        ]);
        $signature = sodium_bin2hex(
            sodium_crypto_sign_detached($json, $this->sk)
        );
        return new Message($json, $signature);
    }
}
