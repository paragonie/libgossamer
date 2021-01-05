<?php
namespace ParagonIE\Gossamer\Tests\Client\PolicyRules;

use ParagonIE\Gossamer\Client\AttestPolicy;
use ParagonIE\Gossamer\Client\PolicyRules\AttestedAt;
use ParagonIE\Gossamer\Client\PolicyRules\GroupAnd;
use PHPUnit\Framework\TestCase;

/**
 * Class GroupAndTest
 * @package ParagonIE\Gossamer\Tests\Client\PolicyRules
 * @covers \ParagonIE\Gossamer\Client\PolicyRules\GroupAnd
 */
class GroupAndTest extends TestCase
{
    public function testAnd()
    {
        $attestations = array(
            array(
                'attestation' => AttestPolicy::SPOT_CHECK,
                'attestor' => 'paragonie',
                'ledgerhash' => '',
            ),
            array(
                'attestation' => AttestPolicy::CODE_REVIEW,
                'attestor' => 'roave',
                'ledgerhash' => '',
            )
        );

        $policy1 = (new AttestPolicy())
            ->addRule(new GroupAnd(
                new AttestedAt(AttestPolicy::SECURITY_AUDIT, array('paragonie')),
                new AttestedAt(AttestPolicy::CODE_REVIEW, array('paragonie'))
            ));
        $this->assertFalse($policy1->passes($attestations));

        $policy2 = (new AttestPolicy())
            ->addRule(new GroupAnd(
                new AttestedAt(AttestPolicy::SECURITY_AUDIT, array('paragonie')),
                new AttestedAt(AttestPolicy::CODE_REVIEW, array('roave'))
            ));
        $this->assertFalse($policy2->passes($attestations));
    }
}
