# Libgossamer API Reference - Protocol - Packet

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Protocol\Packet`

## Constructor

**Arguments**:

1. `string` - Contents

## Static Methods

### `createSigned()`

**Arguments**:

1. `array` - Contents
2. `string` - Secret Key

Returns a `Packet`.

## Methods

### `getPublicKey()`

**Returns** a `string`.

### `getSignature()`

**Returns** a `string`.

### `isSigned()`

**Returns** a `bool`.

### `setPublicKey()`

**Arguments**:

1. `string` - Public Key

**Returns** this `Packet` object.

### `setSignature()`

**Arguments**:

1. `string` - Signature

**Returns** this `Packet` object.

### `signatureIsValid()`

**Returns** a `bool`.
