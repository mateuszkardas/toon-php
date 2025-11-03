# TOON (Token-Oriented Object Notation) - PHP Implementation

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

> **Note:** This is a PHP implementation based on the original TOON format specification and reference implementations from [toon-format/toon](https://github.com/toon-format/toon)

PHP implementation of **Token-Oriented Object Notation (TOON)**, a compact, human-readable serialization format designed for passing structured data to Large Language Models with significantly reduced token usage.

TOON is intended for LLM input, not output. It's particularly efficient for **uniform arrays of objects** – multiple fields per row, same structure across items.

## Features

- 💸 **Token-efficient:** typically 30–60% fewer tokens than JSON
- 🤿 **LLM-friendly guardrails:** explicit lengths and fields enable validation
- 🍱 **Minimal syntax:** removes redundant punctuation (braces, brackets, most quotes)
- 📐 **Indentation-based structure:** like YAML, uses whitespace instead of braces
- 🧺 **Tabular arrays:** declare keys once, stream data as rows
- ✅ **Full compatibility:** Implements the [TOON v1.3 specification](https://github.com/toon-format/spec)

## Installation

Install via Composer:

```bash
composer require mateuszkardas/toon
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Toon\Toon;

// Data to encode
$data = [
    'users' => [
        ['id' => 1, 'name' => 'Alice', 'role' => 'admin'],
        ['id' => 2, 'name' => 'Bob', 'role' => 'user'],
    ],
];

// Encode to TOON
$encoded = Toon::encode($data);
echo $encoded;
// Output:
// users[2]{id,name,role}:
//   1,Alice,admin
//   2,Bob,user

// Decode back to PHP
$decoded = Toon::decode($encoded);
print_r($decoded);
```

## Why TOON?

AI is becoming cheaper and more accessible, but larger context windows allow for larger data inputs as well. **LLM tokens still cost money** – and standard JSON is verbose and token-expensive:

```json
{
  "users": [
    { "id": 1, "name": "Alice", "role": "admin" },
    { "id": 2, "name": "Bob", "role": "user" }
  ]
}
```

TOON conveys the same information with **fewer tokens**:

```
users[2]{id,name,role}:
  1,Alice,admin
  2,Bob,user
```

## API Reference

### Encoding

```php
Toon::encode(mixed $input, ?EncodeOptions $options = null): string
```

Convert a PHP value to TOON format.

**Options:**
- `indent` (int): Number of spaces per indentation level (default: 2)
- `delimiter` (string): Delimiter for arrays (',' '|' or "\t") (default: comma)
- `lengthMarker` (string): Optional marker to prefix array lengths ("#" or "") (default: empty)

**Example:**

```php
use Toon\Toon;
use Toon\EncodeOptions;
use Toon\Constants;

$options = new EncodeOptions(
    indent: 4,
    delimiter: Constants::DELIMITER_PIPE,
    lengthMarker: '#'
);

$result = Toon::encode($data, $options);
```

### Decoding

```php
Toon::decode(string $input, ?DecodeOptions $options = null): mixed
```

Convert TOON format to a PHP value.

**Options:**
- `indent` (int): Number of spaces per indentation level (default: 2)
- `strict` (bool): Enforce strict validation (default: true)

**Example:**

```php
use Toon\Toon;
use Toon\DecodeOptions;

$options = new DecodeOptions(
    indent: 4,
    strict: false
);

$result = Toon::decode($toonString, $options);
```

### Convenience Methods

```php
// Shorthand for encode with defaults
$toon = Toon::stringify($data);

// Shorthand for decode with defaults
$data = Toon::parse($toonString);
```

## Format Overview

### Primitives

```php
$data = [
    'name' => 'Alice',
    'age' => 30,
    'active' => true,
    'deleted' => null,
];

// TOON:
// name: Alice
// age: 30
// active: true
// deleted: null
```

### Objects (Nested)

```php
$data = [
    'user' => [
        'name' => 'Alice',
        'email' => 'alice@example.com',
    ],
];

// TOON:
// user:
//   name: Alice
//   email: alice@example.com
```

### Arrays (Inline)

```php
$data = [
    'tags' => ['php', 'toon', 'serialization'],
    'scores' => [95, 87, 92],
];

// TOON:
// tags[3]: php,toon,serialization
// scores[3]: 95,87,92
```

### Arrays (Tabular) - Most Efficient

```php
$data = [
    'users' => [
        ['id' => 1, 'name' => 'Alice', 'role' => 'admin'],
        ['id' => 2, 'name' => 'Bob', 'role' => 'user'],
        ['id' => 3, 'name' => 'Charlie', 'role' => 'user'],
    ],
];

// TOON:
// users[3]{id,name,role}:
//   1,Alice,admin
//   2,Bob,user
//   3,Charlie,user
```

### Arrays (List)

```php
$data = [
    'items' => [
        ['name' => 'Item A', 'nested' => ['x' => 1]],
        ['name' => 'Item B', 'nested' => ['x' => 2]],
    ],
];

// TOON:
// items[2]:
//   - name: Item A
//     nested:
//       x: 1
//   - name: Item B
//     nested:
//       x: 2
```

## String Escaping

TOON properly escapes special characters:

```php
$data = [
    'text' => "Line 1\nLine 2\tTabbed",
    'quoted' => 'She said "Hello"',
    'path' => 'C:\\Users\\file.txt',
];

// TOON:
// text: "Line 1\nLine 2\tTabbed"
// quoted: "She said \"Hello\""
// path: "C:\\Users\\file.txt"
```

## Custom Delimiters

```php
use Toon\Constants;

// Pipe delimiter
$options = new EncodeOptions(delimiter: Constants::DELIMITER_PIPE);
// Output: users[2]{id,name}:
//           1|Alice
//           2|Bob

// Tab delimiter
$options = new EncodeOptions(delimiter: Constants::DELIMITER_TAB);
// Output: users[2]{id,name}:
//           1	Alice
//           2	Bob
```

## Length Markers

```php
$options = new EncodeOptions(lengthMarker: '#');
// Output: users[#2]{id,name}:
//           1,Alice
//           2,Bob
```

## Examples

See the `/examples` directory for more comprehensive examples:
- `examples/basic.php` - Basic usage examples
- `examples/advanced.php` - Advanced features and use cases

Run examples:
```bash
php examples/basic.php
php examples/advanced.php
```

## Requirements

- PHP 8.1 or higher

## Testing

```bash
composer install
composer test
```

## Notes and Limitations

- **For LLM input only:** TOON is optimized for human-to-LLM communication, not LLM output
- **Best for tabular data:** Maximum efficiency with uniform arrays of objects
- **Deeply nested data:** JSON may be more efficient for complex nested structures
- **Empty arrays:** Treated as empty objects `[]` in the round-trip

## Use Cases

- Passing large datasets to LLMs (GPT, Claude, etc.)
- Reducing token costs in AI applications
- Serializing tabular data for prompts
- Configuration files for AI agents
- Data exchange in token-constrained environments

## Related Projects

- [toon-format/toon](https://github.com/toon-format/toon) - Original TypeScript/JavaScript implementation
- [toon-format/spec](https://github.com/toon-format/spec) - TOON format specification
- [mateuszkardas/toon-go](https://github.com/mateuszkardas/toon-go) - Go implementation

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Author

**Mateusz Kardaś**
- GitHub: [@mateuszkardas](https://github.com/mateuszkardas)
- Repository: [https://github.com/mateuszkardas/toon-php](https://github.com/mateuszkardas/toon-php)

## Acknowledgments

Based on the TOON format specification and reference implementation by the toon-format team.
