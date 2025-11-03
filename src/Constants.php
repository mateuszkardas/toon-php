<?php

declare(strict_types=1);

namespace Toon;

/**
 * TOON format constants
 *
 * @author Mateusz Kardaś
 */
class Constants
{
    // List markers
    public const LIST_ITEM_MARKER = '-';
    public const LIST_ITEM_PREFIX = '- ';
    
    // Structural characters
    public const COMMA = ',';
    public const COLON = ':';
    public const SPACE = ' ';
    public const PIPE = '|';
    public const HASH = '#';
    public const TAB = "\t";
    
    // Brackets and braces
    public const OPEN_BRACKET = '[';
    public const CLOSE_BRACKET = ']';
    public const OPEN_BRACE = '{';
    public const CLOSE_BRACE = '}';
    
    // Literals
    public const NULL_LITERAL = 'null';
    public const TRUE_LITERAL = 'true';
    public const FALSE_LITERAL = 'false';
    
    // Escape characters
    public const BACKSLASH = '\\';
    public const DOUBLE_QUOTE = '"';
    public const NEWLINE = "\n";
    public const CARRIAGE_RETURN = "\r";
    
    // Delimiters
    public const DELIMITER_COMMA = ',';
    public const DELIMITER_TAB = "\t";
    public const DELIMITER_PIPE = '|';
    
    // Default values
    public const DEFAULT_INDENT = 2;
    public const DEFAULT_DELIMITER = self::DELIMITER_COMMA;
    public const DEFAULT_LENGTH_MARKER = '';
}
