<?php

declare(strict_types=1);

namespace Toon;

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
