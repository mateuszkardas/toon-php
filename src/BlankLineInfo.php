<?php

declare(strict_types=1);

namespace Toon;

class BlankLineInfo
{
    public function __construct(
        public int $lineNumber,
        public int $indent,
        public int $depth
    ) {}
}
