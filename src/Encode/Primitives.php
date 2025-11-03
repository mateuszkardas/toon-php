<?php

declare(strict_types=1);

namespace Toon\Encode;

use Toon\Constants;
use Toon\Shared\StringUtils;

/**
 * Primitive value encoding for TOON format
 *
 * @author Mateusz Kardaś
 */
class Primitives
{
    /**
     * Encode a primitive value
     */
    public static function encode(mixed $value, string $delimiter): string
    {
        if (is_null($value)) {
            return Constants::NULL_LITERAL;
        }
        
        if (is_bool($value)) {
            return $value ? Constants::TRUE_LITERAL : Constants::FALSE_LITERAL;
        }
        
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        
        if (is_string($value)) {
            return self::encodeString($value, $delimiter);
        }
        
        throw new \InvalidArgumentException('Value is not a primitive type');
    }
    
    /**
     * Encode a string value
     */
    public static function encodeString(string $value, string $delimiter): string
    {
        // Check if quoting is needed
        $needsQuotes = StringUtils::needsQuotes($value) || 
                      strpos($value, $delimiter) !== false;
        
        if ($needsQuotes) {
            $escaped = StringUtils::escape($value);
            return '"' . $escaped . '"';
        }
        
        return $value;
    }
    
    /**
     * Encode and join multiple primitives
     */
    public static function encodeAndJoin(array $values, string $delimiter): string
    {
        $encoded = array_map(
            fn($val) => self::encode($val, $delimiter),
            $values
        );
        
        return implode($delimiter, $encoded);
    }
    
    /**
     * Encode a key (always needs proper escaping)
     */
    public static function encodeKey(string $key): string
    {
        if (StringUtils::needsQuotes($key)) {
            $escaped = StringUtils::escape($key);
            return '"' . $escaped . '"';
        }
        
        return $key;
    }
}
