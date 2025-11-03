<?php

declare(strict_types=1);

namespace Toon;

class EncodeOptions
{
    public function __construct(
        public int $indent = Constants::DEFAULT_INDENT,
        public string $delimiter = Constants::DEFAULT_DELIMITER,
        public string $lengthMarker = Constants::DEFAULT_LENGTH_MARKER
    ) {
        if ($indent < 1) {
            throw new \InvalidArgumentException('Indent must be at least 1');
        }

        if (!in_array($delimiter, [Constants::DELIMITER_COMMA, Constants::DELIMITER_TAB, Constants::DELIMITER_PIPE], true)) {
            throw new \InvalidArgumentException('Invalid delimiter. Must be comma, tab, or pipe');
        }

        if ($lengthMarker !== '' && $lengthMarker !== '#') {
            throw new \InvalidArgumentException('Length marker must be empty or "#"');
        }
    }
}
