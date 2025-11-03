<?php

declare(strict_types=1);

namespace Toon\Encode;

use Toon\Constants;
use Toon\Shared\LiteralUtils;

/**
 * Main encoder for TOON format
 *
 * @author Mateusz Kardaś
 */
class Encoders
{
    /**
     * Encode a value to TOON format
     */
    public static function encodeValue(mixed $value, int $indent, string $delimiter, string $lengthMarker): string
    {
        if (LiteralUtils::isPrimitive($value)) {
            return Primitives::encode($value, $delimiter);
        }
        
        $writer = new Writer($indent);
        
        if (LiteralUtils::isArray($value)) {
            self::encodeArray('', $value, $writer, 0, $delimiter, $lengthMarker);
        } elseif (LiteralUtils::isObject($value)) {
            self::encodeObject($value, $writer, 0, $delimiter, $lengthMarker);
        }
        
        return $writer->toString();
    }
    
    /**
     * Encode an object
     */
    public static function encodeObject(array $obj, Writer $writer, int $depth, string $delimiter, string $lengthMarker): void
    {
        foreach ($obj as $key => $value) {
            self::encodeKeyValuePair($key, $value, $writer, $depth, $delimiter, $lengthMarker);
        }
    }
    
    /**
     * Encode a key-value pair
     */
    public static function encodeKeyValuePair(string $key, mixed $value, Writer $writer, int $depth, string $delimiter, string $lengthMarker): void
    {
        $encodedKey = Primitives::encodeKey($key);
        
        if (LiteralUtils::isPrimitive($value)) {
            $writer->push($depth, $encodedKey . ': ' . Primitives::encode($value, $delimiter));
        } elseif (LiteralUtils::isArray($value)) {
            self::encodeArray($key, $value, $writer, $depth, $delimiter, $lengthMarker);
        } elseif (LiteralUtils::isObject($value)) {
            if (empty($value)) {
                $writer->push($depth, $encodedKey . ':');
            } else {
                $writer->push($depth, $encodedKey . ':');
                self::encodeObject($value, $writer, $depth + 1, $delimiter, $lengthMarker);
            }
        }
    }
    
    /**
     * Encode an array
     */
    public static function encodeArray(string $key, array $arr, Writer $writer, int $depth, string $delimiter, string $lengthMarker): void
    {
        if (empty($arr)) {
            $header = self::formatHeader(0, $key, null, $delimiter, $lengthMarker);
            $writer->push($depth, $header);
            return;
        }
        
        // Primitive array
        if (self::isArrayOfPrimitives($arr)) {
            $formatted = self::encodeInlineArrayLine($arr, $delimiter, $key, $lengthMarker);
            $writer->push($depth, $formatted);
            return;
        }
        
        // Array of objects (tabular or list) - check BEFORE generic array-of-arrays
        if (self::isArrayOfObjects($arr)) {
            $header = self::extractTabularHeader($arr);
            if ($header !== null) {
                self::encodeArrayOfObjectsAsTabular($key, $arr, $header, $writer, $depth, $delimiter, $lengthMarker);
            } else {
                self::encodeMixedArrayAsListItems($key, $arr, $writer, $depth, $delimiter, $lengthMarker);
            }
            return;
        }

        // Array of arrays (all primitives) (only pure indexed primitive subarrays)
        if (self::isArrayOfArrays($arr)) {
            $allPrimitiveArrays = true;
            foreach ($arr as $item) {
                if (!self::isArrayOfPrimitives($item)) {
                    $allPrimitiveArrays = false;
                    break;
                }
            }
            if ($allPrimitiveArrays) {
                self::encodeArrayOfArraysAsListItems($key, $arr, $writer, $depth, $delimiter, $lengthMarker);
                return;
            }
        }
        
        // Mixed array: fallback to expanded format
        self::encodeMixedArrayAsListItems($key, $arr, $writer, $depth, $delimiter, $lengthMarker);
    }
    
