<?php
namespace ParagonIE\Gossamer\Tests\Client\PolicyRules;

use ParagonIE\Gossamer\Client\AttestPolicy;
use ParagonIE\Gossamer\Client\PolicyRules\AttestedAt;
use ParagonIE\Gossamer\Client\PolicyRules\Not;
use PHPUnit\Framework\TestCase;

/**
 * Class NotTest
 * @package ParagonIE\Gossamer\Tests\Client\PolicyRules
 * @covers \ParagonIE\Gossamer\Client\PolicyRules\Not
 */
class NotTest extends TestCase
{
    public function testNot()
    {
        $policy = (new AttestPolicy())
            ->addRule(
                new Not(
                    new AttestedAt(AttestPolicy::VOTE_AGAINST, array('paragonie'))
                )
            );
        $this->assertTrue($policy->passes(array()));
        $this->assertTrue($policy->passes(array(
            array(
                'attestor' => 'deborah',
                'attestation' => AttestPolicy::VOTE_AGAINST,
                'ledgerhash' => bin2hex(random_bytes(16))
            )
        )));
        $this->assertFalse($policy->passes(array(
            array(
                'attestor' => 'paragonie',
                'attestation' => AttestPolicy::VOTE_AGAINST,
                'ledgerhash' => bin2hex(random_bytes(16))
            )
        )));
    }
}
