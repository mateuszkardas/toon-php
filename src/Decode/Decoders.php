<?php

declare(strict_types=1);

namespace Toon\Decode;

use Toon\ArrayHeaderInfo;
use Toon\Shared\LiteralUtils;
use Toon\Shared\ValidationUtils;

/**
 * Main decoder for TOON format
 *
 * @author Mateusz Kardaś
 */
class Decoders
{
    /**
     * Decode value from parsed lines
     *
     * @throws \InvalidArgumentException on decode error
     */
    public static function decodeValueFromLines(LineCursor $cursor, int $indent, bool $strict, string $defaultDelimiter): mixed
    {
        $first = $cursor->peek();
        if ($first === null) {
            throw new \InvalidArgumentException('No content to decode');
        }
        
        // Check for root array (with or without hyphen)
        [$headerInfo, $inlineValues] = Parser::parseArrayHeaderLine($first->content, $defaultDelimiter);
        if ($headerInfo !== null && $headerInfo->key === '') {
            // This is a root-level array (no key)
            $cursor->advance();
            return self::decodeArrayFromHeader($headerInfo, $inlineValues, $cursor, 0, $indent, $strict);
        }
        
        // Check for single primitive value
        if ($cursor->length() === 1 && !self::isKeyValueLine($first)) {
            return LiteralUtils::parsePrimitive(trim($first->content));
        }
        
        // Default to object
        return self::decodeObject($cursor, 0, $indent, $strict, $defaultDelimiter);
    }
    
    /**
     * Check if line is a key-value line
     */
    private static function isKeyValueLine($line): bool
    {
        $content = $line->content;
        
        // Look for unquoted colon or quoted key followed by colon
        if (str_starts_with($content, '"')) {
            $closingQuoteIndex = \Toon\Shared\StringUtils::findClosingQuote($content, 0);
            if ($closingQuoteIndex === -1) {
                return false;
            }
            return strpos($content, ':', $closingQuoteIndex + 1) !== false;
        }
        
        return strpos($content, ':') !== false;
    }
    
    /**
     * Decode an object
     */
    public static function decodeObject(LineCursor $cursor, int $baseDepth, int $indent, bool $strict, string $defaultDelimiter): array
    {
        $obj = [];
        $computedDepth = null;
        
        while (!$cursor->atEnd()) {
            $line = $cursor->peek();
            if ($line === null || $line->depth < $baseDepth) {
                break;
            }
            
            if ($computedDepth === null && $line->depth >= $baseDepth) {
                $computedDepth = $line->depth;
            }
            
            if ($line->depth === $computedDepth) {
                [$key, $value] = self::decodeKeyValuePair($line, $cursor, $computedDepth, $indent, $strict, $defaultDelimiter);
                $obj[$key] = $value;
            } else {
                break;
            }
        }
        
        return $obj;
    }
    
    /**
     * Decode a key-value pair
     *
     * @return array{0: string, 1: mixed}
     */
    private static function decodeKeyValuePair($line, LineCursor $cursor, int $baseDepth, int $indent, bool $strict, string $defaultDelimiter): array
    {
        $cursor->advance();
        return self::decodeKeyValue($line->content, $cursor, $baseDepth, $indent, $strict, $defaultDelimiter);
    }
    
    /**
     * Decode key and value from content
     *
     * @return array{0: string, 1: mixed}
     */
    private static function decodeKeyValue(string $content, LineCursor $cursor, int $baseDepth, int $indent, bool $strict, string $defaultDelimiter): array
    {
        // Check for array header first
        [$arrayHeader, $inlineValues] = Parser::parseArrayHeaderLine($content, $defaultDelimiter);
        if ($arrayHeader !== null && $arrayHeader->key !== '') {
            $value = self::decodeArrayFromHeader($arrayHeader, $inlineValues, $cursor, $baseDepth, $indent, $strict);
            return [$arrayHeader->key, $value];
        }
        
        // Regular key-value pair
        [$key, $end] = Parser::parseKeyToken($content);
        
        $rest = trim(substr($content, $end));
        
        // No value after colon - expect nested object or empty
        if ($rest === '') {
            $nextLine = $cursor->peek();
            if ($nextLine !== null && $nextLine->depth > $baseDepth) {
                $nested = self::decodeObject($cursor, $baseDepth + 1, $indent, $strict, $defaultDelimiter);
                return [$key, $nested];
            }
            // Empty object
            return [$key, []];
        }
        
        // Inline primitive value
        $value = LiteralUtils::parsePrimitive($rest);
        return [$key, $value];
    }
    
    /**
     * Decode array from its header
     */
    private static function decodeArrayFromHeader(ArrayHeaderInfo $header, string $inlineValues, LineCursor $cursor, int $baseDepth, int $indent, bool $strict): array
    {
        // Inline primitive array
        if ($inlineValues !== '') {
            return self::decodeInlinePrimitiveArray($header, $inlineValues, $strict);
        }
        
        // Tabular array
        if ($header->fields !== null && !empty($header->fields)) {
            return self::decodeTabularArray($header, $cursor, $baseDepth, $indent, $strict);
        }
        
        // List array
        return self::decodeListArray($header, $cursor, $baseDepth, $indent, $strict, $header->delimiter);
    }
    
