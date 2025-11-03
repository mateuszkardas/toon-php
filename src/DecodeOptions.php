<?php

declare(strict_types=1);

namespace Toon;

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
