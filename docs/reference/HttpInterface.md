# Libgossamer API Reference - HttpInterface

* **Fully Qualified Interface Name**: `ParagonIE\Gossamer\HttpInterface`

## Interface Methods

### `get()`

**Arguments**:

1. `string` - URL

Returns an `array`:

  * `body`: `string`
  * `headers`: `array<array-key, array<array-key, string>>`
  * `status`: `int`

### `post()`

**Arguments**:

1. `string` - URL
2. `string` - POST body
3. `array` - HTTP Headers

Returns an `array`:

  * `body`: `string`
  * `headers`: `array<array-key, array<array-key, string>>`
  * `status`: `int`
