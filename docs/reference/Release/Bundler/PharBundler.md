# Libgossamer API Reference - Release - Bundler - PharBundler

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Release\Bundler\PharBundler`

This class inherits all methods from [AbstractBundler](AbstractBundler.md).

## Methods

### `setDefaultStubFilename()`

Sets the filename for the default stub in the PHP Archive.

**Arguments**:

1. `string` - Filename (e.g. `index.php`)

Returns this object.

### `bundle()`

See [ReleaseBundlerInterface::bundle()](../../Interfaces/ReleaseBundlerInterface.md#bundle).

> **Warning**: This method cannot be called unless `phar.readonly = 0` in your php.ini configuration. 

**Arguments**:

1. `string` - Output file path

Returns TRUE on success and FALSE on failure.

This creates a Phar archive at the destination output file.
