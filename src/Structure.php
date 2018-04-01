<?php
declare(strict_types = 1);

namespace Innmind\Config;

use Innmind\Immutable\MapInterface;

interface Structure
{
    public static function build(
        array $schema,
        Structures $structures,
        Properties $properties
    ): self;

    /**
     * @return MapInterface<scalar, mixed>
     */
    public function process(array $data): MapInterface;
}
