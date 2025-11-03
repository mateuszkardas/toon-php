<?php

declare(strict_types=1);

namespace Toon\Shared;

/**
 * Utility functions for parsing TOON literals
 *
 * @author Mateusz Kardaś
 */
class LiteralUtils
{
    /**
     * Parse a primitive token (null, bool, number, or string)
     *
     * @return mixed The parsed value
     * @throws \InvalidArgumentException if token is invalid
     */
    public static function parsePrimitive(string $token): mixed
    {
        $trimmed = trim($token);
        
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Cannot parse empty token');
        }
        
        // Check for null
        if ($trimmed === 'null') {
            return null;
        }
        
        // Check for boolean
        if ($trimmed === 'true') {
            return true;
        }
        if ($trimmed === 'false') {
            return false;
        }
        
        // Check for quoted string
        if ($trimmed[0] === '"') {
            return self::parseQuotedString($trimmed);
        }
        
        // Check for number
        if (is_numeric($trimmed)) {
            return self::parseNumber($trimmed);
        }
        
        // Unquoted string
        return $trimmed;
    }
    
    /**
     * Parse a quoted string
     *
     * @throws \InvalidArgumentException if string is not properly quoted
     */
    public static function parseQuotedString(string $token): string
    {
        $len = strlen($token);
        if ($len < 2 || $token[0] !== '"' || $token[$len - 1] !== '"') {
            throw new \InvalidArgumentException('Invalid quoted string: missing quotes');
        }

        $content = substr($token, 1, -1);
        return StringUtils::unescape($content);
    }
    
    /**
     * Parse a number (int or float)
     */
    public static function parseNumber(string $token): int|float
    {
        // Check if it's a float
        if (strpos($token, '.') !== false || 
            stripos($token, 'e') !== false) {
            return (float) $token;
        }
        
        // Integer
        return (int) $token;
    }
    
    /**
     * Check if a value is a primitive type
     */
    public static function isPrimitive(mixed $value): bool
    {
        return is_null($value) || 
               is_bool($value) || 
               is_int($value) || 
               is_float($value) || 
               is_string($value);
    }
    
    /**
     * Check if a value is an object (associative array)
     */
    public static function isObject(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        // Empty array -> treat as array (not object) for encoding semantics
        if (empty($value)) {
            return false;
        }
        // Associative arrays (non-sequential numeric keys) are objects
        return array_keys($value) !== range(0, count($value) - 1);
    }
    
    /**
     * Check if a value is an array (indexed array)
     */
    public static function isArray(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        // Empty array -> still an array
        if (empty($value)) {
            return true;
        }
        // Check if array has numeric sequential keys
        return array_keys($value) === range(0, count($value) - 1);
    }
}
