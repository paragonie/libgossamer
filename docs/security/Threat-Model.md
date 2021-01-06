# Gossamer Threat Model

Gossamer is a public key infrastructure, without privileged authorities,
intended to be used for securing the supply chain for open source software
projects.

Consequently, the threat model for Gossamer is a superset of the threat
model for any package/dependency management software that integrates with
Gossamer. For that reason, we must generalize these properties (which may
mor may not be pertinent for a given software ecosystem) and extend them.

## Terms and Concepts

* **Providers** own **packages** and have a unique identifying name.
* **Signing Keys** are Ed25519 secret keys.
* **Verification Keys** are Ed25519 public keys.
* **Packages** are discrete software deliverables with a distinct name per Provider.
* **Updates** are changes to a package.
* **Attestations** are statements made by a one provider about an update owned by
  another provider.

## Security Goals

### Transport-Layer Confidentiality and Integrity

All communications MUST be secured with TLS, and MAY be furthermore secured with
[SAPIENT](https://github.com/paragonie/sapient).

### Authenticity

The purpose of Gossamer is to deliver verification keys for software providers, cryptographic
signatures of software updates (and relevant metadata), revocations, and attestations
from other providers about the trustworthiness of other messages.

Consequently, every message in the Gossamer protocol **MUST** be cryptographically signed
with a signing key whose corresponding verification key is currently trusted by the network. 
The chief concern of Gossamer is delivering signatures and verification keys.

### Auditability

Every action performed in the Gossamer protocol MUST be published on an append-only
cryptographic ledger.

All participants in a Gossamer network MUST be able to audit the entire ledger history.

### Userbase Consistency

Every user in an ecosystem that employs the Gossamer protocol can verify that their
peers see the same signed messages, and can verify the entire ledger history from
inception to the current moment, or some subset of the ledger.

### Availability

The Gossamer network SHOULD be resilient to replay attacks intended to keep users
trusting revoked verification keys and/or installing revoked versions of software.

## Scope

Gossamer aims to provide the following data to its users:

* Associations between a Provider's identity and one or more verification keys
* Revocations of lost or compromised keys
* Software update metadata
  * Cryptographic signatures of the update files
  * Version control identifiers (e.g. git commit hashes)
* Revocations of software updates
* Third-party attestations of a particular software update

The following are **NOT** in the scope of Gossamer:

* Cryptocurrency
* The actual delivery of software update files
* Source code version control
* Replacing the Public Key Infrastructure for TLS
* Multi-Ledger Federation
  * Gossamer is not anti-federation, but we do not implement it at this level.
    Other designs are free to implement cross-network federation at a later date,
    if they have sufficient reason to do so.
* Maintaining a database of vulnerable versions of software to block installation
  * Instead, Gossamer provides a revocation mechanism
* Preventing local attacks
  * (e.g. tampering with the code after it's delivered)
* Preventing insecure software from being written in the first place
  * We do support [negative attestations](../specification/Protocol.md#negative-attestations)
    so third parties can say "don't install this update" without the provider revoking it,
    but that's a nice-to-have not a mainline goal of Gossamer.
* Preventing datacenter attacks
* AuthN/AuthZ of providers to the update server
  * Gossamer treats the update server as a black box

## Architecture

There is a bit of variance in the possible architecture of the Gossamer network, due
to trade-offs that some users might want to make.

A minimalistic description of a software distribution system using Gossamer is as follows:

1. The update server, which developers authenticate to through some mechanism, and upload
   new versions of their software. **Providers** write to this, **Users** read from this.
2. An append-only cryptographic ledger, which encompasses all the Gossamer records.
   * You MAY also have one or more replica instances of this ledger (for durability).
3. The client-side library (e.g. this source code repository) that parses messages on the
   ledger to maintain a consensus of which versions of which software are signed by which
   verification keys.

Users can use the client-side library in step 3 to obtain the verification keys that are trusted
for a particular provider at this given point in time. This data is parsed and verified from
the ledger (step 2) and used to validate the files served from the update server (step 1).
Because the user is directly validating and storing a current snapshot of the world, we
call this is the **local trust** configuration.

However, maintaining all of this state can be space-intensive (requiring O(n^2) storage for
O(n) users to each store O(n) updates).

Because of this, another layer of indirection is supported by Gossamer: End users can outsource
the storage to another service which parses the ledger and maintains the records for them. This
is a trust decision that each end user can make for themselves. Because the user isn't storing
and validating these records themselves, we call this the **federated trust** configuration.

(For example: If you're hosting 10,000 managed websites running the same software, having one
server that handles the Gossamer protocol and configuring each website to trust that instead
will save a lot of unnecessary redundancy and wasted disk space.)

It is **NOT** mandatory for Gossamer networks to support both of these configurations.

### Synchronizer

The high-level component responsible for parsing new records in the cryptographic ledger 
and maintaining the current state of the world in a **local trust** configuration is called
the **Synchronizer**. This is also the component that will be publicly interfaced with by
the end user (although probably through some other API).

The Synchronizer is responsible for verifying the integrity of new records in the ledger and
making the appropriate changes to the local snapshot. This includes making all access control
and permission decisions (i.e. *Was this signed by a verification key owned by the same Provider?*).

Further reading:

* [Reference Documentation for the `Synchronizer` class](../reference/Synchronizer.md)

## Attack Profiles

This section explores the types of attacks relevant to the [Architecture](#architecture) and
[Scope](#scope) of Gossamer.

If an attack scenario is relevant to Gossamer's design or implementation and not covered in
this section, then it should be assumed to be unmitigated until otherwise stated in an update
to this documentation.

### Compromised Update Server

An unauthorized third party that has privileged access to the update server (as described in
the [Architecture](#architecture)).

The unauthorized third party intends to use their privileged position to serve malware to end
users expecting an updated version of software they rely on.

#### Gossamer Mitigation

Updates must be digitally signed by the developer, using a verification key associated with 
the provider identity, before anyone will trust an update enough to install it.

Additionally, new verification keys **MUST** be signed by an existing, currently-trusted, and 
not limited verification key associated with the same provider, in order for the Gossamer
protocol to accept it.

(Exception: Super Providers can sign messages for other Providers, if the network opts to
support this break-glass feature.)

### Poisoned Version Control or Build Servers

An unauthorized third party has slipped malicious code into a software release--either
through the build servers, or the source code repository itself.

As a consequence, a new version of some software contains malware designed to benefit the
unauthorized third party.

#### Gossamer Mitigations

1. The `AttestUpdate` mechanism allows users to design policies where updates are only installed
   after being verified by trusted third parties. These verifications can range from a simple
   spot check of the code to a full-blown security audit.
   
   End users can decide which providers they trust, and for which kinds of attestations, and what 
   threshold needs to be satisfied before an update is accepted. These trust decisions are **NOT**
   advertised to the Gossamer network.
2. The `RevokeUpdate` mechanism can be used to prevent systems from updating to a previously
   trusted software release after the compromise is discovered.

### Theft of a Provider's Signing Key

An unauthorized third party has obtained the signing key for a provider, and intends
to use it to sign malicious updates / keys on behalf of the provider.

#### Gossamer Mitigations 

The optional **Super Provider** feature allows a single Provider for the entire network to handle
extreme revocation scenarios. The Super Provider can revoke all updates/keys affected by the attacker
and approve new verification keys for the compromised provider.

For networks where the Super Provider isn't present, providers can minimize the blast radius of
a potential compromise by using limited keys for day-to-day release signing. (Limited keys can only
be used for `AppendUpdate` messages.)

In either scenario, the malicious updates and any compromised keys are to be revoked.

In the worst case scenario (no Super Provider, a normal key was leaked), the provider identity **MUST**
be burned and all software moved to a new provider identity. The mechanism for this is out of scope
for Gossamer (but is straightforward to implement).

### Government-Mandated Stealth Backdoor

A government agency has demanded a stealth backdoor be introduced into some provider's software.

They might go about this by issuing a court order under seal to the developers.

They might go about this by using their offensive cyber capabilities (a.k.a. NSA or equivalent red team)
to compromise the developers without their knowledge.

#### Gossamer Mitigations

The very nature of Gossamer prevents stealth *anything*: All messages in the network are public and
auditable. It's simply not possible to comply with their demands.

Additionally, systems of interest might rely on third parties to attest for all software releases
before acceptance. (See [`AttestUpdate`](../specification/Protocol.md#attestupdate) for details.)
These attestations can include source code reviews and verifying that each software release is
reproducible from the source code.

If a government decides to forego stealth and carry on with a backdoor, they incur a high risk of
leaking their capabilities to the public. Additionally, they risk weakening other systems in their
own country's infrastructure and putting their own citizens at risk.

### Theft of the *Super Provider's* Signing Keys

In Gossamer networks that support the use of Super Providers--which can perform actions on behalf
of *any* provider: An unauthorized third party has obtained the secret key for the Super Provider.

(This attack profile is not relevant to networks that do not have Super Providers.)

#### Gossamer Mitigations

If the attacker succeeds and then *uses* this capability, they are still required to announce their
activity to the entire network. This provides an audit trail on the attack and allows for a path to
recovery after-the-fact.

**However, if this ever happened, it would still be an all-hands-on-deck, shut-everything-down
emergency.**

Networks that adopt the Super Provider feature **SHOULD** take extra steps to secure those keys--such
as the employment of Hardware Security Modules or airgaps--to minimize the risk of exposure and leakage.

Additionally, the Super Provider **MUST** be used sparingly (i.e. as a break-glass feature for recovering
other providers from a system compromise, or to revoke updates containing malware).

### Cryptographic Ledger Compromised

An unauthorized third party obtains access to the servers that serve the contents of the
append-only cryptographic ledger and intend to forge or alter records.

#### Gossamer Mitigations

Every record is verified by the [Synchronizer](#synchronizer) as it's read from the ledger.
Even privileged access to the ledger only permits an attacker to inject invalid records
that get rejected and consequently skipped by the Synchronizer.

Any attacker that attempts to insert a record that performs an action without a valid
cryptographic signature from the pertinent Provider will be wasting their time and alerting
the world to the compromise.

When the ledger backing a Gossamer network is [Chronicle](https://github.com/paragonie/chronicle):
Replica instances independently verify each record before duplicating them. Additionally,
Chronicle supports cross-signing onto other instances, allowing independent verification
across networks or across replica instances.

## Recommendations

### General Recommendations

1. Default to Local Trust, allow hosting companies to switch to Federated Trust to save space.
2. Define a Super Provider but rarely, if ever, actually use it.
3. Have multiple replica instances of the ledger, and have end users query *those* instead of
   the primary instance. (This allows for better horizontal scaling.)

### Update Server Recommendations

1. Support (or even require) hardware-backed two-factor authentication
   (FIDO U2F, WebAuthn, etc.) to authenticate users.
2. Use a microservice model to keep the keys used for publishing messages
   onto the cryptographic ledger out of the hands of unauthorized third parties
   if the update server is breached.
3. When first rolling out Gossamer support, make sure providers cannot namesquat.
