# TOON PHP Examples

This directory contains usage examples for the TOON PHP library.

## Running Examples

Make sure you have installed dependencies:

```bash
cd ..
composer install
```

Then run any example:

```bash
php basic.php
php advanced.php
```

## Examples

### basic.php
Demonstrates fundamental TOON features:
- Simple object encoding
- Tabular arrays (most efficient format)
- Nested structures
- Custom encoding options
- Round-trip encoding/decoding
- Token efficiency comparison with JSON
- Primitive arrays
- Mixed content structures

### advanced.php
Shows advanced use cases:
- Large datasets (GitHub-like repositories)
- E-commerce order structures
- Analytics data
- Different delimiter types (comma, pipe, tab)
- Complex nested configurations
- Special character handling and escaping
- Empty and null value handling

## Creating Your Own Examples

Feel free to create additional examples by copying and modifying these files.

Basic template:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Toon\Toon;

$data = ['your' => 'data'];
$encoded = Toon::encode($data);
echo $encoded . "\n";

$decoded = Toon::decode($encoded);
print_r($decoded);
```

## Documentation

For full API documentation, see:
- [Main README](../README.md)
- [Implementation Details](../IMPLEMENTATION.md)
