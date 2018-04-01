<?php
declare(strict_types = 1);

namespace Innmind\Config;

use Innmind\Config\Exception\{
    DomainException,
    SchemaNotParseable,
};
use Innmind\Immutable\{
    StreamInterface,
    Stream,
    Str,
};

final class Properties
{
    private static $defaults;
    private $properties;

    public function __construct(string ...$properties)
    {
        $properties = Stream::of('string', ...$properties);

        if ($properties->size() === 0) {
            $properties = self::defaults();
        }

        $properties->foreach(static function(string $property): void {
            $refl = new \ReflectionClass($property);

            if (!$refl->implementsInterface(Property::class)) {
                throw new DomainException($property);
            }
        });

        $this->properties = $properties;
    }

    public function build(Str $schema): Property
    {
        foreach ($this->properties as $property) {
            try {
                return [$property, 'build']($schema, $this);
            } catch (SchemaNotParseable $e) {
                //pass
            }
        }

        throw new SchemaNotParseable((string) $schema);
    }

    /**
     * @return StreamInterface<string>
     */
    public static function defaults(): StreamInterface
    {
        return self::$defaults ?? self::$defaults = Stream::of(
            'string',
            Property\Primitive::class,
            Property\Set::class,
            Property\Stream::class,
            Property\Sequence::class
        );
    }
}
