<?php
declare(strict_types = 1);

namespace Innmind\Config\Property;

use Innmind\Config\{
    Property,
    Properties,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable;

final class Sequence implements Property
{
    private const PATTERN = '~^sequence\+?$~';

    private $requiresValue = false;

    private function __construct(bool $requiresValue)
    {
        $this->requiresValue = $requiresValue;
    }

    public static function build(Immutable\Str $schema, Properties $properties): Property
    {
        if (!$schema->matches(self::PATTERN)) {
            throw new SchemaNotParseable((string) $schema);
        }

        return new self((string) $schema->substring(-1) === '+');
    }

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        if (is_null($value)) {
            if ($this->requiresValue) {
                throw new InvalidArgumentException;
            }

            return new Immutable\Sequence;
        }

        if ($value instanceof Immutable\Sequence) {
            if ($this->requiresValue && $value->size() === 0) {
                throw new InvalidArgumentException;
            }

            return $value;
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException;
        }

        $sequence = Immutable\Sequence::of(...$value);

        if ($this->requiresValue && $sequence->size() === 0) {
            throw new InvalidArgumentException;
        }

        return $sequence;
    }
}