    /**
     * Decode inline primitive array
     */
    private static function decodeInlinePrimitiveArray(ArrayHeaderInfo $header, string $inlineValues, bool $strict): array
    {
        if (trim($inlineValues) === '') {
            ValidationUtils::assertExpectedCount(0, $header->length, 'inline array items', $strict);
            return [];
        }
        
        $tokens = Parser::parseDelimitedValues($inlineValues, $header->delimiter);
        $values = [];
        
        foreach ($tokens as $token) {
            $trimmed = trim($token);
            if ($trimmed !== '') {
                $values[] = LiteralUtils::parsePrimitive($trimmed);
            }
        }
        
        ValidationUtils::assertExpectedCount(count($values), $header->length, 'inline array items', $strict);
        
        return $values;
    }
    
    /**
     * Decode tabular array
     */
    private static function decodeTabularArray(ArrayHeaderInfo $header, LineCursor $cursor, int $baseDepth, int $indent, bool $strict): array
    {
        $result = [];
        $rowCount = 0;
        
        while (!$cursor->atEnd()) {
            $line = $cursor->peek();
            if ($line === null || $line->depth <= $baseDepth) {
                break;
            }
            
            if ($line->depth === $baseDepth + 1) {
                $cursor->advance();
                
                $tokens = Parser::parseDelimitedValues($line->content, $header->delimiter);
                $row = [];
                
                foreach ($header->fields as $i => $fieldName) {
                    $value = null;
                    if ($i < count($tokens)) {
                        $trimmed = trim($tokens[$i]);
                        $value = LiteralUtils::parsePrimitive($trimmed);
                    }
                    $row[$fieldName] = $value;
                }
                
                $result[] = $row;
                $rowCount++;
            } else {
                break;
            }
        }
        
        ValidationUtils::assertExpectedCount($rowCount, $header->length, 'tabular array rows', $strict);
        
        return $result;
    }
    
    /**
     * Decode list array
     */
    private static function decodeListArray(ArrayHeaderInfo $header, LineCursor $cursor, int $baseDepth, int $indent, bool $strict, string $delimiter): array
    {
        $result = [];
        $itemCount = 0;
        
        while (!$cursor->atEnd()) {
            $line = $cursor->peek();
            if ($line === null || $line->depth <= $baseDepth) {
                break;
            }
            
            if ($line->depth === $baseDepth + 1) {
                $item = self::decodeListItem($line, $cursor, $baseDepth + 1, $indent, $strict, $delimiter);
                $result[] = $item;
                $itemCount++;
            } else {
                break;
            }
        }
        
        ValidationUtils::assertExpectedCount($itemCount, $header->length, 'list array items', $strict);
        
        return $result;
    }
    
    /**
     * Decode a list item
     */
    private static function decodeListItem($line, LineCursor $cursor, int $baseDepth, int $indent, bool $strict, string $delimiter): mixed
    {
        $cursor->advance();
        $content = $line->content;
        
        // Check for list marker (hyphen)
        if (str_starts_with($content, '- ')) {
            $content = substr($content, 2);
        }
        
        // Check for array header
        [$headerInfo, $inlineValues] = Parser::parseArrayHeaderLine($content, $delimiter);
        if ($headerInfo !== null && $headerInfo->key === '') {
            return self::decodeArrayFromHeader($headerInfo, $inlineValues, $cursor, $baseDepth, $indent, $strict);
        }
        
        // Check for key-value (object as list item)
        if (strpos($content, ':') !== false) {
            [$arrayHeader, $inlineValues] = Parser::parseArrayHeaderLine($content, $delimiter);
            if ($arrayHeader !== null && $arrayHeader->key !== '') {
                $obj = [];
                $obj[$arrayHeader->key] = self::decodeArrayFromHeader($arrayHeader, $inlineValues, $cursor, $baseDepth, $indent, $strict);
                
                // Check for additional properties
                while (!$cursor->atEnd()) {
                    $nextLine = $cursor->peek();
                    if ($nextLine === null || $nextLine->depth <= $baseDepth) {
                        break;
                    }
                    
                    if ($nextLine->depth === $baseDepth + 1) {
                        [$key, $value] = self::decodeKeyValue($nextLine->content, $cursor, $baseDepth + 1, $indent, $strict, $delimiter);
                        $obj[$key] = $value;
                        $cursor->advance();
                    } else {
                        break;
                    }
                }
                
                return $obj;
            }
            
            // Regular key-value pair
            try {
                [$key, $end] = Parser::parseKeyToken($content);
                $rest = trim(substr($content, $end));
                
                $obj = [];
                if ($rest === '') {
                    // Nested object
                    $nextLine = $cursor->peek();
                    if ($nextLine !== null && $nextLine->depth > $baseDepth) {
                        $obj[$key] = self::decodeObject($cursor, $baseDepth + 1, $indent, $strict, $delimiter);
                    } else {
                        $obj[$key] = [];
                    }
                } else {
                    $obj[$key] = LiteralUtils::parsePrimitive($rest);
                }
                
                // Check for additional properties
                while (!$cursor->atEnd()) {
                    $nextLine = $cursor->peek();
                    if ($nextLine === null || $nextLine->depth <= $baseDepth) {
                        break;
                    }
                    
                    if ($nextLine->depth === $baseDepth + 1) {
                        [$k, $v] = self::decodeKeyValue($nextLine->content, $cursor, $baseDepth + 1, $indent, $strict, $delimiter);
                        $obj[$k] = $v;
                        $cursor->advance();
                    } else {
                        break;
                    }
                }
                
                return $obj;
            } catch (\Exception $e) {
                // Fall through to primitive
            }
        }
        
        // Primitive value
        return LiteralUtils::parsePrimitive($content);
    }
}
