<?php
namespace ParagonIE\Gossamer\Tests\Client\PolicyRules;

use ParagonIE\Gossamer\Client\AttestPolicy;
use ParagonIE\Gossamer\Client\PolicyRules\AttestedAt;
use ParagonIE\Gossamer\Client\PolicyRules\GroupAtMost;
use PHPUnit\Framework\TestCase;

/**
 * Class GroupAtMostTest
 * @package ParagonIE\Gossamer\Tests\Client\PolicyRules
 * @covers \ParagonIE\Gossamer\Client\PolicyRules\GroupAtMost
 */
class GroupAtMostTest extends TestCase
{
    public function testAtMost()
    {
        $attestations = array(
            array(
                'attestation' => AttestPolicy::VOTE_AGAINST,
                'attestor' => 'jedisct1',
                'ledgerhash' => '',
            ),
            array(
                'attestation' => AttestPolicy::VOTE_AGAINST,
                'attestor' => 'paragonie',
                'ledgerhash' => '',
            ),
            array(
                'attestation' => AttestPolicy::VOTE_AGAINST,
                'attestor' => 'roave',
                'ledgerhash' => '',
            )
        );
        $policy1 = (new AttestPolicy())
            ->addRule(new GroupAtMost(2,
                new AttestedAt(AttestPolicy::VOTE_AGAINST, array('jedisct1')),
                new AttestedAt(AttestPolicy::VOTE_AGAINST, array('kudelski')),
                new AttestedAt(AttestPolicy::VOTE_AGAINST, array('ncc')),
                new AttestedAt(AttestPolicy::VOTE_AGAINST, array('paragonie')),
                new AttestedAt(AttestPolicy::VOTE_AGAINST, array('roave'))
            ));
        $this->assertFalse($policy1->passes($attestations));

        $policy2 = (new AttestPolicy())
            ->addRule(new GroupAtMost(3,
                new AttestedAt(AttestPolicy::VOTE_AGAINST, array('jedisct1')),
                new AttestedAt(AttestPolicy::VOTE_AGAINST, array('kudelski')),
                new AttestedAt(AttestPolicy::VOTE_AGAINST, array('ncc')),
                new AttestedAt(AttestPolicy::VOTE_AGAINST, array('paragonie')),
                new AttestedAt(AttestPolicy::VOTE_AGAINST, array('roave'))
            ));
        $this->assertTrue($policy2->passes($attestations));
    }
}
