# Libgossamer API Reference - Protocol - Action

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Protocol\Action`

An action encapsulates a `verb` and the necessary relevant data to enact some
change on the local database.

## Constructor

**Arguments**:

  1. `string` - Verb (optional)

## Verbs

| Constant                     | Value          | Meaning | 
|------------------------------|----------------|---------|
| `Action::VERB_APPEND_KEY`    | `AppendKey`    | Append a new public key for a new provider. |
| `Action::VERB_REVOKE_KEY`    | `RevokeKey`    | Revoke an existing public key for a given provider. |
| `Action::VERB_APPEND_UPDATE` | `AppendUpdate` | Append an update for a project owned by this provider. |
| `Action::VERB_REVOKE_UPDATE` | `RevokeUpdate` | Revoke an existing update for a project owned by this provider. |

**Special rule:** The first chronological `verb` seen for a given `provider` **MUST** be
an `AppendKey`. The first chronological `verb` seen for a given project owned by any 
`provider` **MUST** be an `AppendUpdate`.

## Static Methods

### `fromMessage()`

**Arguments**:

  1. [`Message`](Message.md) - Message object

**Returns** an instance of `Action`.

This method returns an instance of an `Action` from a given `Message` object.

**This is the preferred way of extracting `Action` objects from `Message` objects.** 

## Methods

### `toMessage()`

**Returns** an instance of [`Message`](Message.md)

### `toSignedMessage()`

**Arguments**:

  1. `string` - Signing Key

**Returns** an instance of `SignedMessage`.

### `getHash()`

**Returns** a `string`.

### `getMeta()`

**Returns** a `string`.

### `getPackage()`

**Returns** a `string`.

### `getProvider()`

**Returns** a `string`.

### `getPublicKey()`

**Returns** a `string`.

### `getRelease()`

**Returns** a `string`.

### `getSignature()`

**Returns** a `string`.

### `getVerb()`

**Returns** a `string`.

### `perform()`

**Arguments**:

  1. [`DbInterface`](../Interfaces/DbInterface.md) - Database

**Returns** a `bool`.

**This is the preferred way of changing a database based on an `Action` definition.**

### `withHash()`

**Arguments**:

  1. `string`

**Returns** a new instance of `Action`.

### `withMeta()`

**Arguments**:

  1. `array`

**Returns** a new instance of `Action`.

### `withPackage()`

**Arguments**:

  1. `string`

**Returns** a new instance of `Action`.

### `withProvider()`

**Arguments**:

  1. `string`

**Returns** a new instance of `Action`.

### `withPublicKey()`

**Arguments**:

  1. `string`

**Returns** a new instance of `Action`.

### `withRelease()`

**Arguments**:

  1. `string`

**Returns** a new instance of `Action`.

### `withSignature()`

**Arguments**:

  1. `string`

**Returns** a new instance of `Action`.

### `withVerb()`

**Arguments**:

  1. `string`

**Returns** a new instance of `Action`.
