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
};

final class Structures
{
    private static $defaults;
    private $structures;

    public function __construct(string ...$structures)
    {
        $structures = Stream::of('string', ...$structures);

        if ($structures->size() === 0) {
            $structures = self::defaults();
        }

        $structures->foreach(static function(string $structure): void {
            $refl = new \ReflectionClass($structure);

            if (!$refl->implementsInterface(Structure::class)) {
                throw new DomainException($structure);
            }
        });

        $this->structures = $structures;
    }

    public function build(array $schema, Properties $properties): Structure
    {
        foreach ($this->structures as $structure) {
            try {
                return [$structure, 'build']($schema, $this, $properties);
            } catch (SchemaNotParseable $e) {
                //pass
            }
        }

        throw new SchemaNotParseable('', 0, $e ?? null);
    }

    /**
     * @return StreamInterface<Structure>
     */
    public static function defaults(): StreamInterface
    {
        return self::$defaults ?? self::$defaults = Stream::of(
            'string',
            Structure\Prototype::class,
            Structure\Map::class
        );
    }
}
