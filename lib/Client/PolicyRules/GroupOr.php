<?php
namespace ParagonIE\Gossamer\Client\PolicyRules;

use ParagonIE\Gossamer\Client\PolicyRuleInterface;

/**
 * Class GroupOr
 *
 * "Any of $x OR $y OR $z must pass."
 *
 * @package ParagonIE\Gossamer\Client\PolicyRules
 */
class GroupOr implements PolicyRuleInterface
{
    /** @var PolicyRuleInterface[] $rules */
    private $rules;

    /**
     * GroupAnd constructor.
     * @param PolicyRuleInterface ...$rules
     */
    public function __construct(PolicyRuleInterface ...$rules)
    {
        $this->rules = $rules;
    }

    /**
     * @param PolicyRuleInterface $rule
     * @return self
     */
    public function addRule(PolicyRuleInterface $rule)
    {
        $this->rules []= $rule;
        return $this;
    }

    /**
     * @param array{attestor: string, attestation: string, ledgerhash: string}[] $attestations
     * @return bool
     */
    public function passes(array $attestations)
    {
        $passedAny = false;
        foreach ($this->rules as $rule) {
            $passedAny = $passedAny || $rule->passes($attestations);
        }
        return $passedAny;
    }
}
