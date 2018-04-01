<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config\Property;

use Innmind\Config\{
    Property\Stream,
    Property,
    Properties,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable;
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    /**
     * @dataProvider patterns
     */
    public function testBuild($pattern)
    {
        $property = Stream::build(Str::of($pattern), new Properties);

        $this->assertInstanceOf(Stream::class, $property);
        $this->assertInstanceOf(Property::class, $property);
    }

    public function testThrowWhenInvalidPattern()
    {
        $this->expectException(SchemaNotParseable::class);
        $this->expectExceptionMessage('foo');

        Stream::build(Str::of('foo'), new Properties);
    }

    /**
     * @dataProvider values
     */
    public function testProcess($pattern, $value, $expected)
    {
        $this->assertTrue(
            $expected->equals(
                Stream::build(Str::of($pattern), new Properties)->process($value)
            )
        );
    }

    /**
     * @dataProvider invalidValues
     */
    public function testThrowWhenInvalidValue($pattern, $value)
    {
        $this->expectException(InvalidArgumentException::class);

        Stream::build(Str::of($pattern), new Properties)->process($value);
    }

    public function patterns(): array
    {
        return [
            ['stream<int>'],
            ['stream<int>+'],
        ];
    }

    public function values(): array
    {
        return [
            ['stream<int>', null, Immutable\Stream::of('int')],
            ['stream<int>', [], Immutable\Stream::of('int')],
            ['stream<int>', [42], Immutable\Stream::of('int', 42)],
            ['stream<int>+', [42], Immutable\Stream::of('int', 42)],
        ];
    }

    public function invalidValues(): array
    {
        return [
            ['stream<int>', 42],
            ['stream<int>+', 42],
            ['stream<int>+', null],
            ['stream<int>+', []],
            ['stream<int>+', Immutable\Stream::of('int')],
            ['stream<int>', Immutable\Stream::of('string')],
        ];
    }
}
