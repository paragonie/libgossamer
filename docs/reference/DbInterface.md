# Libgossamer API Reference - DbInterface

* **Fully Qualified Interface Name**: `ParagonIE\Gossamer\DbInterface`

## Constants

| Constant | Value |
|----------|-------|
| `GOSSAMER_PROTOCOL_VERSION` | `1.0.0` |
| `TABLE_META` | `gossamer_meta` |
| `TABLE_PROVIDERS` | `gossamer_providers` |
| `TABLE_PUBLIC_KEYS` | `gossamer_provider_publickeys` |
| `TABLE_PACKAGES` | `gossamer_packages` |
| `TABLE_PACKAGE_RELEASES` | `gossamer_package_releases` |

## Interface Methods

### `getCheckpointHash()`

The hash of the latest local entry. This method is used for fetching
new records from the cryptographic ledger.

**Returns** a `string`

### `updateMeta()`

Updates the checkpoint hash to the latest retrieved from the 
cryptographic ledger.

**Arguments**:

1. `string` - Checkpoint Hash

**Returns** a `bool`.

### `appendKey()`

Append a new key to the local store.

Note: No identity verification is performed at this step.
It **MUST** have already been checked at a higher level.

**Arguments**:

1. `string` - Provider
2. `string` - Public Key
3. `bool` - Limited key?
4. `string` - Purpose for they key.
5. `array` - Metadata
6. `string` - Hash

**Returns** a `bool`.

### `revokeKey()`

Revoke a public key.

**Arguments**:

1. `string` - Provider
2. `string` - Public Key
3. `array` - Metadata
4. `string` - Hash

**Returns** a `bool`.

### `appendUpdate()`

Appends signature/etc. information about a software update.

**Arguments**:

1. `string` - Provider
2. `string` - Package
3. `string` - Public Key
4. `string` - Release (version)
5. `string` - Signature (of the release file)
6. `array` - Metadata
7. `string` - Hash

**Returns** a `bool`.

### `revokeUpdate()`

Revoke an existing update.

**Arguments**:

1. `string` - Provider
2. `string` - Package
3. `string` - Public Key
4. `string` - Release (version)
5. `array` - Metadata
6. `string` - Hash

**Returns** a `bool`.

### `providerExists()`

Have we seen this Provider before?

**Arguments**:

1. `string` - Provider Name

**Returns** a `bool`.

### `getPublicKeysForProvider()`

Returns the Verification Keys (Ed25519 public keys) for a given provider.

**Arguments**:

1. `string` - Provider Name
2. `?bool` - Limited keys?
   * If you pass as TRUE, this method only returns limited keys.
   * If you pass as FALSE, this method only returns non-limited keys.
   * If you pass as NULL (default), it returns both kinds.
3. `?string` - Purpose?
   * If you pass as an empty string, this method disregards purpose.
   * If you pass as a non-empty string, this method only returns keys that match that purpose.
   * If you pass as NULL (default), it only returns keys without a purpose.

**Returns** an `array` of `string`s. 

### `getPackageId()`

Returns the database primary key for this package.

**Arguments**:

1. `string` - Package Name
2. `int` - Provider ID

**Returns** an `int`.

### `getProviderId()`

Returns the database primary key for this provider.

**Arguments**:

1. `string` - Provider Name

**Returns** an `int`.

### `getPublicKeyId()`

Returns the database primary key for this public key.

**Arguments**:

1. `string` - Public Key
2. `int` - Provier ID

**Returns** an `int`.
