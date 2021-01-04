# Libgossamer API Reference - AttestPolicy

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Client\AttestPolicy`

## Constants

| Constant | Value |
|----------|-------|
| `CODE_REVIEW` | `code-review` |
| `REPRODUCED` | `reproduced` |
| `SPOT_CHECK` | `spot-check` |
| `SECURITY_AUDIT` | `sec-audit` |
| `VOTE_AGAINST` | `vote-against` |

## Constructor

**Arguments**:

1. [`PolicyRuleInterface`](PolicyRuleInterface.md) (variadic)

## Methods

### `addRule()`

Adds the rule to the set of top-level rules, and returns this object.

**Arguments**:

1. [`PolicyRuleInterface`](PolicyRuleInterface.md)

### `passes()`

Returns `TRUE` if the attestations pass all of the top-level rules.
Returns `FALSE` otherwise.

**Arguments**:

1. Array of arrays, which each contain these indices:
   1. `attestor` (string) - provider name
   2. `attestation` (string) - see [constants](#constants)
   3. `ledgerhash` (string) - hash from the cryptographic ledger

## Usage Example

A complex policy of rules that must pass before the update is installed
might look like this:

    (
        One of the following trusted providers must have verified
        that this build was reproducible from the source code.
    ) AND (
        (
            Two of the following trusted providers must have
            evaluated this at the `spot-check` level or higher.
        ) OR (
            One of the following trusted providers must have
            evaluated this at the `code-review` level or higher.
        )
    ) AND (
        None of the following trusted providers have issued a vote
        *against* installing this update.
    )

This can be easily expressed in code.

```php
<?php
use \ParagonIE\Gossamer\Client\AttestPolicy;
use \ParagonIE\Gossamer\Client\PolicyRules\AttestedAt;
use \ParagonIE\Gossamer\Client\PolicyRules\AttestedAtOrAbove;
use \ParagonIE\Gossamer\Client\PolicyRules\GroupAnd;
use \ParagonIE\Gossamer\Client\PolicyRules\GroupOr;
use \ParagonIE\Gossamer\Client\PolicyRules\Not;

$policy = (new AttestPolicy())
    ->addRule(
        new GroupAnd(
            new AttestedAt(
                AttestPolicy::REPRODUCED,
                ['reproduced-bot']
            ),
            new GroupOr(
                new AttestedAtOrAbove(
                    AttestPolicy::SPOT_CHECK,
                    ['paragonie', 'symfony', 'laravel', 'ncc'],
                    2
                ),
                new AttestedAtOrAbove(
                    AttestPolicy::CODE_REVIEW,
                    ['paragonie', 'ncc']
                )
            ),
            new Not(
                new AttestedAt(
                    AttestPolicy::VOTE_AGAINST,
                    ['jedisct1', 'muglug']
                )
            )
        )
    );
```
