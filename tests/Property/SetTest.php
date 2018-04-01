<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config\Property;

use Innmind\Config\{
    Property\Set,
    Property,
    Properties,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable;
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class SetTest extends TestCase
{
    /**
     * @dataProvider patterns
     */
    public function testBuild($pattern)
    {
        $property = Set::build(Str::of($pattern), new Properties);

        $this->assertInstanceOf(Set::class, $property);
        $this->assertInstanceOf(Property::class, $property);
    }

    public function testThrowWhenInvalidPattern()
    {
        $this->expectException(SchemaNotParseable::class);
        $this->expectExceptionMessage('foo');

        Set::build(Str::of('foo'), new Properties);
    }

    /**
     * @dataProvider values
     */
    public function testProcess($pattern, $value, $expected)
    {
        $this->assertTrue(
            $expected->equals(
                Set::build(Str::of($pattern), new Properties)->process($value)
            )
        );
    }

    /**
     * @dataProvider invalidValues
     */
    public function testThrowWhenInvalidValue($pattern, $value)
    {
        $this->expectException(InvalidArgumentException::class);

        Set::build(Str::of($pattern), new Properties)->process($value);
    }

    public function patterns(): array
    {
        return [
            ['set<int>'],
            ['set<int>+'],
        ];
    }

    public function values(): array
    {
        return [
            ['set<int>', null, Immutable\Set::of('int')],
            ['set<int>', [], Immutable\Set::of('int')],
            ['set<int>', [42], Immutable\Set::of('int', 42)],
            ['set<int>+', [42], Immutable\Set::of('int', 42)],
        ];
    }

    public function invalidValues(): array
    {
        return [
            ['set<int>', 42],
            ['set<int>+', 42],
            ['set<int>+', null],
            ['set<int>+', []],
            ['set<int>+', Immutable\Set::of('int')],
        ];
    }
}
