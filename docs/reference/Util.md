# Libgossamer API Reference - Util

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Util`

## Static Methods

### `b64uEncode()`

**Arguments**:

1. `string`

Returns a `string`.

### `strlen()`

1. `string`

Return an `int`.

### `memzero()`

**Arguments**:

1. `string&` - Reference to string variable

### `rawBinary()`

**Arguments**:

1. `string` - Input (possibly encoded?)
2. `int` - Output Length (expected)

Returns a `string`.

### `randomInt()`

**Arguments**:

1. `int` Minimum
2. `int` Maximum

Returns an `int`.

### `secureShuffle()`

Shuffle an array in-place. Does not preserve keys.

**Arguments**:

1. `array&` - Reference to an array
