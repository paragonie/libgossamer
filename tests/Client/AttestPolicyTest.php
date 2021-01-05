<?php
namespace ParagonIE\Gossamer\Tests\Client;

use ParagonIE\Gossamer\Client\AttestPolicy;
use ParagonIE\Gossamer\Client\PolicyRules\AttestedAt;
use ParagonIE\Gossamer\Client\PolicyRules\GroupAnd;
use ParagonIE\Gossamer\Client\PolicyRules\GroupOr;
use PHPUnit\Framework\TestCase;

/**
 * Class AttestPolicyTest
 * @covers \ParagonIE\Gossamer\Client\AttestPolicy
 * @package ParagonIE\Gossamer\Tests\Client
 */
class AttestPolicyTest extends TestCase
{
    public function testBasicRules()
    {
        $attestations = array(
            array(
                'attestor' => 'alexa',
                'attestation' => AttestPolicy::SPOT_CHECK,
                'ledgerhash' => bin2hex(random_bytes(16))
            ),
            array(
                'attestor' => 'brian',
                'attestation' => AttestPolicy::REPRODUCED,
                'ledgerhash' => bin2hex(random_bytes(16))
            ),
            array(
                'attestor' => 'charlie',
                'attestation' => AttestPolicy::SPOT_CHECK,
                'ledgerhash' => bin2hex(random_bytes(16))
            ),
            array(
                'attestor' => 'deborah',
                'attestation' => AttestPolicy::CODE_REVIEW,
                'ledgerhash' => bin2hex(random_bytes(16))
            )
        );


        $policy1 = (new AttestPolicy())
            ->addRule(
                new GroupOr(
                    new AttestedAt(AttestPolicy::SPOT_CHECK, array('alexa', 'charlie', 'evelyn'), 2),
                    new AttestedAt(AttestPolicy::CODE_REVIEW, array('brian', 'deborah'))
                )
            );
        $this->assertTrue($policy1->passes($attestations));

        $policy2 = (new AttestPolicy())
            ->addRule(
                new GroupAnd(
                    new AttestedAt(AttestPolicy::SPOT_CHECK, array('alexa', 'charlie', 'evelyn', 'frank'), 4),
                    new AttestedAt(AttestPolicy::CODE_REVIEW, array('brian', 'deborah', 2))
                )
            );
        $this->assertFalse($policy2->passes($attestations));
    }

}
