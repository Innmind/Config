<?php
declare(strict_types = 1);

namespace Innmind\Config\Structure;

use Innmind\Config\{
    Structure,
    Structures,
    Properties,
    Property,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable;
use Innmind\Immutable\{
    MapInterface,
    Str,
};

final class Map implements Structure
{
    private $structures;
    private $properties;

    private function __construct(Immutable\Map $structures, Immutable\Map $properties)
    {
        $this->structures = $structures;
        $this->properties = $properties;
    }

    public static function build(
        array $schema,
        Structures $structures,
        Properties $properties
    ): Structure {
        $structuresMap = new Immutable\Map('string', Structure::class);
        $propertiesMap = new Immutable\Map('string', Property::class);

        foreach ($schema as $key => $value) {
            if (is_array($value)) {
                $structuresMap = $structuresMap->put(
                    $key,
                    $structures->build($value, $properties)
                );

                continue;
            }

            if (!is_string($value)) {
                throw new SchemaNotParseable((string) $key);
            }

            $propertiesMap = $propertiesMap->put(
                $key,
                $properties->build(
                    Str::of($value),
                    $properties
                )
            );
        }

        return new self($structuresMap, $propertiesMap);
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data): MapInterface
    {
        $structures = $this
            ->structures
            ->foreach(static function(string $key) use ($data): void {
                if (!array_key_exists($key, $data)) {
                    throw new InvalidArgumentException($key);
                }

                if (!is_array($data[$key])) {
                    throw new InvalidArgumentException($key);
                }
            })
            ->reduce(
                new Immutable\Map('scalar', 'mixed'),
                static function(MapInterface $processed, string $key, Structure $structure) use ($data): MapInterface {
                    return $processed->put(
                        $key,
                        $structure->process($data[$key])
                    );
                }
            );
        $properties = $this
            ->properties
            ->reduce(
                new Immutable\Map('scalar', 'mixed'),
                static function(MapInterface $processed, string $key, Property $property) use ($data): MapInterface {
                    try {
                        return $processed->put(
                            $key,
                            $property->process($data[$key] ?? null)
                        );
                    } catch (InvalidArgumentException $e) {
                        throw new InvalidArgumentException($key, 0, $e);
                    }
                }
            );

        return $structures->merge($properties);
    }
}
