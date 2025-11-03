<?php

declare(strict_types=1);

namespace Toon\Shared;

/**
 * Validation utility functions for TOON format
 *
 * @author Mateusz Kardaś
 */
class ValidationUtils
{
    /**
     * Validate that a key is a valid TOON identifier
     *
     * @throws \InvalidArgumentException if key is invalid
     */
    public static function validateKey(string $key): void
    {
        if ($key === '') {
            throw new \InvalidArgumentException('Key cannot be empty');
        }
        
        // Keys should not contain control characters
        if (preg_match('/[\x00-\x1F\x7F]/', $key)) {
            throw new \InvalidArgumentException("Key contains invalid control characters: {$key}");
        }
    }
    
    /**
     * Validate array length matches expected count
     *
     * @throws \InvalidArgumentException if strict mode and counts don't match
     */
    public static function assertExpectedCount(int $actual, int $expected, string $context, bool $strict): void
    {
        if ($strict && $actual !== $expected) {
            throw new \InvalidArgumentException(
                "Expected {$expected} {$context}, but got {$actual}"
            );
        }
    }
    
    /**
     * Validate that a string contains only valid characters for a key
     */
    public static function isValidKeyString(string $key): bool
    {
        if ($key === '') {
            return false;
        }
        
        // Check for control characters
        if (preg_match('/[\x00-\x1F\x7F]/', $key)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if a line is blank (empty or only whitespace)
     */
    public static function isBlankLine(string $line): bool
    {
        return trim($line) === '';
    }
    
    /**
     * Calculate indentation level
     */
    public static function getIndentLevel(string $line, int $indent): int
    {
        $spaces = 0;
        $length = strlen($line);
        
        for ($i = 0; $i < $length; $i++) {
            if ($line[$i] === ' ') {
                $spaces++;
            } else {
                break;
            }
        }
        
        return (int) floor($spaces / $indent);
    }
}
