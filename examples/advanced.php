<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Toon\Toon;
use Toon\EncodeOptions;
use Toon\Constants;

echo "=== Advanced TOON Examples ===\n\n";

// Example 1: Large Dataset (GitHub-like repositories)
echo "Example 1: Large Dataset\n";
echo "------------------------\n";

$repos = [
    'repositories' => [
        [
            'name' => 'toon-php',
            'stars' => 125,
            'forks' => 23,
            'language' => 'PHP',
            'open_issues' => 5,
        ],
        [
            'name' => 'awesome-lib',
            'stars' => 1543,
            'forks' => 234,
            'language' => 'JavaScript',
            'open_issues' => 12,
        ],
        [
            'name' => 'data-processor',
            'stars' => 89,
            'forks' => 15,
            'language' => 'Python',
            'open_issues' => 3,
        ],
    ],
];

$encoded = Toon::encode($repos);
echo $encoded . "\n\n";

// Example 2: E-commerce Order
echo "Example 2: E-commerce Order\n";
echo "---------------------------\n";

$order = [
    'order_id' => 'ORD-2025-001234',
    'customer' => [
        'id' => 'CUST-5678',
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ],
    'items' => [
        [
            'sku' => 'PROD-001',
            'name' => 'Wireless Mouse',
            'quantity' => 2,
            'price' => 29.99,
        ],
        [
            'sku' => 'PROD-042',
            'name' => 'USB-C Cable',
            'quantity' => 3,
            'price' => 12.99,
        ],
        [
            'sku' => 'PROD-117',
            'name' => 'Laptop Stand',
            'quantity' => 1,
            'price' => 45.50,
        ],
    ],
    'shipping' => [
        'method' => 'express',
        'cost' => 15.00,
        'address' => [
            'street' => '123 Main St',
            'city' => 'San Francisco',
            'state' => 'CA',
            'zip' => '94102',
        ],
    ],
    'total' => 174.93,
    'status' => 'processing',
];

$encoded = Toon::encode($order);
echo $encoded . "\n\n";

$jsonSize = strlen(json_encode($order));
$toonSize = strlen($encoded);
echo "JSON size: {$jsonSize} bytes\n";
echo "TOON size: {$toonSize} bytes\n";
echo "Savings: " . round((1 - $toonSize / $jsonSize) * 100, 1) . "%\n\n";

// Example 3: Analytics Data
echo "Example 3: Analytics Data\n";
echo "-------------------------\n";

$analytics = [
    'website' => 'example.com',
    'period' => '2025-11',
    'metrics' => [
        'pageviews' => 125000,
        'unique_visitors' => 45000,
        'avg_session_duration' => 235,
        'bounce_rate' => 42.5,
    ],
    'top_pages' => [
        ['url' => '/home', 'views' => 25000, 'avg_time' => 120],
        ['url' => '/products', 'views' => 18500, 'avg_time' => 180],
        ['url' => '/blog', 'views' => 15000, 'avg_time' => 240],
        ['url' => '/about', 'views' => 8500, 'avg_time' => 90],
        ['url' => '/contact', 'views' => 5000, 'avg_time' => 60],
    ],
    'traffic_sources' => [
        ['source' => 'organic', 'percentage' => 45.2],
        ['source' => 'direct', 'percentage' => 28.5],
        ['source' => 'social', 'percentage' => 16.3],
        ['source' => 'referral', 'percentage' => 10.0],
    ],
];

$encoded = Toon::encode($analytics);
echo $encoded . "\n\n";

// Example 4: Different Delimiters
echo "Example 4: Different Delimiters\n";
echo "-------------------------------\n";

$data = [
    'data' => [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
    ],
];

echo "Comma delimiter (default):\n";
$encoded1 = Toon::encode($data);
echo $encoded1 . "\n\n";

echo "Pipe delimiter:\n";
$encoded2 = Toon::encode($data, new EncodeOptions(delimiter: Constants::DELIMITER_PIPE));
echo $encoded2 . "\n\n";

echo "Tab delimiter:\n";
$encoded3 = Toon::encode($data, new EncodeOptions(delimiter: Constants::DELIMITER_TAB));
echo $encoded3 . "\n\n";

// Example 5: Complex Nested Structure
echo "Example 5: Complex Nested Structure\n";
echo "-----------------------------------\n";

$config = [
    'app_name' => 'MyApp',
    'version' => '2.5.0',
    'database' => [
        'primary' => [
            'host' => 'db1.example.com',
            'port' => 5432,
            'name' => 'myapp_prod',
        ],
        'replica' => [
            'host' => 'db2.example.com',
            'port' => 5432,
            'name' => 'myapp_prod',
        ],
    ],
    'cache' => [
        'type' => 'redis',
        'servers' => [
            ['host' => 'cache1.example.com', 'port' => 6379],
            ['host' => 'cache2.example.com', 'port' => 6379],
        ],
    ],
    'features' => [
        'beta_features' => false,
        'analytics' => true,
        'notifications' => true,
    ],
];

$encoded = Toon::encode($config);
echo $encoded . "\n\n";

// Example 6: Special Characters and Escaping
echo "Example 6: Special Characters and Escaping\n";
echo "------------------------------------------\n";

$data = [
    'text' => "Line 1\nLine 2\tTabbed",
    'quoted' => 'She said "Hello"',
    'path' => 'C:\\Users\\Documents\\file.txt',
    'special' => 'Comma, colon: bracket[0]',
];

$encoded = Toon::encode($data);
echo $encoded . "\n\n";

$decoded = Toon::decode($encoded);
echo "Decoded correctly:\n";
print_r($decoded);
echo "\n";

// Example 7: Empty and Null Values
echo "Example 7: Empty and Null Values\n";
echo "--------------------------------\n";

$data = [
    'string' => '',
    'null_value' => null,
    'empty_array' => [],
    'empty_object' => [],
    'array_with_nulls' => [null, 'value', null],
];

$encoded = Toon::encode($data);
echo $encoded . "\n\n";

echo "All advanced examples completed!\n";
