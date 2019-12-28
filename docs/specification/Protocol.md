# The Gossamer Protocol

## Serialization Formats

**Actions** are serialized as JSON-encoded strings inside of a **Message**.
Example message:

```json
{
  "verb": "AppendKey",
  "provider": "foo",
  "public-key": "..."
}
```

**Messages** that are signed by a **provider** become a **SignedMessage**.
Example signed message:

```json
{
  "signature": "...",
  "message": "{\"verb\":\"AppendKey\",\"provider\":\"foo\",\"public-key\":\"...\"}",
  "provider": "foo",
  "public-key": ""
}
```

Note that the outermost `public-key` is blank on the first `AppendKey`
operation for a given **provider**.

**SignedMessages** are serialized as a JSON string and stored in the ledger.

Although pretty-printing was used in the examples above, it is strictly optional.
In libgossamer, we omit pretty-printing in favor of a flat, one-line JSON string,
like so:

```json
{"signature":"...","message":"{\"verb\":\"AppendKey\",\"provider\":\"foo\",\"public-key\":\"...\"}","provider":"foo","public-key":""}
```

At the deepest level, `signature` covers the `message` contents, as a literal
string. Signatures MUST be base64url-encoded Ed25519 signatures.
 
Base64url is defined in [RFC 4648](https://tools.ietf.org/html/rfc4648).  
Ed25519 is defined in [RFC 8032](https://tools.ietf.org/html/rfc8032).

Each ledger may impose additional internal serialization rules. Gossamer treats
these as a black box:

 * Signed messages are serialized and written to the ledger using an appropriate 
   **scribe** class. 
 * These entries are read from the ledger with an appropriate **source** class, 
   and deserialized as a `SignedMessage` object.

## The Super Provider

Typically, providers manage their own signing keys and share the associated
verification keys with the world. Therefore, all **actions** related to any
public key or software update owned by that provider **SHOULD** be signed by that
provider's keys.

However, many systems will need an emergency "break glass" feature to allow
providers to recover from key loss or compromise. To meet this real-world
operational need, Gossamer allows a single special provider (dubbed the 
**Super Provider**) the power to sign operations on behalf of other providers.

The Super Provider **MUST NOT** be a regular provider that sees routine,
non-emergency operational usage. 

For example, if the entity that manages the Super Provider is FooBar LLC, which
occupies the **foo-bar** supplier, then the Super Provider **MUST NOT** be
**foo-bar**. Instead, a separate provider identity (e.g. **foo-bar-emergency**)
should be declared, with its own set of signing keys, for the sole purpose
of handling emergency situations.

The Super Provider **MAY** issue any action (`AppendKey`, `RevokeKey`, 
`AppendUpdate`, `RevokeUpdate`) for any provider. However, it **CANNOT** bypass
the requirement for all transactions to be published in the append-only
cryptographic ledger.

## Actions and Validation Rules

### AppendKey

An `AppendKey` action **MUST** contain the following fields:

| Name         | Description                         |
|--------------|-------------------------------------|
| `verb`       | Must be `AppendKey`.                |
| `provider`   | Provider name.                      |
| `public-key` | Base64url-encoded verification key. |

If the `provider` is not found in the local key store when an `AppendKey`
action is encountered, then it **MUST** be created in the local key store.

If the `provider` was found, the `SignedMessage` that encapsulates this Action 
must be signed by the same provider (or the Super Provider, if applicable).

When this action is performed, it should insert a new row in a database.

### RevokeKey

A `RevokeKey` action **MUST** contain the following fields:

| Name         | Description                         |
|--------------|-------------------------------------|
| `verb`       | Must be `RevokeKey`.                |
| `provider`   | Provider name.                      |
| `public-key` | Base64url-encoded verification key. |

If the `provider` is not found in the local key store when a `RevokeKey` action
is encountered, an error **MUST** be raised. In most languages, this means
throwing an Exception.

The `SignedMessage` that encapsulates this Action must be signed by the provider
(or the Super Provider, if applicable).

When this action is performed, it should update an existing row in a database
to mark the row as revoked.

### AppendUpdate

An `AppendUpdate` action **MUST** contain the following fields:

| Name         | Description                              |
|--------------|------------------------------------------|
| `verb`       | Must be `AppendUpdate`.                  |
| `provider`   | Provider name.                           |
| `public-key` | Base64url-encoded verification key.      |
| `signature`  | Signature of the update file.            |
| `package`    | Name of the package (owned by provider). |
| `release`    | Version being released.                  |

If the `provider` is not found in the local key store when an `AppendUpdate`
action is encountered, an error **MUST** be raised. In most languages, this
means throwing an Exception.

The `SignedMessage` that encapsulates this Action must be signed by the
correct provider (or the Super Provider, if applicable).

When this action is performed, it should insert a new row in a database.

### RevokeUpdate

A `RevokeUpdate` action **MUST** contain the following fields:

| Name         | Description                              |
|--------------|------------------------------------------|
| `verb`       | Must be `RevokeUpdate`.                  |
| `provider`   | Provider name.                           |
| `public-key` | Base64url-encoded verification key.      |
| `package`    | Name of the package (owned by provider). |
| `release`    | Version being released.                  |

If the `provider` is not found in the local key store when an `RevokeUpdate`
action is encountered, an error **MUST** be raised. In most languages, this
means throwing an Exception.

The `SignedMessage` that encapsulates this Action must be signed by the
correct provider (or the Super Provider, if applicable).

When this action is performed, it should update an existing row in a database
to mark the row as revoked.
