<?php
declare(strict_types = 1);

namespace Innmind\Config\Exception;

use Innmind\Immutable\Sequence;

final class SchemaNotParseable extends DomainException
{
    public static function rethrow(self $exception): self
    {
        $original = $exception;
        $messages = new Sequence;

        do {
            $messages = $messages->add($exception->getMessage());
        } while ($exception = $exception->getPrevious());

        throw new self(
            (string) $messages
                ->filter(static function(string $message): bool {
                    return $message !== '';
                })
                ->join('.'),
            0,
            $original
        );
    }
}
