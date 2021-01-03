# Libgossamer API Reference - DbInterface

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Client\GossamerClient`

## Constructor

**Arguments**:

1. [`TrustModeInterface`](TrustModeInterface.md)
2. [`AttestPolicy`](AttestPolicy.md) or NULL -- defaults to NULL
3. `int` - Algorithm identifier (see [`ParagonIE\Gossamer\Release\Common`](../Release/Common.md))
   -- defaults to the constant for Ed25519 with BLAKE2b

## Class Methods

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

