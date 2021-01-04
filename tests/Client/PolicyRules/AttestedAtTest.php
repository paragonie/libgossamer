<?php
namespace ParagonIE\Gossamer\Tests\Client\PolicyRules;

use ParagonIE\Gossamer\Client\AttestPolicy;
use PHPUnit\Framework\TestCase;
use ParagonIE\Gossamer\Client\PolicyRules\AttestedAt;

/**
 * Class AttestedAtTest
 * @package ParagonIE\Gossamer\Tests\Client\PolicyRules
 *
 * @covers \ParagonIE\Gossamer\Client\PolicyRules\AttestedAt
 */
class AttestedAtTest extends TestCase
{
    public function testAttestedAt()
    {
        $rule = new AttestedAt(AttestPolicy::CODE_REVIEW, array('kudelski', 'paragonie', 'roave'), 2);
        $this->assertFalse($rule->passes(
            array(
                array('attestor' => 'kudelski', 'attestation' => AttestPolicy::SPOT_CHECK),
                array('attestor' => 'paragonie', 'attestation' => AttestPolicy::CODE_REVIEW),
                array('attestor' => 'roave', 'attestation' => AttestPolicy::REPRODUCED)
            )
        ));
        $this->assertFalse($rule->passes(
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
