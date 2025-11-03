<?php

declare(strict_types=1);

namespace Toon\Decode;

use Toon\ParsedLine;
use Toon\BlankLineInfo;
use Toon\ScanResult;

/**
 * Line cursor for navigating parsed lines
 *
 * @author Mateusz Kardaś
 */
class LineCursor
{
    private int $index = 0;
    
    /**
     * @param ParsedLine[] $lines
     * @param BlankLineInfo[] $blankLines
     */
    public function __construct(
        private array $lines,
        private array $blankLines
    ) {}
    
    /**
     * Get blank lines
     */
    public function getBlankLines(): array
    {
        return $this->blankLines;
    }
    
    /**
     * Peek at current line without advancing
     */
    public function peek(): ?ParsedLine
    {
        if ($this->index >= count($this->lines)) {
            return null;
        }
        return $this->lines[$this->index];
    }
    
    /**
     * Get current line and advance
     */
    public function next(): ?ParsedLine
    {
        if ($this->index >= count($this->lines)) {
            return null;
        }
        $line = $this->lines[$this->index];
        $this->index++;
        return $line;
    }
    
    /**
     * Get previously consumed line
     */
    public function current(): ?ParsedLine
    {
        if ($this->index === 0) {
            return null;
        }
        return $this->lines[$this->index - 1];
    }
    
    /**
     * Advance cursor
     */
    public function advance(): void
    {
        $this->index++;
    }
    
    /**
     * Check if at end
     */
    public function atEnd(): bool
    {
        return $this->index >= count($this->lines);
    }
    
    /**
     * Get total number of lines
     */
    public function length(): int
    {
        return count($this->lines);
    }
}
