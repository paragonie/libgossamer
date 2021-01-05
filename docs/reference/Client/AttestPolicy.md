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

See [the relevant tutorial](../../tutorials/01-configuring-gossamer-client.md).
