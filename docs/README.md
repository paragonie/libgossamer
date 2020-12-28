# Libgossamer

## Overview

Gossamer is an authority-free PKI for ensuring **providers** can sign their
updates and manage their own signing keys, and that **users** can verify the
authenticity of new keys and updates.

### Motivation

The aim of Gossamer is to allow automatic key/update trust in a way that can be
audited by independent third parties.

The basic idea of Gossamer is simple: Use verifiable data structures to create
a live snapshot of the current state of affairs for public keys and software
updates.

No X.509, no ASN.1, no expiration dates and [weird rules](https://www.pkisolutions.com/basic-constraints-certificate-extension).

This lets you write code that looks like this:

    $keys = fetch_public_keys('paragonie');

...and then if any keys are compromised, the $keys array will not contain the
revoked ones.

For more information, please refer to the **[security documentation](security)**.

### Components

| **Documentation Name** | **Description** |
|---|---|
| [`Action`](reference/Protocol/Action.md) | Describes a change to make: Adding/revoking public keys or releases. |
| [`Message`](reference/Protocol/Message.md) | A serialized `Action`. |
| [`SignedMessage`](reference/Protocol/SignedMessage.md) | A cryptographically signed (and identity-bound) `Message`. |
| [`Packet`](reference/Protocol/Packet.md) | HTTP request or response (i.e. to/from a cryptographic ledger) |
| [`Scribe`](reference/ScribeInterface.md) | Publishes `SignedMessage`s to a ledger. |
| [`ReleaseSigner`](reference/Release/Signer.md) | Signs a file with your secret key. |
| [`ReleaseVerifier`](reference/Release/Verifier.md) | Verifies a file against your public key. |

### Gossamer PKI Use Cases

Depending on what you're trying to build with libgossamer, you will need a
different subset of our library's built-in functionality.

| **Use Case Name** | **Example** | **Functionality**                |
|-------------------|-------------|----------------------------------|
| **Update Server** | api.wordpress.org | `Scribe`                   |
| **Federated Key Management** | Hosting Provider | `Synchronizer`   |
| **Deployed System** | WordPress blog | `ReleaseVerifier`, `Synchronizer` (optional) |
| **Developer Tools** | PHPStorm | `ReleaseSigner`                   |

## The Life-Cycle of a Gossamer Communication

1. Someone defines an `Action` they wish to perform. Typically this will be done
   through tooling rather than manual code snippets. Each action will have one of
   the following "verbs":
   * `AppendKey` (This must be the first action for a given provider.)
   * `RevokeKey`
   * `AppendUpdate`
   * `RevokeUpdate`
   * `AttestUpdate`
2. The `Action` is serialized and signed with the user's secret key,
   forming a `SignedMessage` object.
3. The `SignedMessage` is serialized and sent to the update server.
4. The update server verifies the incoming `SignedMessage` (performing
   not only integrity checks, but access controls as well).
5. The update server uses the `Scribe` to publish the `SignedMessage`
   onto the cryptographic ledger.
6. Replica nodes verify and mirror the new ledger records.
7. An end system (which can be an individual deployed system or a federated
   standalone key server that deployed systems trust) queries a replica node
   of the cryptographic ledger.
8. The end system downloads each new record and extracts a `SignedMessage`
   object.
9. The end system uses the currently-trusted public keys for a given provider
   to authenticate the `SignedMessage`. If successful, it returns an `Action`.
10. The `Action` is performed against the database.

## Further Reading

* **[API Reference](reference)** - Internals, class definitions, etc. Basic API documentation.
* **[Specification](specification)** - Protocol description to aid cross-platform implementations.
* **[Tutorials](tutorials)** - How to integrate libgossamer into your package ecosystem.
* **[Discussions](discussions.md)** - Blog posts and message board threads worth reading.
