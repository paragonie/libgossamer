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

An `AppendKey` action **MAY ALSO** Contain the following optional fields:

| Name         | Description                                                        |
|--------------|--------------------------------------------------------------------|
| `meta`       | JSON-encoded string of optional metadata (e.g. Git commit hashes). |
| `limited`    | Boolean (True = Limited key, False = Normal key). Default: False.  |
| `purpose`    | String. Used for identifying keys used for non-Gossamer purposes.  |

If the `provider` is not found in the local key store when an `AppendKey`
action is encountered, then it **MUST** be created in the local key store.

If the `provider` was found, the `SignedMessage` that encapsulates this Action 
must be signed by the same provider (or the Super Provider, if applicable).

An `AppendKey` action **MAY** contain a `limited` field, which must be boolean.
If it is absent, it is implicitly false. The first `AppendKey` for a provider
**MUST NOT** have `limited` set to `TRUE`.

An `AppendKey` action **MAY** also contain a `purpose` field, which must be a
valid UTF-8 string. Any key with a `purpose` **MUST NOT** be used with any other
Gossamer action. The `purpose` field is used to identify public keys shared through
Gossamer that are only intended for non-Gossamer uses (i.e. not for software
updates).

The provider must have at least one non-limited, non-revoked key without a
`purpose` field in order to create limited keys.

**Limited keys can only be used to issue an `AppendUpdate` action.**

**Keys with a `purpose` field cannot be used in Gossamer.**

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

If the `public-key` does not belong to the correct `provider` (or the Super
Provider, if applicable), an error **MUST** be raised. In most languages, this
means throwing an Exception.

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
| `artifact`   | Build artifact type being released.      |

An `AppendUpdate` action **MAY ALSO** Contain the following optional fields:

| Name         | Description                                                        |
|--------------|--------------------------------------------------------------------|
| `meta`       | JSON-encoded string of optional metadata (e.g. Git commit hashes). |

If the `provider` is not found in the local key store when an `AppendUpdate`
action is encountered, an error **MUST** be raised. In most languages, this
means throwing an Exception.

The `SignedMessage` that encapsulates this Action must be signed by the
correct provider (or the Super Provider, if applicable).

When this action is performed, it should insert a new row in a database.

The `artifact` is optional. It can be used to distinguish different deliverables for different
platforms or distribution channels (e.g. for `.zip` files vs `.patch` files).

### RevokeUpdate

A `RevokeUpdate` action **MUST** contain the following fields:

| Name         | Description                              |
|--------------|------------------------------------------|
| `verb`       | Must be `RevokeUpdate`.                  |
| `provider`   | Provider name.                           |
| `public-key` | Base64url-encoded verification key.      |
| `package`    | Name of the package (owned by provider). |
| `release`    | Version being released.                  |
| `artifact`   | Which build artifact type to revoke.     |

If the `provider` is not found in the local key store when an `RevokeUpdate`
action is encountered, an error **MUST** be raised. In most languages, this
means throwing an Exception.

The `SignedMessage` that encapsulates this Action must be signed by the
correct provider (or the Super Provider, if applicable).

When this action is performed, it should update an existing row in a database
to mark the row as revoked.

### AttestUpdate

A `AttestUpdate` action **MUST** contain the following fields:

| Name          | Description                              |
|---------------|------------------------------------------|
| `verb`        | Must be `AttestUpdate`.                  |
| `provider`    | Provider name.                           |
| `package`     | Name of the package (owned by provider). |
| `release`     | Version of the package in question.      |
| `artifact`    | Which build artifact type to attest.     |
| `attestor`    | Provider attesting this package.         |
| `attestation` | See below.                               |

If the `provider` is not found in the local key store when an `AttestUpdate`
action is encountered, an error **MUST** be raised. In most languages, this
means throwing an Exception.

If the `attestor` is not found in the local key store when an `AttestUpdate`
action is encountered, an error **MUST** be raised. In most languages, this
means throwing an Exception.

An attestation is a [NOP](https://en.wikipedia.org/wiki/NOP_(code)) for the
purposes of Gossamer's goals (key management and code-signing). However, other
protocols **MAY** wish to use attestations in order to provide third-party
oversight into the protocol.

Attestations **MUST** be one of the following:

| Attestation    | Meaning                                                              | Connotation |
|----------------|----------------------------------------------------------------------|-------------|
| `vote-against` | The attestor claims that this update should NOT be installed.        | Negative    |
| `reproduced`   | The attestor was able to reproduce this update from the source code. | Positive    |
| `spot-check`   | The attestor provided a spot check for this update.                  | Positive    |
| `code-review`  | The attestor performed a code review for this update.                | Positive    |
| `sec-audit`    | This update passed a security audit performed by the attestor.       | Positive    |

When implementing support for attestations in a protocol that builds atop
Gossamer, you **MAY** treat the above as an enum.

**Warning**: Attestations are immutable and **CANNOT** be revoked.

#### Negative Attestations

* If you want to advise users to not install an update, simply issue a `vote-against`
  attestation. This is useful for identifying that an old version of software SHOULD NOT
  be installed upon the discovery of a security flaw. However, unlike revocation,
  it will only take effect if users trust your `vote-against` attestations and configure
  them into their [attestation policies](../reference/Client/AttestPolicy.md).

#### Positive Attestations

* Reproducing a build package from the source code (`reproduced`) is important,
  but can be performed without human attention.
* A `spot-check` can be done in a hurry, and only certifies that nothing leaped
  out to the reviewer as "obviously malicious or broken".
* A code review (`code-review`) means that someone looked over the contents of
  the release and evaluated their contents carefully. It doesn't necessarily
  imply any sort of security guarantees; merely that this update was peer
  reviewed.
* A security audit (`sec-audit`) is the highest level of attestation that a
  third party can provide. It implies that not only was the release reviewed
  by an independent third party (like a `code-review`), but also that the third
  party in question claims to have discovered no security vulnerabilities in
  this particular version of the software.

## Invalid Messages in the Ledger

Any records in the ledger that do not contain a valid JSON message 
**MUST** be skipped.

Any records in the ledger that contain a valid JSON message, but
do not contain a `verb` field conforming to one of the actions defined in
this specification, **MUST** be skipped.

The above two rules allow a single ledger instance to be used for
multiple purposes without interfering with the normal operation of the
Gossamer protocol.

### Validation

Any records that contain a JSON message and a `verb` field
conforming to an action, but are somehow invalid (as defined by the rules
of each particular `verb`; e.g. missing a mandatory field) but **NOT**
malicious (e.g. not violating a rule about who can author an `AppendKey`), 
**MAY** be skipped, but the discrepancy **SHOULD** also be logged.

Alternatively, this can be a protocol error until the operator manually
skips the invalid message by fast-forwarding to the next ledger record.

### Security

Any records that obey the JSON message rules but violate the security 
requirements of an action (e.g. trying to append an update for a different
provider, except if issued by the Super Provider) must constitute a protocol 
error.

### Protocol Error Handling
 
The protocol will be stopped until a manual action is performed. 

To recover, an operator **MUST** discard the malicious message in order and
fast-forward to the next ledger record. The operator **SHOULD** investigate
the cause of the breakage to ensure they are not the subject of a targeted 
attack.
 
In the event of a network attack that led to invalid records being
replicated, operators **MAY** empty their keystore and replay the entire
protocol from the first record once they are sure the network is consistent
and trustworthy.

Tooling **SHOULD** be provided to allow an operator to specify the new
savepoint (summary hash, Merkle root, etc.) for the cryptographic ledger
powering the Gossamer protocol.
