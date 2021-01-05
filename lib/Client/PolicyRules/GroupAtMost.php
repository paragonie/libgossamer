<?php
namespace ParagonIE\Gossamer\Client\PolicyRules;

use ParagonIE\Gossamer\Client\PolicyRuleInterface;

/**
 * Class GroupAtMost
 *
 * "At most N of these M rules can pass."
 *
 * @package ParagonIE\Gossamer\Client\PolicyRules
 */
class GroupAtMost implements PolicyRuleInterface
{
    /** @var int $maximum */
    private $maximum;

    /** @var PolicyRuleInterface[] $rules */
    private $rules;

    /**
     * AtMost constructor.
     * @param int $maximum
     * @param PolicyRuleInterface ...$rules
     */
    public function __construct($maximum = 1, PolicyRuleInterface ...$rules)
    {
        $this->maximum = $maximum;
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
        return $valid <= $this->maximum;
    }
}
