<?php
namespace ParagonIE\Gossamer\Client\PolicyRules;

use ParagonIE\Gossamer\Client\PolicyRuleInterface;

/**
 * Class GroupAtLeast
 *
 * "At least N of these M rules must pass."
 *
 * @package ParagonIE\Gossamer\Client\PolicyRules
 */
class GroupAtLeast implements PolicyRuleInterface
{
    /** @var int $minimum */
    private $minimum;

    /** @var PolicyRuleInterface[] $rules */
    private $rules;

    /**
     * AtLeast constructor.
     * @param int $minimum
     * @param PolicyRuleInterface ...$rules
     */
    public function __construct($minimum = 1, PolicyRuleInterface ...$rules)
    {
        $this->minimum = $minimum;
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
        $valid = 0;
        foreach ($this->rules as $rule) {
            if ($rule->passes($attestations)) {
                ++$valid;
            }
        }
        return $valid >= $this->minimum;
    }
}
