# Libgossamer API Reference - Client - TrustMode - FederatedTrust

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Client\TrustMode\FederatedTrust`

## Constructor

**Arguments**:

1. [HttpInterface](../../Interfaces/HttpInterface.md)
2. `string` URL to the [Gossamer Server](https://github.com/paragonie/gossamer-server)

## Class Methods

### `getUpdateInfo()`

Returns an [`UpdateFile`](UpdateFile.md) object.

**Arguments**:

1. `string` Provider name
2. `string` Package name
3. `string` Version identifier

### `getVerificationKeys()`

Returns an array of strings.

**Arguments**:

1. `string` Provider name
