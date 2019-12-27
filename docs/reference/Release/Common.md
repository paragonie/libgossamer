# Libgossamer API Reference - Release - Common

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Release\Common`

## Class Constants

| Name | Value |
|------|-------|
| `Common::SIGN_ALG_ED25519_BLAKE2B` | `0x57505733` |
| `Common::SIGN_ALG_ED25519_SHA384` | `0x5750652f` |

## Constructor

**Arguments**:

1. `int` Algorithm (see [Class Constants](#class-constants))

## Static Methods

### `signatureAlgorithmMap`

Maps the constants to a developer-friendly definition.

Returns an `array`.

## Methods

### `preHashFile()`

**Arguments**:

1. `string` - File Path

Returns a `string` (raw binary hash).
