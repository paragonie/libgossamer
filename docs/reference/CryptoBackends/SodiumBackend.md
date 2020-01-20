# SodiumBackend

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\CryptoBackends\SodiumBackend`

## Class Methods

### `sign()`

**Arguments**:

1. `string` - Message
2. `string` - Ed25519 secret key

**Returns** a `string`.

### `verify()`

**Arguments**:

1. `string` - Message
2. `string` - Signature
3. `string` - Ed25519 public key

**Returns** a `bool`.
