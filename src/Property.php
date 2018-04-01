<?php
declare(strict_types = 1);

namespace Innmind\Config;

use Innmind\Immutable\Str;

interface Property
{
    public static function build(Str $schema, Properties $properties): self;

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function process($value);
}
