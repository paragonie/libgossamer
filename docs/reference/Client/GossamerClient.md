# Libgossamer API Reference - Client - GossamerClient

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Client\GossamerClient`

## Constructor

**Arguments**:

1. [`TrustModeInterface`](TrustModeInterface.md)
2. [`AttestPolicy`](AttestPolicy.md) or NULL -- defaults to NULL
   which results in a blank attestation policy
3. `int` - Algorithm identifier (see [`ParagonIE\Gossamer\Release\Common`](../Release/Common.md))
   -- defaults to the constant for Ed25519 with BLAKE2b

## Class Methods

### `getVerificationKeys()`

Get the currently trusted verification keys for a given provider
(and optional "purpose").

The "purpose" flag is not used with Gossamer, but is included so that
developers can include their own public keys for higher-level protocols
that build atop Gossamer.

Any keys with a "purpose" set will be excluded from this, unless that
exact purpose string is provided as the optional second argument.

**Arguments**:

1. `string` Provider name
2. `?string` Purpose (or NULL)

Returns an array of strings representing the encoded
verification keys for the given provider that are currently
trusted by the entire network.

### `getUpdate()`

Get an `UpdateFile` object that can be used to decide whether a file
is safe to install (i.e. if its signature matches and passes the
configured Attestation Policy).

**Arguments**:

You can call this method one of two ways.

1. With two arguments:
   1. Normalized package name (`provider/package`)
   2. Version identifier (e.g. `v1.2.1`)
2. With three arguments:
   1. Provider name 
   2. Package name
   3. Version identifier (e.g. `v1.2.1`)

**Returns** an [`UpdateFile`](UpdateFile.md).

