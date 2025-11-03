<?php

declare(strict_types=1);

namespace Toon;

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
