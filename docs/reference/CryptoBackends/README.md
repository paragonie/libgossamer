# Backends

This contains the code to integrate libgossamer with various cryptography
backends that provide cryptographic signatures. This allows projects to
extend the [Signer](../Release/Signer.md) and [Verifier](../Release/Verifier.md) classes
to support Hardware Security Modules (HSMs).

We ship with a single backend (`SodiumBackend`) which uses Libsodium to
provide Ed25519 signatures of the hashes of release files.
