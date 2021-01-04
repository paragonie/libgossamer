<?php
namespace ParagonIE\Gossamer\Client\PolicyRules;

use ParagonIE\Gossamer\Client\PolicyRuleInterface;

/**
 * Class Not
 *
 * "Rule X must NOT pass."
 *
 * Logical inversion.
 *
 * @package ParagonIE\Gossamer\Client\PolicyRules
 */
class Not implements PolicyRuleInterface
{
    /** @var PolicyRuleInterface $predicate */
    private $predicate;

    /**
     * Not constructor.
     *
     * @param PolicyRuleInterface $predicate
     */
    public function __construct(PolicyRuleInterface $predicate)
    {
        $this->predicate = $predicate;
    }

    /**
     * @param array{attestor: string, attestation: string, ledgerhash: string}[] $attestations
     * @return bool
     */
    public function passes(array $attestations)
    {
        return !$this->predicate->passes($attestations);
    }
}
