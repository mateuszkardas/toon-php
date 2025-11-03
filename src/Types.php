<?php

declare(strict_types=1);

namespace Toon;

/**
 * Encode options for TOON format
 *
 * @author Mateusz Kardaś
 */
class EncodeOptions
{
    public function __construct(
        public int $indent = Constants::DEFAULT_INDENT,
        public string $delimiter = Constants::DEFAULT_DELIMITER,
        public string $lengthMarker = Constants::DEFAULT_LENGTH_MARKER
    ) {
        if ($indent < 1) {
            throw new \InvalidArgumentException('Indent must be at least 1');
        }
        
        if (!in_array($delimiter, [Constants::DELIMITER_COMMA, Constants::DELIMITER_TAB, Constants::DELIMITER_PIPE])) {
            throw new \InvalidArgumentException('Invalid delimiter. Must be comma, tab, or pipe');
        }
        
        if ($lengthMarker !== '' && $lengthMarker !== '#') {
            throw new \InvalidArgumentException('Length marker must be empty or "#"');
        }
    }
}

/**
 * Decode options for TOON format
 *
 * @author Mateusz Kardaś
 */
class DecodeOptions
{
    public function __construct(
        public int $indent = Constants::DEFAULT_INDENT,
        public bool $strict = true
    ) {
        if ($indent < 1) {
            throw new \InvalidArgumentException('Indent must be at least 1');
        }
    }
}

/**
 * Array header information
 *
 * @author Mateusz Kardaś
 */
class ArrayHeaderInfo
{
    public function __construct(
        public string $key,
        public int $length,
        public string $delimiter,
        public ?array $fields = null,
        public bool $hasLengthMarker = false
    ) {}
}

/**
 * Parsed line information
 *
 * @author Mateusz Kardaś
 */
class ParsedLine
{
    public function __construct(
        public string $raw,
        public int $depth,
        public int $indent,
        public string $content,
        public int $lineNumber
    ) {}
}

/**
 * Blank line information
 *
 * @author Mateusz Kardaś
 */
class BlankLineInfo
{
    public function __construct(
        public int $lineNumber,
        public int $indent,
        public int $depth
    ) {}
}

/**
 * Scan result
 *
 * @author Mateusz Kardaś
 */
class ScanResult
{
    /**
     * @param ParsedLine[] $lines
     * @param BlankLineInfo[] $blankLines
     */
    public function __construct(
        public array $lines,
        public array $blankLines
    ) {}
}
