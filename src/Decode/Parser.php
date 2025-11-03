<?php

declare(strict_types=1);

namespace Toon\Decode;

use Toon\ArrayHeaderInfo;
use Toon\Constants;
use Toon\Shared\StringUtils;
use Toon\Shared\LiteralUtils;

/**
 * Parser utilities for TOON format
 *
 * @author Mateusz Kardaś
 */
class Parser
{
    /**
     * Parse an array header line
     *
     * @return array{0: ?ArrayHeaderInfo, 1: string} [header info, inline values after colon]
     * @throws \InvalidArgumentException on parse error
     */
    public static function parseArrayHeaderLine(string $content, string $defaultDelimiter): array
    {
        $trimmed = ltrim($content, ' ');
        
        // Find the bracket segment
        $bracketStart = -1;
        
        // For quoted keys, find bracket after closing quote
        if (str_starts_with($trimmed, '"')) {
            $closingQuoteIndex = StringUtils::findClosingQuote($trimmed, 0);
            if ($closingQuoteIndex === -1) {
                return [null, ''];
            }
            
            $afterQuote = substr($trimmed, $closingQuoteIndex + 1);
            if (!str_starts_with($afterQuote, '[')) {
                return [null, ''];
            }
            
            $leadingWhitespace = strlen($content) - strlen($trimmed);
            $keyEndIndex = $leadingWhitespace + $closingQuoteIndex + 1;
            $bracketStart = strpos($content, '[', $keyEndIndex);
        } else {
            $bracketStart = strpos($content, '[');
        }
        
        if ($bracketStart === false) {
            return [null, ''];
        }
        
        $bracketEnd = strpos($content, ']', $bracketStart);
        if ($bracketEnd === false) {
            return [null, ''];
        }
        
        // Find the colon
        $colonIndex = $bracketEnd + 1;
        $braceEnd = $colonIndex;
        
        // Check for fields segment
        $braceStart = strpos($content, '{', $bracketEnd);
        if ($braceStart !== false) {
            $colonIdx = strpos($content, ':', $bracketEnd);
            if ($colonIdx !== false && $braceStart < $colonIdx) {
                $foundBraceEnd = strpos($content, '}', $braceStart);
                if ($foundBraceEnd !== false) {
                    $braceEnd = $foundBraceEnd + 1;
                }
            }
        }
        
        $colonIndex = strpos($content, ':', max($bracketEnd, $braceEnd));
        if ($colonIndex === false) {
            return [null, ''];
        }
        
        // Extract key
        $key = '';
        if ($bracketStart > 0) {
            $rawKey = trim(substr($content, 0, $bracketStart));
            if (str_starts_with($rawKey, '"')) {
                $key = self::parseStringLiteral($rawKey);
            } else {
                $key = $rawKey;
            }
        }
        
        $afterColon = trim(substr($content, $colonIndex + 1));
        
        $bracketContent = substr($content, $bracketStart + 1, $bracketEnd - $bracketStart - 1);
        
        // Parse bracket segment
        [$length, $delimiter, $hasLengthMarker] = self::parseBracketSegment($bracketContent, $defaultDelimiter);
        
        // Check for fields
        $fields = null;
        if ($braceStart !== false && $braceStart < $colonIndex) {
            $foundBraceEnd = strpos($content, '}', $braceStart);
            if ($foundBraceEnd !== false && $foundBraceEnd < $colonIndex) {
                $fieldsContent = substr($content, $braceStart + 1, $foundBraceEnd - $braceStart - 1);
                $fieldValues = self::parseDelimitedValues($fieldsContent, $delimiter);
                $fields = [];
                foreach ($fieldValues as $field) {
                    $trimmedField = trim($field);
                    $fields[] = self::parseStringLiteral($trimmedField);
                }
            }
        }
        
        $headerInfo = new ArrayHeaderInfo($key, $length, $delimiter, $fields, $hasLengthMarker);
        
        return [$headerInfo, $afterColon];
    }
    
    /**
     * Parse bracket segment [N] or [#N] or [N|] etc
     *
     * @return array{0: int, 1: string, 2: bool} [length, delimiter, hasLengthMarker]
     */
    private static function parseBracketSegment(string $seg, string $defaultDelimiter): array
    {
        $hasLengthMarker = false;
        $content = $seg;
        
        // Check for length marker
        if (str_starts_with($content, '#')) {
            $hasLengthMarker = true;
            $content = substr($content, 1);
        }
        
        // Check for delimiter suffix
        $delimiter = $defaultDelimiter;
        if (str_ends_with($content, "\t")) {
            $delimiter = Constants::DELIMITER_TAB;
            $content = substr($content, 0, -1);
        } elseif (str_ends_with($content, '|')) {
            $delimiter = Constants::DELIMITER_PIPE;
            $content = substr($content, 0, -1);
        }
        
        $length = (int) $content;
        if ($length < 0 || (string) $length !== $content) {
            throw new \InvalidArgumentException("Invalid array length: {$seg}");
        }
        
        return [$length, $delimiter, $hasLengthMarker];
    }
    
    /**
     * Parse delimited values respecting quotes
     */
    public static function parseDelimitedValues(string $input, string $delimiter): array
    {
        $values = [];
        $current = '';
        $inQuotes = false;
        $length = strlen($input);
        
        for ($i = 0; $i < $length; $i++) {
            $char = $input[$i];
            
            if ($char === '\\' && $i + 1 < $length && $inQuotes) {
                $current .= $char . $input[$i + 1];
                $i++;
                continue;
            }
            
            if ($char === '"') {
                $inQuotes = !$inQuotes;
                $current .= $char;
                continue;
            }
            
            if ($char === $delimiter && !$inQuotes) {
                $values[] = trim($current);
                $current = '';
                continue;
            }
            
            $current .= $char;
        }
        
        // Add last value
        if ($current !== '' || !empty($values)) {
            $values[] = trim($current);
        }
        
        return $values;
    }
    
    /**
     * Parse a key token from content (key: value)
     *
     * @return array{0: string, 1: int} [key, position after colon]
     */
    public static function parseKeyToken(string $content): array
    {
        // Check if starts with quoted key
        if (str_starts_with($content, '"')) {
            $closingQuoteIndex = StringUtils::findClosingQuote($content, 0);
            if ($closingQuoteIndex === -1) {
                throw new \InvalidArgumentException('Unclosed quoted key');
            }
            
            $quotedKey = substr($content, 0, $closingQuoteIndex + 1);
            $key = self::parseStringLiteral($quotedKey);
            
            // Find colon after quote
            $colonIndex = strpos($content, ':', $closingQuoteIndex + 1);
            if ($colonIndex === false) {
                throw new \InvalidArgumentException('Missing colon after key');
            }
            
            return [$key, $colonIndex + 1];
        }
        
        // Unquoted key
        $colonIndex = StringUtils::findUnquotedChar($content, ':');
        if ($colonIndex === -1) {
            throw new \InvalidArgumentException('Missing colon after key');
        }
        
        $key = trim(substr($content, 0, $colonIndex));
        if ($key === '') {
            throw new \InvalidArgumentException('Empty key');
        }
        
        return [$key, $colonIndex + 1];
    }
    
    /**
     * Parse a string literal (with or without quotes)
     */
    public static function parseStringLiteral(string $token): string
    {
        $trimmed = trim($token);
        
        if ($trimmed === '') {
            return '';
        }
        
        // Quoted string
        if ($trimmed[0] === '"') {
            return LiteralUtils::parseQuotedString($trimmed);
        }
        
        // Unquoted string
        return $trimmed;
    }
}
