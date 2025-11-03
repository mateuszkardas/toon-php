<?php

declare(strict_types=1);

namespace Toon\Decode;

use Toon\ParsedLine;
use Toon\BlankLineInfo;
use Toon\ScanResult;

/**
 * Scanner for parsing TOON input into lines
 *
 * @author Mateusz Kardaś
 */
class Scanner
{
    /**
     * Parse input text into lines with depth information
     *
     * @throws \InvalidArgumentException if strict mode validation fails
     */
    public static function toParsedLines(string $source, int $indentSize, bool $strict): ScanResult
    {
        $trimmed = trim($source);
        if ($trimmed === '') {
            return new ScanResult([], []);
        }
        
        $lines = explode("\n", $source);
        $parsed = [];
        $blankLines = [];
        
        foreach ($lines as $i => $raw) {
            $lineNumber = $i + 1;
            $indent = 0;
            
            // Count leading spaces
            $len = strlen($raw);
            while ($indent < $len && $raw[$indent] === ' ') {
                $indent++;
            }
            
            $content = substr($raw, $indent);
            
            // Track blank lines
            if (trim($content) === '') {
                $depth = self::computeDepthFromIndent($indent, $indentSize);
                $blankLines[] = new BlankLineInfo($lineNumber, $indent, $depth);
                continue;
            }
            
            $depth = self::computeDepthFromIndent($indent, $indentSize);
            
            // Strict mode validation
            if ($strict) {
                // Check for tabs in leading whitespace
                $wsEnd = 0;
                while ($wsEnd < $len && ($raw[$wsEnd] === ' ' || $raw[$wsEnd] === "\t")) {
                    $wsEnd++;
                }
                
                if (strpos(substr($raw, 0, $wsEnd), "\t") !== false) {
                    throw new \InvalidArgumentException(
                        "Line {$lineNumber}: tabs are not allowed in indentation in strict mode"
                    );
                }
                
                // Check for exact multiples of indentSize
                if ($indent > 0 && $indent % $indentSize !== 0) {
                    throw new \InvalidArgumentException(
                        "Line {$lineNumber}: indentation must be exact multiple of {$indentSize}, but found {$indent} spaces"
                    );
                }
            }
            
            $parsed[] = new ParsedLine($raw, $depth, $indent, $content, $lineNumber);
        }
        
        // Strict blank line validation
        if ($strict && !empty($blankLines)) {
            foreach ($blankLines as $blankLine) {
                $found = false;
                foreach ($parsed as $line) {
                    if ($line->lineNumber > $blankLine->lineNumber && $line->depth === $blankLine->depth) {
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    throw new \InvalidArgumentException(
                        "Line {$blankLine->lineNumber}: blank line at depth {$blankLine->depth} with no subsequent content at that depth"
                    );
                }
            }
        }
        
        return new ScanResult($parsed, $blankLines);
    }
    
    /**
     * Compute depth from indentation
     */
    private static function computeDepthFromIndent(int $indent, int $indentSize): int
    {
        return (int) floor($indent / $indentSize);
    }
}
