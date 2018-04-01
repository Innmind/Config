<?php
declare(strict_types = 1);

namespace Innmind\Config\Structure;

use Innmind\Config\{
    Structure,
    Structures,
    Properties,
    Property,
    Exception\OnlyOnePrototypeDefinitionAllowed,
    Exception\SchemaNotParseable,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\{
    MapInterface,
    Map,
    Str,
};

final class Prototype implements Structure
{
    private const PATTERN = '~^prototype<(?<type>(int|string|scalar))>$~';
    private $prototype;
    private $prototypeKey;
    private $structure;
    private $structureKeys;

    /**
     * @param Property\Structure $prototype
     */
    private function __construct(
        $prototype,
        Property $prototypeKey,
        Structure $structure,
        array $structureKeys
    ) {
        $this->prototype = $prototype;
        $this->prototypeKey = $prototypeKey;
        $this->structure = $structure;
        $this->structureKeys = array_flip($structureKeys);
    }

    public static function build(
        array $schema,
        Structures $structures,
        Properties $properties
    ): Structure {
        $prototype = $prototypeKey = null;
        $keyToRemove = null;

        foreach ($schema as $key => $value) {
            if (!is_string($key) || !Str::of($key)->matches(self::PATTERN)) {
                continue;
            }

            if (!is_null($prototype)) {
                throw new OnlyOnePrototypeDefinitionAllowed;
            }

            $prototypeKey = $properties->build(
                Str::of($key)->capture(self::PATTERN)->get('type')
            );
            $keyToRemove = $key;

            if (is_string($value)) {
                $prototype = $properties->build(Str::of($value));

                continue;
            }

            if (is_array($value)) {
                $prototype = $structures->build($value, $properties);

                continue;
            }

            throw new SchemaNotParseable($key);
        }

        if (is_null($prototype)) {
            throw new SchemaNotParseable;
        }

        unset($schema[$keyToRemove]);

        return new self(
            $prototype,
            $prototypeKey,
            $structures->build($schema, $properties),
            array_keys($schema)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data): MapInterface
    {
        $prototypes = array_diff_key($data, $this->structureKeys);
        $map = new Map('scalar', 'mixed');

        foreach ($prototypes as $key => $value) {
            try {
                $map = $map->put(
                    $this->prototypeKey->process($key),
                    $this->prototype->process($value)
                );
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException((string) $key, 0, $e);
            }
        }

        return $map->merge(
            $this->structure->process(
                array_intersect_key($data, $this->structureKeys)
            )
        );
    }
}
