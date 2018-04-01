<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config\Structure;

use Innmind\Config\{
    Structure\Prototype,
    Structure\Map,
    Structure,
    Structures,
    Properties,
    Exception\OnlyOnePrototypeDefinitionAllowed,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\MapInterface;
use PHPUnit\Framework\TestCase;

class PrototypeTest extends TestCase
{
    public function testInterface()
    {
        $structure = Prototype::build(
            ['prototype<int>' => 'bool'],
            new Structures,
            new Properties
        );

        $this->assertInstanceOf(Prototype::class, $structure);
        $this->assertInstanceOf(Structure::class, $structure);
    }

    public function testThrowWhenNoPrototypeDefinition()
    {
        $this->expectException(SchemaNotParseable::class);

        Prototype::build(
            [],
            new Structures,
            new Properties
        );
    }

    public function testThrowWhenMultiplePrototypes()
    {
        $this->expectException(OnlyOnePrototypeDefinitionAllowed::class);

        Prototype::build(
            [
                'prototype<int>' => 'string',
                'prototype<string>' => 'string',
            ],
            new Structures,
            new Properties
        );
    }

    public function testThrowWhenInvalidPrototypeDefinition()
    {
        $this->expectException(SchemaNotParseable::class);
        $this->expectExceptionMessage('prototype<int>');

        Prototype::build(
            [
                'prototype<int>' => true,
            ],
            new Structures,
            new Properties
        );
    }

    public function testProcessOnlyStructurePrototype()
    {
        $structure = Prototype::build(
            [
                'prototype<string>' => [
                    'nested' => 'int',
                ],
            ],
            new Structures,
            new Properties
        );

        $result = $structure->process([
            'foo' => [
                'nested' => 42,
            ],
            'bar' => [
                'nested' => 24,
            ],
        ]);

        $this->assertInstanceOf(MapInterface::class, $result);
        $this->assertSame('scalar', (string) $result->keyType());
        $this->assertSame('mixed', (string) $result->valueType());
        $this->assertCount(2, $result);
        $foo = $result->get('foo');
        $this->assertInstanceOf(MapInterface::class, $foo);
        $this->assertSame('scalar', (string) $foo->keyType());
        $this->assertSame('mixed', (string) $foo->valueType());
        $this->assertCount(1, $foo);
        $this->assertSame(42, $foo->get('nested'));
        $bar = $result->get('bar');
        $this->assertInstanceOf(MapInterface::class, $bar);
        $this->assertSame('scalar', (string) $bar->keyType());
        $this->assertSame('mixed', (string) $bar->valueType());
        $this->assertCount(1, $bar);
        $this->assertSame(24, $bar->get('nested'));
    }

    public function testProcessOnlyPropertyPrototype()
    {
        $structure = Prototype::build(
            [
                'prototype<string>' => 'int',
            ],
            new Structures,
            new Properties
        );

        $result = $structure->process([
            'foo' => 42,
            'bar' => 24,
        ]);

        $this->assertInstanceOf(MapInterface::class, $result);
        $this->assertSame('scalar', (string) $result->keyType());
        $this->assertSame('mixed', (string) $result->valueType());
        $this->assertCount(2, $result);
        $this->assertSame(42, $result->get('foo'));
        $this->assertSame(24, $result->get('bar'));
    }

    public function testThrowWhenPrototypeKeyIsInvalid()
    {
        $structure = Prototype::build(
            [
                'prototype<string>' => 'int',
            ],
            new Structures,
            new Properties
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('24');

        $structure->process([
            'foo' => 42,
            24 => 24,
        ]);
    }

    public function testThrowWhenPrototypeValueIsInvalid()
    {
        $structure = Prototype::build(
            [
                'prototype<string>' => 'int',
            ],
            new Structures,
            new Properties
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('bar');

        $structure->process([
            'foo' => 42,
            'bar' => true,
        ]);
    }

    public function testProcessCombinedStrutureWithPrototype()
    {
        $structure = Prototype::build(
            [
                'foo' => 'int',
                'prototype<int>' => 'float',
                'bar' => 'string',
            ],
            new Structures,
            new Properties
        );

        $result = $structure->process([
            'foo' => 24,
            42 => 24.42,
            43 => 34.43,
            'bar' => 'foo',
            44 => 44.44,
        ]);

        $this->assertInstanceOf(MapInterface::class, $result);
        $this->assertSame('scalar', (string) $result->keyType());
        $this->assertSame('mixed', (string) $result->valueType());
        $this->assertCount(5, $result);
        $this->assertSame(24, $result->get('foo'));
        $this->assertSame(24.42, $result->get(42));
        $this->assertSame(34.43, $result->get(43));
        $this->assertSame('foo', $result->get('bar'));
        $this->assertSame(44.44, $result->get(44));
    }

    public function testThrowWhenNoValueButOneRequired()
    {
        $this->expectException(InvalidArgumentException::class);

        Prototype::build(['prototype<int>+' => 'bool'], new Structures, new Properties)->process([]);
    }
}
