# Gossamer Specification

The aim of Gossamer is to allow automatic key/update trust in a way that can be
audited by independent third parties.

The basic idea of Gossamer is simple: Use verifiable data structures to create
a live snapshot of the current state of affairs for public keys and software
updates.

This requires that our protocol have a well-defined grammar, and a
straightforward flow to the components.

# Contents

 * [Gossamer Overview](Overview.md)
   * [Security Goals and Desired Properties](Overview.md#security-goals-and-desired-properties)
 * [The Gossamer Protocol](Protocol.md)
   * [Serialization Formats](Protocol.md#serialization-formats)
   * [The Super Provider](Protocol.md#the-super-provider)
   * [Actions and Validation Rules](Protocol.md#actions-and-validation-rules)
     * [AppendKey](Protocol.md#appendkey)
     * [RevokeKey](Protocol.md#revokekey)
     * [AppendUpdate](Protocol.md#appendupdate)
     * [RevokeUpdate](Protocol.md#revokeupdate)
