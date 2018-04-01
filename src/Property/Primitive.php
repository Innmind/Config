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

final class Primitive implements Property
{
    private const PATTERN = '~^\??(?<type>.+)$~';

    private $function;
    private $optional = false;

    private function __construct(string $type)
    {
        $this->function = 'is_'.$type;
    }

    public static function build(Str $schema, Properties $properties): Property
    {
        if (!$schema->matches(self::PATTERN)) {
            throw new SchemaNotParseable((string) $schema);
        }

        $type = (string) $schema->capture(self::PATTERN)->get('type');

        if (!function_exists('is_'.$type)) {
            throw new SchemaNotParseable((string) $schema);
        }

        $self = new self($type);

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

        if (!($this->function)($value)) {
            throw new InvalidArgumentException;
        }

        return $value;
    }
}
