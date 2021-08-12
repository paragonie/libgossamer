# CryptoBackendInterface

* **Fully Qualified Interface Name**: `ParagonIE\Gossamer\Interfaces\CryptoBackendInterface`

## Interface Methods

### `sign()`

**Arguments**:

1. `string` - Message
2. `string` - Secret key (or a key identifier, if required)

**Returns** a `string`.

### `verify()`

**Arguments**:

1. `string` - Message
2. `string` - Signature
3. `string` - Public key (or a key identifier, if required)

**Returns** a `bool`.
