# Gossamer Protocol Specification

The aim of Gossamer is to allow automatic key/update trust in a way that can be
audited by independent third parties.

The basic idea of Gossamer is simple: Use verifiable data structures to create
a live snapshot of the current state of affairs for public keys and software
updates.

This requires that our protocol have a well-defined grammar, and a
straightforward flow to the components.

## Gossamer Overview

**Gossamer** requires a **ledger** (specifically a cryptographic ledger, such
as [Chronicle](https://github.com/paragonie/chronicle) or a 
[Trillian](https://github.com/google/trillian) personality). This ledger is
an append-only, immutable, replicated record of **signed messages** which
dictate changes (**actions**) to be applied to the **local keystore**.

When you publish a new record to the ledger, it will ultimately be reflected in
the local keystore of every participant in your network.

There are four types of actions that can be performed in the Gossamer protocol:

1. `AppendKey` - Adds a **verification key** (Ed25519 Public Key) to the local
   keystore. The new verification key is bound cryptographically a specific
   identity (called a **provider**), which can sign updates for **packages**.
   * In Composer lingo: **provider** is a `vendor`.
   * The first **action** for any given provider **MUST** be an `AppendKey`
     operation. All subsequent `AppendKey` operations must be signed by either
     an existing trusted verification key for this **provider**, or the
     **Super Provider**. (Support for the Super Provider is optional.)
2. `RevokeKey` - Revokes a **verification key** from the local keystore.
   This should only be used in the event of a key compromise.
3. `AppendUpdate` - Releases a new version of a **package** owned by the
   **provider**. Only metadata and signature information is stored in the
   ledger; the actual update file must be served separately (i.e. through
   the normal channels).
4. `RevokeUpdate` - Revokes a specific update. This should only be used in the
   event of malware or significant stability concerns.

Internally, these keywords are referred to as `verbs`.

### Security Goals and Desired Properties
 
* **Existential Forgery Resistance**: It should be computationally infeasible
  for an adversary to forge a **signed message** for any **provider**, 
  without access to their **signing key** (Ed25519 Secret Key). This property
  implies **integrity** and **authenticity**.
 
  This property is satisfied by Ed25519.

* **Append-Only**: It should be computationally infeasible for anyone to insert
  a record in the history of the ledger without breaking the subsequent
  hashes in the chain or tree.

  This property is satisfied by the cryptographic ledgers.

* **Confidentiality**: So long as the **signing key** is securely generated, it
  should be computationally infeasible for an adversary to recover the signing
  key from a large quantity of **signed messages**, even if the signing machine
  has an insecure random number generator.
  
  This property is satisfied by Ed25519.

* **Availability**: The system should be resilient even if network availability
  cannot be guaranteed.
  
  This property is satisfied by cryptographic ledger replication and local or
  federated keystores.

* **Deterministic**: A blank keystore that synchronizes the cryptographic ledger
  and processes the **signed messages** in order should arrive at the same final
  state as every other **local keystore** in the network.
  
* **Backdoor Resistance**: Targeted attempts to introduce a stealth backdoor will
  fail for one of several reasons:
  
  1. Everyone is guaranteed to see the same copy of the ledger, which prevents
     targeted attacks.
  2. The contents of the ledger are public and replicated, which prevents silent
     attacks.
  3. Additionally, nation state adversaries have a negative incentive: Since they
     have to make everyone less secure in order to perform a targeted attack
     (which, also, will **NOT** be silent), such an attack will put their own
     citizens at risk.

## The Gossamer Protocol

### Serialization Formats

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

### The Super Provider

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

### Actions and Validation Rules

#### AppendKey

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

#### RevokeKey

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

#### AppendUpdate

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

#### RevokeUpdate

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
