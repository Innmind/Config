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

final class Stream implements Property
{
    private const PATTERN = '~^stream<(?<type>.+)>\+?$~';

    private $type;
    private $requiresValue = false;

    private function __construct(string $type)
    {
        $this->type = $type;
    }

    public static function build(Immutable\Str $schema, Properties $properties): Property
    {
        if (!$schema->matches(self::PATTERN)) {
            throw new SchemaNotParseable((string) $schema);
        }

        $self = new self(
            (string) $schema->capture(self::PATTERN)->get('type')
        );

        if ((string) $schema->substring(-1) === '+') {
            $self->requiresValue = true;
        }

        return $self;
    }

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        if ($value instanceof Immutable\StreamInterface && (string) $value->type() === $this->type) {
            if ($this->requiresValue && $value->size() === 0) {
                throw new InvalidArgumentException;
            }

            return $value;
        }

        if ($value instanceof Immutable\StreamInterface) {
            throw new InvalidArgumentException;
        }

        $value = $value ?? [];

        if (!is_array($value)) {
            throw new InvalidArgumentException;
        }

        $stream = Immutable\Stream::of($this->type, ...$value);

        if ($this->requiresValue && $stream->size() === 0) {
            throw new InvalidArgumentException;
        }

        return $stream;
    }
}
