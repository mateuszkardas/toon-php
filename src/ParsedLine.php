<?php

declare(strict_types=1);

namespace Toon;

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
