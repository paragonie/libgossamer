# Libgossamer API Reference - Release - Bundler - GitDiffBundler

* **Fully Qualified Class Name**: `ParagonIE\Gossamer\Release\Bundler\GitDiffBundler`

This class inherits all methods from [AbstractBundler](AbstractBundler.md).

## Methods

### `setPreviousIdentifier()`

Sets the git identifier (commit, branch, or tag) to be compared against.

**Arguments**:

1. `string` - Git identifier

Returns this object.

### `bundle()`

See [ReleaseBundlerInterface::bundle()](../../Interfaces/ReleaseBundlerInterface.md#bundle).

**Arguments**:

1. `string` - Output file path
2. `string` - Current git identifier (default: `HEAD`)

Returns TRUE on success and FALSE on failure.

This creates a patch file (in `git diff` format) based on the changes between two git
identifiers (commits, branches, or tags).
