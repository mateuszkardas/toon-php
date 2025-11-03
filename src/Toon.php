<?php

declare(strict_types=1);

namespace Toon;

use Toon\Encode\Encoders;
use Toon\Encode\Normalize;
use Toon\Decode\Scanner;
use Toon\Decode\Decoders;
use Toon\Decode\LineCursor;

/**
 * TOON (Token-Oriented Object Notation) - Main API
 *
 * A compact, human-readable serialization format designed for passing
 * structured data to Large Language Models with significantly reduced token usage.
 *
 * @author Mateusz Kardaś
 * @link https://github.com/mateuszkardas/toon-php
 */
class Toon
{
    /**
     * Encode a PHP value to TOON format
     *
     * @param mixed $input The value to encode
     * @param EncodeOptions|null $options Encoding options
     * @return string The TOON-formatted string
     * @throws \InvalidArgumentException if value cannot be encoded
     */
    public static function encode(mixed $input, ?EncodeOptions $options = null): string
    {
        $normalizedValue = Normalize::normalize($input);
        $opts = $options ?? new EncodeOptions();
        
        return Encoders::encodeValue(
            $normalizedValue,
            $opts->indent,
            $opts->delimiter,
            $opts->lengthMarker
        );
    }
    
    /**
     * Decode a TOON-formatted string to PHP value
     *
     * @param string $input The TOON-formatted string
     * @param DecodeOptions|null $options Decoding options
     * @return mixed The decoded value
     * @throws \InvalidArgumentException if input cannot be decoded
     */
    public static function decode(string $input, ?DecodeOptions $options = null): mixed
    {
        $opts = $options ?? new DecodeOptions();
        
        $scanResult = Scanner::toParsedLines($input, $opts->indent, $opts->strict);
        
        if (empty($scanResult->lines)) {
            throw new \InvalidArgumentException('Cannot decode empty input: input must be a non-empty string');
        }
        
        $cursor = new LineCursor($scanResult->lines, $scanResult->blankLines);
        
        return Decoders::decodeValueFromLines(
            $cursor,
            $opts->indent,
            $opts->strict,
            Constants::DEFAULT_DELIMITER
        );
    }
    
    /**
     * Convenience method: Encode and return TOON string with default options
     *
     * @param mixed $input The value to encode
     * @return string The TOON-formatted string
     */
    public static function stringify(mixed $input): string
    {
        return self::encode($input);
    }
    
    /**
     * Convenience method: Decode TOON string with default options
     *
     * @param string $input The TOON-formatted string
     * @return mixed The decoded value
     */
    public static function parse(string $input): mixed
    {
        return self::decode($input);
    }
}
