<?php
namespace ParagonIE\Gossamer\Client\PolicyRules;

use ParagonIE\Gossamer\Client\PolicyRuleInterface;

/**
 * Class GroupAnd
 *
 * "$x AND $y AND $z must pass."
 *
 * @package ParagonIE\Gossamer\Client\PolicyRules
 */
class GroupAnd implements PolicyRuleInterface
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
        $passedAll = false;
        foreach ($this->rules as $rule) {
            $passedAll = $passedAll && $rule->passes($attestations);
        }
        return $passedAll;
    }
}
