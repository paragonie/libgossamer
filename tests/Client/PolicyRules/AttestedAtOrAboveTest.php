<?php
namespace ParagonIE\Gossamer\Tests\Client\PolicyRules;

use ParagonIE\Gossamer\Client\AttestPolicy;
use PHPUnit\Framework\TestCase;
use ParagonIE\Gossamer\Client\PolicyRules\AttestedAtOrAbove;

/**
 * Class AttestedAtOrAboveTest
 * @package ParagonIE\Gossamer\Tests\Client\PolicyRules
 *
 * @covers \ParagonIE\Gossamer\Client\PolicyRules\AttestedAtOrAbove
 */
class AttestedAtOrAboveTest extends TestCase
{
    public function testAttestedAtOrAbove()
    {
        $rule = new AttestedAtOrAbove(AttestPolicy::CODE_REVIEW, array('kudelski', 'paragonie', 'roave'), 2);
        $this->assertFalse($rule->passes(
            array(
                array('attestor' => 'kudelski', 'attestation' => AttestPolicy::SPOT_CHECK),
                array('attestor' => 'paragonie', 'attestation' => AttestPolicy::CODE_REVIEW),
                array('attestor' => 'roave', 'attestation' => AttestPolicy::REPRODUCED)
            )
        ));
        $this->assertTrue($rule->passes(
            array(
                array('attestor' => 'kudelski', 'attestation' => AttestPolicy::SECURITY_AUDIT),
                array('attestor' => 'paragonie', 'attestation' => AttestPolicy::SECURITY_AUDIT),
                array('attestor' => 'roave', 'attestation' => AttestPolicy::SECURITY_AUDIT)
            )
        ));
        $this->assertTrue($rule->passes(
            array(
                array('attestor' => 'kudelski', 'attestation' => AttestPolicy::CODE_REVIEW),
                array('attestor' => 'paragonie', 'attestation' => AttestPolicy::CODE_REVIEW),
                array('attestor' => 'roave', 'attestation' => AttestPolicy::CODE_REVIEW)
            )
        ));
    }
}
