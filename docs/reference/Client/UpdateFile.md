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

## Class Methods

### `isFileValid()`

Returns TRUE if the signature is valid for this update file AND the
attestation policy is satisfied.

**Arguments**:

1. `string|resource|StreamInterface` The file path, or file handle, or
   PSR-7 `StreamInterface` containing the update file.

Returns a `bool`.

### `isSignatureValid()`

Returns TRUE if the signature is valid for this update file.

**Arguments**:

1. `string|resource|StreamInterface` The file path, or file handle, or
   PSR-7 `StreamInterface` containing the update file.

Returns a `bool`.

### `passesAttestationPolicy()`

Returns TRUE if the configured attestation policy has been satisfied.

Returns a `bool`.

### `setAlgorithm()`

Sets the algorithm identifier for the signature on this update file.

**Arguments:**

1. `int` See [the constants in Common.php](../Release/Common.md).

Returns this object.

### `setAttestPolicy()`

Sets the configured attestation policy to the provided object.

**Arguments:**

1. [`AttestPolicy`](AttestPolicy.md)

Returns this object.
