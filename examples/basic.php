<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Toon\Toon;
use Toon\EncodeOptions;
use Toon\DecodeOptions;
use Toon\Constants;

echo "=== TOON Format Examples ===\n\n";

// Example 1: Simple Object
echo "Example 1: Simple Object\n";
echo "------------------------\n";

$data = [
    'name' => 'Alice',
    'age' => 30,
    'active' => true,
];

$encoded = Toon::encode($data);
echo "Encoded:\n";
echo $encoded . "\n\n";

// Example 2: Tabular Array (Most Efficient Format)
echo "Example 2: Tabular Array (Most Efficient Format)\n";
echo "-----------------------------------------------\n";

$data = [
    'users' => [
        ['id' => 1, 'name' => 'Alice', 'role' => 'admin'],
        ['id' => 2, 'name' => 'Bob', 'role' => 'user'],
        ['id' => 3, 'name' => 'Charlie', 'role' => 'user'],
    ],
];

$encoded = Toon::encode($data);
echo "Encoded:\n";
echo $encoded . "\n\n";

// Decode it back
$decoded = Toon::decode($encoded);
echo "Decoded:\n";
print_r($decoded);
echo "\n";

// Example 3: Nested Structure
echo "Example 3: Nested Structure\n";
echo "---------------------------\n";

$data = [
    'company' => [
        'name' => 'TechCorp',
        'location' => [
            'city' => 'San Francisco',
            'country' => 'USA',
        ],
        'employees' => [
            ['name' => 'Alice', 'department' => 'Engineering'],
            ['name' => 'Bob', 'department' => 'Sales'],
        ],
    ],
];

$encoded = Toon::encode($data);
echo "Encoded:\n";
echo $encoded . "\n\n";

// Example 4: Custom Options
echo "Example 4: Custom Options\n";
echo "-------------------------\n";

$data = [
    'items' => [
        ['id' => 1, 'name' => 'Item A'],
        ['id' => 2, 'name' => 'Item B'],
    ],
];

$options = new EncodeOptions(
    indent: 4,
    delimiter: Constants::DELIMITER_PIPE,
    lengthMarker: '#'
);

$encoded = Toon::encode($data, $options);
echo "Encoded with custom options (4 spaces, pipe delimiter, # marker):\n";
echo $encoded . "\n\n";

// Example 5: Round-trip
echo "Example 5: Round-trip\n";
echo "--------------------\n";

$original = [
    'message' => 'Hello, TOON!',
    'numbers' => [1, 2, 3, 4, 5],
    'nested' => [
        'a' => 'value A',
        'b' => 'value B',
    ],
];

$encoded = Toon::encode($original);
$decoded = Toon::decode($encoded);

echo "Original:\n";
print_r($original);
echo "\nEncoded:\n";
echo $encoded . "\n";
echo "\nDecoded:\n";
print_r($decoded);
echo "\nMatch: " . (json_encode($original) === json_encode($decoded) ? 'YES' : 'NO') . "\n\n";

// Example 6: Comparison with JSON
echo "Example 6: Token Efficiency Comparison\n";
echo "--------------------------------------\n";

$data = [
    'users' => [
        ['id' => 1, 'name' => 'Alice', 'role' => 'admin', 'active' => true],
        ['id' => 2, 'name' => 'Bob', 'role' => 'user', 'active' => true],
        ['id' => 3, 'name' => 'Charlie', 'role' => 'user', 'active' => false],
        ['id' => 4, 'name' => 'David', 'role' => 'user', 'active' => true],
        ['id' => 5, 'name' => 'Eve', 'role' => 'admin', 'active' => true],
    ],
];

$jsonEncoded = json_encode($data, JSON_PRETTY_PRINT);
$toonEncoded = Toon::encode($data);

echo "JSON format:\n";
echo $jsonEncoded . "\n\n";
echo "TOON format:\n";
echo $toonEncoded . "\n\n";

echo "JSON length: " . strlen($jsonEncoded) . " characters\n";
echo "TOON length: " . strlen($toonEncoded) . " characters\n";
echo "Savings: " . round((1 - strlen($toonEncoded) / strlen($jsonEncoded)) * 100, 1) . "%\n\n";

// Example 7: Primitive Arrays
echo "Example 7: Primitive Arrays\n";
echo "---------------------------\n";

$data = [
    'tags' => ['php', 'toon', 'serialization', 'llm'],
    'scores' => [95, 87, 92, 88, 91],
];

$encoded = Toon::encode($data);
echo "Encoded:\n";
echo $encoded . "\n\n";

// Example 8: Mixed Content
echo "Example 8: Mixed Content\n";
echo "------------------------\n";

$data = [
    'title' => 'Project Report',
    'date' => '2025-11-03',
    'sections' => [
        [
            'name' => 'Introduction',
            'content' => 'This is the introduction section.',
        ],
        [
            'name' => 'Results',
            'data' => [42, 56, 78],
        ],
    ],
];

$encoded = Toon::encode($data);
echo "Encoded:\n";
echo $encoded . "\n\n";

echo "All examples completed!\n";
