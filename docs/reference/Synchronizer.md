# Libgossamer API Reference - Synchronizer

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Synchronizer`

The `Synchronizer` class automates the tedious task of reading from the ledger,
parsing [`Action`](Protocol/Action.md) objects from the
[`SignedMessage`](Protocol/SignedMessage.md) objects defined in the ledger, and
then performing each action against the local database.

## Constructor

**Arguments**:

1. [`DbInterface`](DbInterface.md)
2. [`HttpInterface`](HttpInterface.md)
3. [`VerifierInterface`](VerifierInterface.md)
4. `array` - Pool of ledgers. Each item in this array must be an array with the following keys:
   1. `url`: `string`
   2. `public-key`: `string`
   3. `trust`: `string`
5. `string` - Super Provider's name

## Methods

### `addToPool()`

**Arguments**:

1. `string` - URL
2. `string` - Public Key
3. `string` - Trust Level (optional)

Returns this `Synchronizer` object.

### `extractSourceAndPeers`

Returns an **array**. The element at index 0 is the "source". Index 1 is an array of "peers".

Both the source and each peer will an array with the following keys:

1. `url`: `string`
2. `public-key`: `string`
3. `trust`: `string`

### `getSource()`

**Arguments**:

1. `array` - Configuration

**Returns** an object that implements [`SourceInterface`](SourceInterface.md).

### `getVerifier()`

**Arguments**:

1. `array` - Peers

**Returns** an object that implements [`VerifierInterface`](VerifierInterface.md).

### `sync()`

Keep calling [`transcribe()`](#transcribe) until we run out of upstream messages to copy/parse,
or we encounter a [`GossamerException`](GossamerException.md).

**Returns** a `bool`.

### `transcribe()`

Verify signatures, extract the [`Action`](Protocol/Action.md), then apply it on
the Synchronizer's [`DbInterface`](DbInterface.md) object.

**Arguments**:

1. `SignedMessage[]`
2. `VerifierInterface`

**Returns** a `bool`.