    /**
     * Check if array contains only primitives
     */
    private static function isArrayOfPrimitives(array $arr): bool
    {
        foreach ($arr as $item) {
            if (!LiteralUtils::isPrimitive($item)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Check if array contains only arrays
     */
    private static function isArrayOfArrays(array $arr): bool
    {
        foreach ($arr as $item) {
            if (!is_array($item)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Check if array contains only objects
     */
    private static function isArrayOfObjects(array $arr): bool
    {
        foreach ($arr as $item) {
            if (!LiteralUtils::isObject($item)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Encode inline array line
     */
    private static function encodeInlineArrayLine(array $arr, string $delimiter, string $key, string $lengthMarker): string
    {
        $header = self::formatHeader(count($arr), $key, null, $delimiter, $lengthMarker);
        if (empty($arr)) {
            return $header;
        }
        $joinedValue = Primitives::encodeAndJoin($arr, $delimiter);
        return $header . ' ' . $joinedValue;
    }
    
    /**
     * Format array header
     */
    private static function formatHeader(int $length, string $key, ?array $fields, string $delimiter, string $lengthMarker): string
    {
        $result = '';
        
        if ($key !== '') {
            $result .= Primitives::encodeKey($key);
        }
        
        $result .= Constants::OPEN_BRACKET;
        
        if ($lengthMarker !== '') {
            $result .= $lengthMarker;
        }
        
        $result .= (string) $length;
        $result .= Constants::CLOSE_BRACKET;
        
        if ($fields !== null && !empty($fields)) {
            $result .= Constants::OPEN_BRACE;
            $encodedFields = array_map(fn($f) => Primitives::encodeKey($f), $fields);
            $result .= implode(Constants::COMMA, $encodedFields);
            $result .= Constants::CLOSE_BRACE;
        }
        
        $result .= Constants::COLON;
        
        return $result;
    }
    
    /**
     * Extract tabular header from array of objects
     */
    private static function extractTabularHeader(array $objects): ?array
    {
        if (empty($objects)) {
            return null;
        }
        
        $firstObj = $objects[0];
        if (empty($firstObj)) {
            return null;
        }
        
        $firstKeys = array_keys($firstObj);
        
        if (self::isTabularArray($objects, $firstKeys)) {
            return $firstKeys;
        }
        
        return null;
    }
    
    /**
     * Check if array can be represented as tabular
     */
    private static function isTabularArray(array $objects, array $header): bool
    {
        foreach ($objects as $obj) {
            if (count($obj) !== count($header)) {
                return false;
            }
            
            foreach ($header as $key) {
                if (!array_key_exists($key, $obj)) {
                    return false;
                }
                if (!LiteralUtils::isPrimitive($obj[$key])) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Encode array of objects as tabular
     */
    private static function encodeArrayOfObjectsAsTabular(string $key, array $objects, array $header, Writer $writer, int $depth, string $delimiter, string $lengthMarker): void
    {
        $formattedHeader = self::formatHeader(count($objects), $key, $header, $delimiter, $lengthMarker);
        $writer->push($depth, $formattedHeader);
        
        foreach ($objects as $obj) {
            $values = [];
            foreach ($header as $k) {
                $values[] = $obj[$k];
            }
            $joinedValue = Primitives::encodeAndJoin($values, $delimiter);
            $writer->push($depth + 1, $joinedValue);
        }
    }
    
    /**
     * Encode array of arrays as list items
     */
    private static function encodeArrayOfArraysAsListItems(string $key, array $arr, Writer $writer, int $depth, string $delimiter, string $lengthMarker): void
    {
        $header = self::formatHeader(count($arr), $key, null, $delimiter, $lengthMarker);
        $writer->push($depth, $header);
        
        foreach ($arr as $item) {
            if (self::isArrayOfPrimitives($item)) {
                $inline = self::encodeInlineArrayLine($item, $delimiter, '', $lengthMarker);
                $writer->pushListItem($depth + 1, $inline);
            }
        }
    }
    
    /**
     * Encode mixed array as list items
     */
    private static function encodeMixedArrayAsListItems(string $key, array $arr, Writer $writer, int $depth, string $delimiter, string $lengthMarker): void
    {
        $header = self::formatHeader(count($arr), $key, null, $delimiter, $lengthMarker);
        $writer->push($depth, $header);
        
        foreach ($arr as $item) {
            self::encodeListItemValue($item, $writer, $depth + 1, $delimiter, $lengthMarker);
        }
    }
    
    /**
     * Encode a single list item value
     */
    private static function encodeListItemValue(mixed $value, Writer $writer, int $depth, string $delimiter, string $lengthMarker): void
    {
        if (LiteralUtils::isPrimitive($value)) {
            $writer->pushListItem($depth, Primitives::encode($value, $delimiter));
        } elseif (LiteralUtils::isArray($value)) {
            if (self::isArrayOfPrimitives($value)) {
                $inline = self::encodeInlineArrayLine($value, $delimiter, '', $lengthMarker);
                $writer->pushListItem($depth, $inline);
            } else {
                $header = self::formatHeader(count($value), '', null, $delimiter, $lengthMarker);
                $writer->pushListItem($depth, $header);
                foreach ($value as $item) {
                    self::encodeListItemValue($item, $writer, $depth + 1, $delimiter, $lengthMarker);
                }
            }
        } elseif (LiteralUtils::isObject($value)) {
            self::encodeObjectAsListItem($value, $writer, $depth, $delimiter, $lengthMarker);
        }
    }
    
    /**
     * Encode an object as a list item
     */
    private static function encodeObjectAsListItem(array $obj, Writer $writer, int $depth, string $delimiter, string $lengthMarker): void
    {
        if (empty($obj)) {
            $writer->push($depth, '-');
            return;
        }
        
        // Get first key-value pair
        $keys = array_keys($obj);
        $firstKey = $keys[0];
        $firstValue = $obj[$firstKey];
        
        $encodedKey = Primitives::encodeKey($firstKey);
        
        if (LiteralUtils::isPrimitive($firstValue)) {
            $writer->pushListItem($depth, $encodedKey . ': ' . Primitives::encode($firstValue, $delimiter));
        } elseif (LiteralUtils::isArray($firstValue)) {
            if (self::isArrayOfPrimitives($firstValue)) {
                $formatted = self::encodeInlineArrayLine($firstValue, $delimiter, $firstKey, $lengthMarker);
                $writer->pushListItem($depth, $formatted);
            } elseif (self::isArrayOfObjects($firstValue)) {
                $header = self::extractTabularHeader($firstValue);
                if ($header !== null) {
                    $formattedHeader = self::formatHeader(count($firstValue), $firstKey, $header, $delimiter, $lengthMarker);
                    $writer->pushListItem($depth, $formattedHeader);
                    foreach ($firstValue as $item) {
                        $values = [];
                        foreach ($header as $k) {
                            $values[] = $item[$k];
                        }
                        $joinedValue = Primitives::encodeAndJoin($values, $delimiter);
                        $writer->push($depth + 1, $joinedValue);
                    }
                } else {
                    $writer->pushListItem($depth, $encodedKey . '[' . count($firstValue) . ']:');
                    foreach ($firstValue as $item) {
                        if (LiteralUtils::isObject($item)) {
                            self::encodeObjectAsListItem($item, $writer, $depth + 1, $delimiter, $lengthMarker);
                        }
                    }
                }
            } else {
                $writer->pushListItem($depth, $encodedKey . '[' . count($firstValue) . ']:');
                foreach ($firstValue as $item) {
                    self::encodeListItemValue($item, $writer, $depth + 1, $delimiter, $lengthMarker);
                }
            }
        } elseif (LiteralUtils::isObject($firstValue)) {
            if (empty($firstValue)) {
                $writer->pushListItem($depth, $encodedKey . ':');
            } else {
                $writer->pushListItem($depth, $encodedKey . ':');
                self::encodeObject($firstValue, $writer, $depth + 2, $delimiter, $lengthMarker);
            }
        }
        
        // Encode remaining keys
        foreach ($keys as $i => $k) {
            if ($i === 0) continue; // Skip first key (already encoded)
            self::encodeKeyValuePair($k, $obj[$k], $writer, $depth + 1, $delimiter, $lengthMarker);
        }
    }
}
