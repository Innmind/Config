<?php
declare(strict_types = 1);

namespace Innmind\Config\Property;

use Innmind\Config\{
    Property,
    Properties,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\Str;

final class Mixed implements Property
{
    public static function build(Str $schema, Properties $properties): Property
    {
        if ((string) $schema !== 'mixed') {
            throw new SchemaNotParseable((string) $schema);
        }

        return new self;
    }

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        return $value;
    }
}
