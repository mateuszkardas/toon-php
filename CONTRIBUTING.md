# TOON PHP - Development Setup

## Quick Start

### 1. Install Dependencies

```bash
composer install
```

### 2. Run Basic Test

```bash
php test_basic.php
```

### 3. Run Examples

```bash
php examples/basic.php
php examples/advanced.php
```

## Development

### Project Structure

```
toon-php/
├── src/                  # Source code
│   ├── Toon.php         # Main API
│   ├── Constants.php    # Constants
│   ├── Types.php        # Type definitions
│   ├── Shared/          # Utilities
│   ├── Encode/          # Encoding module
│   └── Decode/          # Decoding module
├── examples/            # Usage examples
├── tests/               # Unit tests (TODO)
└── vendor/              # Dependencies (generated)
```

### Adding Tests

Create PHPUnit tests in the `tests/` directory:

```php
<?php

namespace Toon\Tests;

use PHPUnit\Framework\TestCase;
use Toon\Toon;

class ToonTest extends TestCase
{
    public function testBasicEncoding(): void
    {
        $data = ['name' => 'Alice'];
        $encoded = Toon::encode($data);
        $this->assertEquals("name: Alice", $encoded);
    }
}
```

Run tests:
```bash
composer test
```

### Code Style

This project follows PSR-12 coding standards.

### Requirements

- PHP 8.1 or higher
- Composer

## GitHub Repository

This project is hosted at: https://github.com/mateuszkardas/toon-php

## License

MIT License - See LICENSE file for details.
