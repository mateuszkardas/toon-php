<?php

declare(strict_types=1);

namespace Toon\Shared;

/**
 * String utility functions for escaping and unescaping TOON strings
 *
 * @author Mateusz Kardaś
 */
class StringUtils
{
    /**
     * Escape special characters in a string for encoding
     */
    public static function escape(string $value): string
    {
        $result = '';
        $length = mb_strlen($value);
        
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($value, $i, 1);
            
            switch ($char) {
                case '\\':
                    $result .= '\\\\';
                    break;
                case '"':
                    $result .= '\\"';
                    break;
                case "\n":
                    $result .= '\\n';
                    break;
                case "\r":
                    $result .= '\\r';
                    break;
                case "\t":
                    $result .= '\\t';
                    break;
                default:
                    $result .= $char;
                    break;
            }
        }
        
        return $result;
    }
    
    /**
     * Unescape a string by processing escape sequences
     *
     * @throws \InvalidArgumentException if escape sequence is invalid
     */
    public static function unescape(string $value): string
    {
        $result = '';
        $length = strlen($value);
        $i = 0;
        
        while ($i < $length) {
            if ($value[$i] === '\\') {
                if ($i + 1 >= $length) {
                    throw new \InvalidArgumentException('Invalid escape sequence: backslash at end of string');
                }
                
                $next = $value[$i + 1];
                switch ($next) {
                    case 'n':
                        $result .= "\n";
                        $i += 2;
                        break;
                    case 't':
                        $result .= "\t";
                        $i += 2;
                        break;
                    case 'r':
                        $result .= "\r";
                        $i += 2;
                        break;
                    case '\\':
                        $result .= '\\';
                        $i += 2;
                        break;
                    case '"':
                        $result .= '"';
                        $i += 2;
                        break;
                    default:
                        throw new \InvalidArgumentException("Invalid escape sequence: \\{$next}");
                }
            } else {
                $result .= $value[$i];
                $i++;
            }
        }
        
        return $result;
    }
    
    /**
     * Find the index of the closing double quote in a string,
     * accounting for escape sequences
     *
     * @return int The index of the closing quote, or -1 if not found
     */
    public static function findClosingQuote(string $content, int $start): int
    {
        $i = $start + 1;
        $length = strlen($content);
        
        while ($i < $length) {
            if ($content[$i] === '\\' && $i + 1 < $length) {
                // Skip escaped character
                $i += 2;
                continue;
            }
            if ($content[$i] === '"') {
                return $i;
            }
            $i++;
        }
        
        return -1; // Not found
    }
    
    /**
     * Find the index of a specific character outside of quoted sections
     *
     * @return int The index of the character, or -1 if not found
     */
    public static function findUnquotedChar(string $content, string $char, int $start = 0): int
    {
        $inQuotes = false;
        $i = $start;
        $length = strlen($content);
        
        while ($i < $length) {
            if ($content[$i] === '\\' && $i + 1 < $length && $inQuotes) {
                // Skip escaped character
                $i += 2;
                continue;
            }
            
            if ($content[$i] === '"') {
                $inQuotes = !$inQuotes;
            } elseif (!$inQuotes && $content[$i] === $char) {
                return $i;
            }
            
            $i++;
        }
        
        return -1; // Not found
    }
    
    /**
     * Check if a string needs to be quoted in TOON format
     */
    public static function needsQuotes(string $value): bool
    {
        if ($value === '') {
            return true;
        }
        
        // Check for special characters that require quoting
        $specialChars = [',', ':', '[', ']', '{', '}', '|', "\t", "\n", "\r", '"', '\\'];
        foreach ($specialChars as $char) {
            if (strpos($value, $char) !== false) {
                return true;
            }
        }
        
        // Check if starts/ends with whitespace
        if ($value !== trim($value)) {
            return true;
        }
        
        // Check if looks like a literal (null, true, false, number)
        if (in_array($value, ['null', 'true', 'false'], true)) {
            return true;
        }
        
        if (is_numeric($value)) {
            return true;
        }
        
        return false;
    }
}
