<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config;

use Innmind\Config\{
    Properties,
    Property,
    Exception\SchemaNotParseable,
    Exception\DomainException,
};
use Innmind\Immutable\{
    Str,
    Stream,
};
use PHPUnit\Framework\TestCase;

class PropertiesTest extends TestCase
{
    /**
     * @dataProvider patterns
     */
    public function testBuild($pattern, $expected)
    {
        $properties = new Properties;

        $this->assertInstanceOf($expected, $properties->build(Str::of($pattern)));
    }

    public function testDefaults()
    {
        $defaults = Properties::defaults();

        $this->assertTrue(
            $defaults->equals(Stream::of(
                'string',
                Property\Primitive::class,
                Property\Set::class,
                Property\Stream::class,
                Property\Sequence::class
            ))
        );
        $this->assertSame($defaults, Properties::defaults());
    }

    public function testDoesntLoadDefaultsWhenSpecifyingTheProperties()
    {
        $properties = new Properties(Property\Primitive::class);

        $this->expectException(SchemaNotParseable::class);

        $properties->build(Str::of('set<int>'));
    }

    public function testThrowWhenInvalidProperty()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('stdClass');

        new Properties('stdClass');
    }

    public function patterns(): array
    {
        return [
            ['int', Property\Primitive::class],
            ['sequence', Property\Sequence::class],
            ['set<int>', Property\Set::class],
            ['stream<int>', Property\Stream::class],
        ];
    }
}
