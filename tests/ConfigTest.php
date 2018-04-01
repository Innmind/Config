<?php
declare(strict_types = 1);

namespace Tests\Innmind\Config;

use Innmind\Config\{
    Config,
    Structures,
    Structure,
    Properties,
    Property,
    Exception\SchemaNotParseable,
};
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testInterface()
    {
        $config = new Config;

        $this->assertInstanceOf(Structure::class, $config->build([]));
    }

    public function testAllowToSpecifyStructures()
    {
        $config = new Config(new Structures(Structure\Prototype::class));

        $this->expectException(SchemaNotParseable::class);

        $config->build(['foo' => 'int']);
    }

    public function testAllowToSpecifyProperties()
    {
        $config = new Config(null, new Properties(Property\Sequence::class));

        $this->expectException(SchemaNotParseable::class);

        $config->build(['foo' => 'int']);
    }
}
