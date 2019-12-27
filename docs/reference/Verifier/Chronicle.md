# Libgossamer API Reference - Verifier - Chronicle ([LedgerInterface](../LedgerInterface.md), [VerifierInterface](../VerifierInterface.md))

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Verifier\Chronicle`

## Class Constants

| Name | Value | Meaning |
|------|-------|---------|
| `Verifier::TRUST_BASIC` | `basic` | Verify Ed25519 signatures with the given public key |
| `Verifier::TRUST_ZEALOUS` | `zealous` | Blind faith in this Chronicle instance |

## Constructor

**Arguments**:

1. [`HttpInterface`](../HttpInterface.md)

## Methods

### `verify()`

Proxy method for [`quorumAgrees()`](#quorumAgrees).

**Arguments**:

1. `string` - Hash

**Returns** a `bool`.

### `addChronicle()`

**Arguments**:

1. `string` - URL
2. `string` - Public Key
3. `string` - Trust Level (optional)

**Returns** this `Chronicle` object.

### `randomChronicle()`

**Returns** an `array`.

### `randomSubset()`

**Arguments**:

1. `int` - Number to select

**Returns** an `array`.

### `quorumAgrees()` 

**Arguments**:

1. `string` - Hash

**Returns** a `bool`.

### `chronicleSeesHash()`

**Arguments**:

1. `string` - Hash
2. `array` - Chronicle, consisting of
   * `url`: `string`
   * `public-key`: `string`
   * `trust`: `string`

**Returns** a `bool`.

### `processChronicleResponse()`

**Arguments**:

1. `array` - Chronicle, consisting of
   * `url`: `string`
   * `public-key`: `string`
   * `trust`: `string`
2. `int` - HTTP Response Status Code
3. `array<string, string>[]` - HTTP Response Headers
4. `string` HTTP Response Body

**Returns** a `bool`.

### `setQuorumMinimum()`

**Arguments**:

1. `int` - Minimum number of instances that must agree. 

**Returns** this `Chronicle` object.

### `clearInstances()`

Empties the internal list of Chronicle instances to query.

**Returns** this `Chronicle` object.

### `populateInstances()`

1. `array[]` - Array of Chronicle configurations, each, consisting of:
   * `url`: `string`
   * `public-key`: `string`
   * `trust`: `string`

**Returns** this `Chronicle` object.
