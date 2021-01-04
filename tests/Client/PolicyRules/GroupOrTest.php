<?php
namespace ParagonIE\Gossamer\Tests\Client\PolicyRules;

use ParagonIE\Gossamer\Client\AttestPolicy;
use ParagonIE\Gossamer\Client\PolicyRules\AttestedAt;
use ParagonIE\Gossamer\Client\PolicyRules\GroupOr;
use PHPUnit\Framework\TestCase;

/**
 * Class GroupOrTest
 * @package ParagonIE\Gossamer\Tests\Client\PolicyRules
 * @covers \ParagonIE\Gossamer\Client\PolicyRules\GroupOr
 */
class GroupOrTest extends TestCase
{
    public function testOr()
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
            ->addRule(new GroupOr(
                new AttestedAt(AttestPolicy::SECURITY_AUDIT, array('paragonie')),
                new AttestedAt(AttestPolicy::CODE_REVIEW, array('paragonie')),
            ));
        $this->assertFalse($policy1->passes($attestations));

        $policy2 = (new AttestPolicy())
            ->addRule(new GroupOr(
                new AttestedAt(AttestPolicy::SECURITY_AUDIT, array('paragonie')),
                new AttestedAt(AttestPolicy::CODE_REVIEW, array('roave')),
            ));
        $this->assertTrue($policy2->passes($attestations));
    }
}
