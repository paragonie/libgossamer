# Libgossamer API Reference - LedgerVerifierInterface

* **Fully Qualified Interface Name**: `ParagonIE\Gossamer\Interfaces\LedgerVerifierInterface`

This interface extends both [`LedgerInterface`](LedgerInterface.md) and
[`VerifierInterface`](VerifierInterface.md). 

## Interface Methods

### `signedMessageFound()`

Was this `SignedMessage` found in the ledger?

**Arguments**:

1. `SignedMessage` $signedMessage

Returns a `bool`.
