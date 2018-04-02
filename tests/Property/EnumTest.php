<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config\Property;

use Innmind\Config\{
    Property\Enum,
    Property,
    Properties,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    /**
     * @dataProvider patterns
     */
    public function testBuild($pattern)
    {
        $property = Enum::build(Str::of($pattern), new Properties);

        $this->assertInstanceOf(Enum::class, $property);
        $this->assertInstanceOf(Property::class, $property);
    }

    public function testThrowWhenNotAEnum()
    {
        $this->expectException(SchemaNotParseable::class);
        $this->expectExceptionMessage('foo');

        Enum::build(Str::of('foo'), new Properties);
    }

    public function testThrowWhenInvalidPattern()
    {
        $this->expectException(SchemaNotParseable::class);
        $this->expectExceptionMessage('enum()+');

        Enum::build(Str::of('enum()+'), new Properties);
    }

    /**
     * @dataProvider patterns
     */
    public function testProcess($pattern, $value)
    {
        $this->assertSame(
            $value,
            Enum::build(Str::of($pattern), new Properties)->process($value)
        );
    }

    public function testThrowWhenInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);

        Enum::build(Str::of('enum(foo)'), new Properties)->process('bar');
    }

    public function patterns(): array
    {
        return [
            ['enum(foo)', 'foo'],
            ['enum(foo|bar)', 'bar'],
            ['enum(foo|bar|baz)', 'bar'],
            ['?enum(foo)', null],
            ['?enum(foo|bar)', null],
            ['?enum(foo|bar|baz)', null],
        ];
    }
}
