# Client - UpdateFile

**Fully Qualified Class Name**: `ParagonIE\Gossamer\Client\UpdateFile`

## Constructor

**Arguments**:

1. `string` The public key used to sign this update.
2. `string` The signature of this update.
3. `array` Metadata about this update.
4. Array of arrays, which each contain these indices:
    1. `attestor` (string)
    2. `attestation` (string)
    3. `ledgerhash` (string)
5. [`AttestPolicy`](AttestPolicy.md) (or null; optional)

