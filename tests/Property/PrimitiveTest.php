<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config\Property;

use Innmind\Config\{
    Property\Primitive,
    Property,
    Properties,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class PrimitiveTest extends TestCase
{
    /**
     * @dataProvider patterns
     */
    public function testBuild($pattern)
    {
        $property = Primitive::build(Str::of($pattern), new Properties);

        $this->assertInstanceOf(Primitive::class, $property);
        $this->assertInstanceOf(Property::class, $property);
    }

    public function testThrowWhenNotAPrimitive()
    {
        $this->expectException(SchemaNotParseable::class);
        $this->expectExceptionMessage('foo');

        Primitive::build(Str::of('foo'), new Properties);
    }

    public function testThrowWhenInvalidPattern()
    {
        $this->expectException(SchemaNotParseable::class);
        $this->expectExceptionMessage('int+');

        Primitive::build(Str::of('int+'), new Properties);
    }

    /**
     * @dataProvider patterns
     */
    public function testProcess($pattern, $value)
    {
        $this->assertSame(
            $value,
            Primitive::build(Str::of($pattern), new Properties)->process($value)
        );
    }

    public function testThrowWhenInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);

        Primitive::build(Str::of('int'), new Properties)->process('42');
    }

    public function patterns(): array
    {
        return [
            ['int', 42],
            ['float', 24.42],
            ['bool', true],
            ['array', ['foo']],
            ['resource', tmpfile()],
            ['object', new \stdClass],
            ['?int', null],
            ['?float', null],
            ['?bool', null],
            ['?array', null],
            ['?resource', null],
            ['?object', null],
        ];
    }
}
