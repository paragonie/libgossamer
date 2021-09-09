# Libgossamer API Reference - TypeHelperTrait

* **Fully Qualified Trait Name**: `ParagonIE\Gossamer\TypeHelperTrait`

## Methods

### `assert()`

Throw a condition if a statement is not true.

**Arguments**:

1. `bool` Statement
2. `string` Error message if statement is false
3. `class-string` Error/Exception class to instantiate

Does not return a value, but will throw if the Statement is false (or falsy).
