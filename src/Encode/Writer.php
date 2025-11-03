<?php

declare(strict_types=1);

namespace Toon\Encode;

use Toon\Constants;

/**
 * Line writer utility for building TOON output
 *
 * @author Mateusz Kardaś
 */
class Writer
{
    private array $lines = [];
    
    public function __construct(
        private int $indent = Constants::DEFAULT_INDENT
    ) {}
    
    /**
     * Add a line at the specified depth
     */
    public function push(int $depth, string $content): void
    {
        $indentation = str_repeat(' ', $depth * $this->indent);
        $this->lines[] = $indentation . $content;
    }
    
    /**
     * Add a list item line (with hyphen prefix)
     */
    public function pushListItem(int $depth, string $content): void
    {
        $indentation = str_repeat(' ', $depth * $this->indent);
        $this->lines[] = $indentation . Constants::LIST_ITEM_PREFIX . $content;
    }
    
    /**
     * Get the complete output as a string
     */
    public function toString(): string
    {
        return implode("\n", $this->lines);
    }
    
    /**
     * Get all lines
     */
    public function getLines(): array
    {
        return $this->lines;
    }
    
    /**
     * Check if writer is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->lines);
    }
}
