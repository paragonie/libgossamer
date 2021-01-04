# Client - PolicyRuleInterface

* **Fully Qualified Interface Name**: `ParagonIE\Gossamer\Client\PolicyRuleInterface`

## Interface Methods

### `passes()`

Returns a `bool`.

**Arguments**:

1. Array of arrays, which each contain these indices:
    1. `attestor` (string)
    2. `attestation` (string)
    3. `ledgerhash` (string)
