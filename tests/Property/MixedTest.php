<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config\Property;

use Innmind\Config\{
    Property\Mixed,
    Property,
    Properties,
    Exception\SchemaNotParseable,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class MixedTest extends TestCase
{
    public function testBuild()
    {
        $property = Mixed::build(Str::of('mixed'), new Properties);

        $this->assertInstanceOf(Mixed::class, $property);
        $this->assertInstanceOf(Property::class, $property);
    }

    public function testThrowWhenInvalidPattern()
    {
        $this->expectException(SchemaNotParseable::class);
        $this->expectExceptionMessage('foo');

        Mixed::build(Str::of('foo'), new Properties);
    }

    /**
     * @dataProvider values
     */
    public function testProcess($value)
    {
        $this->assertSame(
            $value,
            Mixed::build(Str::of('mixed'), new Properties)->process($value)
        );
    }

    public function values(): array
    {
        return [
            [42],
            [24.42],
            [true],
            [['foo']],
            [tmpfile()],
            [new \stdClass],
            [null],
        ];
    }
}
