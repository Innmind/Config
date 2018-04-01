<?php
declare(strict_types = 1);

namespace Innmind\Config;

use Innmind\Config\Exception\SchemaNotParseable;

final class Config
{
    private $structures;
    private $properties;

    public function __construct(
        Structures $structures = null,
        Properties $properties = null
   ) {
        $this->structures = $structures ?? new Structures;
        $this->properties = $properties ?? new Properties;
    }

    public function build(array $schema): Structure
    {
        try {
            return $this->structures->build($schema, $this->properties);
        } catch (SchemaNotParseable $e) {
            SchemaNotParseable::rethrow($e);
        }
    }
}
