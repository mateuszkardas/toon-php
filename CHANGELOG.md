# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-11-03

### Added
- Initial release of TOON PHP implementation
- Full encoding support for all PHP types
- Full decoding support for TOON format
- Support for all three delimiters (comma, pipe, tab)
- Optional length markers (#)
- Strict and non-strict validation modes
- Configurable indentation
- Comprehensive string escaping and unescaping
- Automatic array format selection (inline, tabular, list)
- Support for nested objects and arrays
- Object normalization (stdClass, JsonSerializable, DateTime)
- Complete TOON v1.3 specification compliance
- Detailed documentation and examples
- MIT License

### Features
- `Toon::encode()` - Encode PHP values to TOON format
- `Toon::decode()` - Decode TOON format to PHP values
- `Toon::stringify()` - Convenience method for encoding
- `Toon::parse()` - Convenience method for decoding
- `EncodeOptions` - Configurable encoding options
- `DecodeOptions` - Configurable decoding options

### Modules
- **Shared utilities**: String handling, validation, literal parsing
- **Encode module**: Encoders, primitives, normalization, writer
- **Decode module**: Decoders, parser, scanner, line cursor

### Examples
- Basic usage examples (`examples/basic.php`)
- Advanced examples (`examples/advanced.php`)
- Test file for quick validation (`test_basic.php`)

### Documentation
- README.md with installation and usage guide
- IMPLEMENTATION.md with technical details
- Inline code documentation with PHPDoc
- MIT License file

[1.0.0]: https://github.com/mateuszkardas/toon-php/releases/tag/v1.0.0
