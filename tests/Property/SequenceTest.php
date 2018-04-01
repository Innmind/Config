<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config\Property;

use Innmind\Config\{
    Property\Sequence,
    Property,
    Properties,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable;
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class SequenceTest extends TestCase
{
    /**
     * @dataProvider patterns
     */
    public function testBuild($pattern)
    {
        $property = Sequence::build(Str::of($pattern), new Properties);

        $this->assertInstanceOf(Sequence::class, $property);
        $this->assertInstanceOf(Property::class, $property);
    }

    public function testThrowWhenInvalidPattern()
    {
        $this->expectException(SchemaNotParseable::class);
        $this->expectExceptionMessage('foo');

        Sequence::build(Str::of('foo'), new Properties);
    }

    /**
     * @dataProvider values
     */
    public function testProcess($pattern, $value, $expected)
    {
        $this->assertTrue(
            $expected->equals(
                Sequence::build(Str::of($pattern), new Properties)->process($value)
            )
        );
    }

    /**
     * @dataProvider invalidValues
     */
    public function testThrowWhenInvalidValue($pattern, $value)
    {
        $this->expectException(InvalidArgumentException::class);

        Sequence::build(Str::of($pattern), new Properties)->process($value);
    }

    public function patterns(): array
    {
        return [
            ['sequence'],
            ['sequence+'],
        ];
    }

    public function values(): array
    {
        return [
            ['sequence', null, new Immutable\Sequence],
            ['sequence', new Immutable\Sequence, new Immutable\Sequence],
            ['sequence', new Immutable\Sequence('foo'), new Immutable\Sequence('foo')],
            ['sequence', ['foo'], new Immutable\Sequence('foo')],
            ['sequence', [], new Immutable\Sequence],
            ['sequence+', new Immutable\Sequence('foo'), new Immutable\Sequence('foo')],
            ['sequence+', ['foo'], new Immutable\Sequence('foo')],
        ];
    }

    public function invalidValues(): array
    {
        return [
            ['sequence', 42],
            ['sequence+', null],
            ['sequence+', new Immutable\Sequence],
            ['sequence+', []],
        ];
    }
}
