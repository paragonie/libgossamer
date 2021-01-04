<?php
namespace ParagonIE\Gossamer\Client;

/**
 * Class AttestPolicy
 * @package ParagonIE\Gossamer\Client
 */
class AttestPolicy
{
    /* Attestation Types */
    const CODE_REVIEW = 'code-review';
    const REPRODUCED = 'reproduced';
    const SPOT_CHECK = 'spot-check';
    const SECURITY_AUDIT = 'sec-audit';
    const VOTE_AGAINST = 'vote-against';

    /** @var PolicyRuleInterface[] $rules */
    private $rules;

    /**
     * AttestPolicy constructor.
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
     * Does this set of attestations for an update pass all top-level rules?
     *
     * @param array{attestor: string, attestation: string, ledgerhash: string}[] $attestations
     * @return bool
     */
    public function passes(array $attestations)
    {
        if (empty($this->rules)) {
            return true;
        }
        $passesTopLevel = true;
        foreach ($this->rules as $rule) {;
            $passesTopLevel = $passesTopLevel && $rule->passes($attestations);
        }
        return $passesTopLevel;
    }
}
