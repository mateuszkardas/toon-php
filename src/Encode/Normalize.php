<?php

declare(strict_types=1);

namespace Toon\Encode;

/**
 * Normalize PHP values to JSON-compatible format
 *
 * @author Mateusz Kardaś
 */
class Normalize
{
    /**
     * Normalize a value to JSON-compatible format
     */
    public static function normalize(mixed $value): mixed
    {
        if (is_null($value) || is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            return $value;
        }
        
        if (is_array($value)) {
            return self::normalizeArray($value);
        }
        
        if (is_object($value)) {
            return self::normalizeObject($value);
        }
        
        throw new \InvalidArgumentException('Unsupported value type: ' . gettype($value));
    }
    
    /**
     * Normalize an array
     */
    private static function normalizeArray(array $value): array
    {
        $result = [];
        
        foreach ($value as $key => $item) {
            $result[$key] = self::normalize($item);
        }
        
        return $result;
    }
    
    /**
     * Normalize an object
     */
    private static function normalizeObject(object $value): array
    {
        // Handle stdClass
        if ($value instanceof \stdClass) {
            $array = (array) $value;
            return self::normalizeArray($array);
        }
        
        // Handle JsonSerializable
        if ($value instanceof \JsonSerializable) {
            return self::normalize($value->jsonSerialize());
        }
        
        // Handle DateTime
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }
        
        // Convert object to array using get_object_vars
        $array = get_object_vars($value);
        return self::normalizeArray($array);
    }
}
