# Libgossamer API Reference - Synchronizer

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Synchronizer`

The `Synchronizer` class automates the tedious task of reading from the ledger,
parsing [`Action`](Protocol/Action.md) objects from the
[`SignedMessage`](Protocol/SignedMessage.md) objects defined in the ledger, and
then performing each action against the local database.

The public method most implementations will want to call is [`sync()`](#sync).

### Security Note

**MOST security decisions** (i.e. which public keys belong to which provider,
whether or not to trust a "Super Provider") are the responsibility of this class,
and the rest of the components assume that this logic has been followed.

Therefore, the implementation of this class (and any code it calls) is crucial
to the secure operation of libgossamer.

## Constructor

**Arguments**:

1. [`DbInterface`](Interfaces/DbInterface.md)
2. [`HttpInterface`](Interfaces/HttpInterface.md)
3. [`LedgerVerifierInterface`](Interfaces/LedgerVerifierInterface.md)
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

**Returns** an object that implements [`SourceInterface`](Interfaces/SourceInterface.md).

### `getVerifier()`

**Arguments**:

1. `array` - Peers

**Returns** an object that implements [`LedgerVerifierInterface`](Interfaces/LedgerVerifierInterface.md).

### `sync()`

Keep calling [`transcribe()`](#transcribe) until we run out of upstream messages to copy/parse,
or we encounter a [`GossamerException`](GossamerException.md).

**Returns** a `bool`.

### `transcribe()`

Verify signatures, extract the [`Action`](Protocol/Action.md), then apply it on
the Synchronizer's [`DbInterface`](Interfaces/DbInterface.md) object.

**Arguments**:

1. `SignedMessage[]`
2. `LedgerVerifierInterface`

**Returns** a `bool`.
