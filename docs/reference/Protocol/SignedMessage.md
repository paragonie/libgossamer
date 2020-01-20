# Libgossamer API Reference - Protocol - SignedMessage

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Protocol\SignedMessage`

## Constructor

**Arguments**:

1. [`Message`](Message.md) - Serialized [action](Action.md)
2. `string` - Provider Name
3. `string` - Public Key

## Static Methods

### `fromString()`

**Arguments**:

1. `string` - Packed signed message

**Returns** a `SignedMessage` object.

### `init()`

**Arguments**:

1. `string` - Contents
2. `string` - Signature
3. `string` - Provider
4. `string` - Public Key 

**Returns** a `SignedMessage` object.

### `sign()`

**Arguments**:

1. `string` - Contents
2. `string` - Provider
3. `string` - Secret Key
4. [`CryptoProviderBackend`](../CryptoBackendInterface.md) -
   Optional cryptographic backend. Defaults to [SodiumBackend](../CryptoBackends/SodiumBackend.md).

**Returns** a `SignedMessage` object.

## Methods

### `getProvider()`

**Returns** a `string`.

### `getMeta()`

**Arguments**:

1. `string` - Key

**Returns** a `string`.

### `setMeta()`

**Arguments**:

1. `string` - Key
2. `string` - Value

**Returns** this `SignedMessage` object.

### `toString()`

Serializes this `SignedMessage` object as a string. This is the opposite of
[`SignedMessage::fromString()`](#fromstring).

**Returns** a `string`.

### `verifySuperProvider()`

Was this signed by the blessed super-provider?

**Arguments**:

1. [`DbInterface`](../DbInterface.md)
2. `string` - Super Provider Name

**Returns** a `bool`.

### `verify()`

Was this signed by the provider responsible?

**Arguments**:

1. [`DbInterface`](../DbInterface.md)

**Returns** a `bool`.

### `verifyAndExtract()`

Verify that this was signed by the appropriate provider (or the super provider,
if applicable), and return the `Message` object contained within.

**Arguments**:

1. [`DbInterface`](../DbInterface.md)
2. `string` - Super Provider Name

**Returns** a [`Message`](Message.md).

### `insecureExtract()`

Useful for unit testing. Don't use it otherwise.

**Returns** a [`Message`](Message.md).
