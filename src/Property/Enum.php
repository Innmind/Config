<?php
declare(strict_types = 1);

namespace Innmind\Config\Property;

use Innmind\Config\{
    Property,
    Properties,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\{
    Str,
    SetInterface,
    Set,
};

final class Enum implements Property
{
    private const PATTERN = '~^\??enum\((?<values>.+)\)$~';

    private $values;
    private $optional = false;

    private function __construct(Set $values)
    {
        $this->values = $values;
    }

    public static function build(Str $schema, Properties $properties): Property
    {
        if (!$schema->matches(self::PATTERN)) {
            throw new SchemaNotParseable((string) $schema);
        }

        $self = new self(
            $schema
                ->capture(self::PATTERN)
                ->get('values')
                ->split('|')
                ->reduce(
                    Set::of('string'),
                    static function(SetInterface $values, Str $value): SetInterface {
                        return $values->add((string) $value);
                    }
                )
        );

        if ((string) $schema->substring(0, 1) === '?') {
            $self->optional = true;
        }

        return $self;
    }

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        if (is_null($value) && $this->optional) {
            return null;
        }

        if (!$this->values->contains($value)) {
            throw new InvalidArgumentException;
        }

        return $value;
    }
}
