# Libgossamer API Reference - Response extends [Packet](Protocol/Packet.md)

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Response`

This class inherits all methods from [Packet](Protocol/Packet.md).

## Methods

### `extractAllFromChronicleResponse()`

Returns an `array` of [`SignedMessage`](Protocol/SignedMessage.md) objects.

### `extractFromChronicleResponse()` 

**Arguments**:

1. `int` - Index

Returns a [`SignedMessage`](Protocol/SignedMessage.md) object. 
