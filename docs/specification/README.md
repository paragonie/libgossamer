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

> **Next**: [The Gossamer Protocol](Protocol.md)
