# Libgossamer API Reference - Release - Bundler - TarBundler

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Release\Bundler\TarBundler`

This class inherits all methods from [AbstractBundler](AbstractBundler.md).

## Methods

### `setCompression()`

Toggle the type of compression to be used with this Tarball.

**Arguments**:

1. `string|null` - Compression type. Acceptable values:
    * `NULL` - no compression
    * `"gz"` - Gzip Compression
    * `"bz2"` - Bzip2 Compression
    * `"lzma2"` - LZMA 2 Compression

Returns this object.

### `bundle()`

See [ReleaseBundlerInterface::bundle()](../../Interfaces/ReleaseBundlerInterface.md#bundle).

**Arguments**:

1. `string` - Output file path

Returns TRUE on success and FALSE on failure.

This creates a Tar archive at the destination output file.
