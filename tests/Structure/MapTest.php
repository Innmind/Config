<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config\Structure;

use Innmind\Config\{
    Structure\Map,
    Structure,
    Structures,
    Properties,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\MapInterface;
use PHPUnit\Framework\TestCase;

class MapTest extends TestCase
{
    public function testInterface()
    {
        $map = Map::build(
            [],
            new Structures,
            new Properties
        );

        $this->assertInstanceOf(Map::class, $map);
        $this->assertInstanceOf(Structure::class, $map);
    }

    public function testProcess()
    {
        $map = Map::build(
            [
                'raw' => 'int',
                'nested' => [
                    'value' => 'string',
                ],
            ],
            new Structures,
            new Properties
        );

        $result = $map->process([
            'raw' => 42,
            'nested' => [
                'value' => 'watev',
            ],
        ]);

        $this->assertInstanceOf(MapInterface::class, $result);
        $this->assertSame('scalar', (string) $result->keyType());
        $this->assertSame('mixed', (string) $result->valueType());
        $this->assertCount(2, $result);
        $this->assertSame(42, $result->get('raw'));
        $nested = $result->get('nested');
        $this->assertInstanceOf(MapInterface::class, $nested);
        $this->assertSame('scalar', (string) $nested->keyType());
        $this->assertSame('mixed', (string) $nested->valueType());
        $this->assertCount(1, $nested);
        $this->assertSame('watev', $nested->get('value'));
    }

    public function testThrowWhenValueIsNeitherStringNorArray()
    {
        $this->expectException(SchemaNotParseable::class);
        $this->expectExceptionMessage('foo');

        Map::build(
            [
                'foo' => false,
            ],
            new Structures,
            new Properties
        );
    }

    public function testThrowWhenAKeyIsMissing()
    {
        $map = Map::build(
            [
                'key' => 'bool',
            ],
            new Structures,
            new Properties
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('key');

        $map->process([]);
    }

    public function testThrowWhenANestedStructureIsMissing()
    {
        $map = Map::build(
            [
                'foo' => [
                    'key' => 'bool',
                ],
            ],
            new Structures,
            new Properties
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('foo');

        $map->process([]);
    }

    public function testThrowWhenNestedStructureNotOfExpectedType()
    {
        $map = Map::build(
            [
                'foo' => [
                    'key' => 'bool',
                ],
            ],
            new Structures,
            new Properties
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('foo');

        $map->process(['foo' => false]);
    }
}
