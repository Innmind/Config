<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config;

use Innmind\Config\{
    Structures,
    Structure,
    Properties,
    Property,
    Exception\DomainException,
    Exception\SchemaNotParseable,
};
use Innmind\Immutable\StreamInterface;
use PHPUnit\Framework\TestCase;

class StructuresTest extends TestCase
{
    /**
     * @dataProvider schemas
     */
    public function testBuild($schema, $expected)
    {
        $structure = (new Structures)->build($schema);

        $this->assertInstanceOf($expected, $structure);
    }

    public function testDefaults()
    {
        $defaults = Structures::defaults();

        $this->assertInstanceOf(StreamInterface::class, $defaults);
        $this->assertSame(
            [Structure\Prototype::class, Structure\Map::class],
            $defaults->toPrimitive()
        );
        $this->assertSame($defaults, Structures::defaults());
    }

    public function testThrowWhenInvalidStructure()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('stdClass');

        new Structures(null, 'stdClass');
    }

    public function testDefaultsNotLoadedWhenSpecifyingStructures()
    {
        $structures = new Structures(null, Structure\Prototype::class);

        $this->expectException(SchemaNotParseable::class);

        $structures->build(['foo' => 'int']);
    }

    public function testAllowToSpecifyProperties()
    {
        $structures = new Structures(new Properties(Property\Sequence::class));

        $this->expectException(SchemaNotParseable::class);

        $structures->build(['foo' => 'int']);
    }

    public function schemas(): array
    {
        return [
            [[], Structure\Map::class],
            [['foo' => 'string'], Structure\Map::class],
            [['prototype<scalar>' => 'string'], Structure\Prototype::class],
        ];
    }
}
