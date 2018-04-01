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
    private const PATTERN = '~^prototype<(?<type>(int|string|scalar))>\+?$~';
    private $prototype;
    private $prototypeKey;
    private $structure;
    private $structureKeys;
    private $requiresValue;

    /**
     * @param Property\Structure $prototype
     */
    private function __construct(
        $prototype,
        Property $prototypeKey,
        Structure $structure,
        array $structureKeys,
        bool $requiresValue
    ) {
        $this->prototype = $prototype;
        $this->prototypeKey = $prototypeKey;
        $this->structure = $structure;
        $this->structureKeys = array_flip($structureKeys);
        $this->requiresValue = $requiresValue;
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
                try {
                    $prototype = $properties->build(Str::of($value));
                } catch (SchemaNotParseable $e) {
                    throw new SchemaNotParseable($key, 0, $e);
                }

                continue;
            }

            if (is_array($value)) {
                try {
                    $prototype = $structures->build($value, $properties);
                } catch (SchemaNotParseable $e) {
                    throw new SchemaNotParseable($key, 0, $e);
                }

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
            array_keys($schema),
            (string) Str::of($key)->substring(-1) === '+'
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

        if ($this->requiresValue && $map->size() === 0) {
            throw new InvalidArgumentException;
        }

        return $map->merge(
            $this->structure->process(
                array_intersect_key($data, $this->structureKeys)
            )
        );
    }
}
