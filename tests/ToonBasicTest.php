<?php

declare(strict_types=1);

namespace Toon\Tests;

use PHPUnit\Framework\TestCase;
use Toon\Toon;
use Toon\EncodeOptions;
use Toon\DecodeOptions;
use Toon\Constants;

/**
 * Basic tests for TOON encoding and decoding
 */
class ToonBasicTest extends TestCase
{
    public function testSimpleObjectEncoding(): void
    {
        $data = ['name' => 'Alice', 'age' => 30];
        $encoded = Toon::encode($data);
        
        $this->assertStringContainsString('name: Alice', $encoded);
        $this->assertStringContainsString('age: 30', $encoded);
    }
    
    public function testSimpleObjectDecoding(): void
    {
        $toon = "name: Alice\nage: 30";
        $decoded = Toon::decode($toon);
        
        $this->assertEquals('Alice', $decoded['name']);
        $this->assertEquals(30, $decoded['age']);
    }
    
    public function testRoundTrip(): void
    {
        $original = [
            'name' => 'Bob',
            'age' => 25,
            'active' => true,
        ];
        
        $encoded = Toon::encode($original);
        $decoded = Toon::decode($encoded);
        
        $this->assertEquals($original, $decoded);
    }
    
    public function testPrimitiveArray(): void
    {
        $data = ['numbers' => [1, 2, 3, 4, 5]];
        $encoded = Toon::encode($data);
        
        $this->assertStringContainsString('numbers[5]:', $encoded);
        $this->assertStringContainsString('1,2,3,4,5', $encoded);
    }
    
    public function testTabularArray(): void
    {
        $data = [
            'users' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
            ],
        ];
        
        $encoded = Toon::encode($data);
        
        $this->assertStringContainsString('users[2]{id,name}:', $encoded);
        $this->assertStringContainsString('1,Alice', $encoded);
        $this->assertStringContainsString('2,Bob', $encoded);
    }
    
    public function testNestedObject(): void
    {
        $data = [
            'user' => [
                'name' => 'Alice',
                'address' => [
                    'city' => 'SF',
                ],
            ],
        ];
        
        $encoded = Toon::encode($data);
        $decoded = Toon::decode($encoded);
        
        $this->assertEquals('Alice', $decoded['user']['name']);
        $this->assertEquals('SF', $decoded['user']['address']['city']);
    }
    
    public function testNullValue(): void
    {
        $data = ['value' => null];
        $encoded = Toon::encode($data);
        
        $this->assertStringContainsString('value: null', $encoded);
        
        $decoded = Toon::decode($encoded);
        $this->assertNull($decoded['value']);
    }
    
    public function testBooleanValues(): void
    {
        $data = ['yes' => true, 'no' => false];
        $encoded = Toon::encode($data);
        $decoded = Toon::decode($encoded);
        
        $this->assertTrue($decoded['yes']);
        $this->assertFalse($decoded['no']);
    }
    
    public function testStringEscaping(): void
    {
        $data = ['text' => "Line 1\nLine 2"];
        $encoded = Toon::encode($data);
        
        $this->assertStringContainsString('\\n', $encoded);
        
        $decoded = Toon::decode($encoded);
        $this->assertEquals("Line 1\nLine 2", $decoded['text']);
    }
    
    public function testCustomIndent(): void
    {
        $data = ['nested' => ['value' => 42]];
        $options = new EncodeOptions(indent: 4);
        
        $encoded = Toon::encode($data, $options);
        
        // Should have 4 spaces for nested value
        $this->assertStringContainsString('    value: 42', $encoded);
    }
    
    public function testPipeDelimiter(): void
    {
        $data = ['items' => [
            ['a' => 1, 'b' => 2],
            ['a' => 3, 'b' => 4],
        ]];
        
        $options = new EncodeOptions(delimiter: Constants::DELIMITER_PIPE);
        $encoded = Toon::encode($data, $options);
        
        $this->assertStringContainsString('1|2', $encoded);
        $this->assertStringContainsString('3|4', $encoded);
    }
    
    public function testLengthMarker(): void
    {
        $data = ['items' => [1, 2, 3]];
        $options = new EncodeOptions(lengthMarker: '#');
        
        $encoded = Toon::encode($data, $options);
        
        $this->assertStringContainsString('[#3]', $encoded);
    }
    
    public function testEmptyArray(): void
    {
        $data = ['items' => []];
        $encoded = Toon::encode($data);
        
        $this->assertStringContainsString('items[0]:', $encoded);
    }
    
    public function testStringifyAndParse(): void
    {
        $data = ['test' => 'value'];
        
        $encoded = Toon::stringify($data);
        $decoded = Toon::parse($encoded);
        
        $this->assertEquals($data, $decoded);
    }
}
