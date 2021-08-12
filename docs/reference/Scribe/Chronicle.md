# Libgossamer API Reference - Scribe - Chronicle ([ScribeInterface](../Interfaces/ScribeInterface.md))

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Scribe\Chronicle`

## Class Constants

| Name | Value |
|------|-------|
| `Chronicle::CLIENT_ID_HEADER` | `Chronicle-Client-Key-ID` |
| `Chronicle::BODY_SIGNATURE_HEADER` | `Body-Signature-Ed25519` |

## Constructor

**Arguments**:

1. [`HttpInterface`](../Interfaces/HttpInterface.md)
2. `string` - Base URL
3. `string` - Client ID
4. `string` - Client Secret Key (for signing messages)
5. `string` - Server Public Key (for verifying responses)

## Methods

### `signMessageBody`

**Arguments**:

1. `string`

Returns a `string`.

### `responseValid()`

**Arguments**:

1. `int` - HTTP Status Code
2. `string` - Body
3. `array<string, string>[]` - HTTP Headers

Returns a `bool`.

### `publish()`

Publish a [`SignedMessage`](../Protocol/SignedMessage.md) onto the Chroncile ledger.

**Arguments**:

1. [`SignedMessage`](../Protocol/SignedMessage.md)

**Returns** a `bool`.
