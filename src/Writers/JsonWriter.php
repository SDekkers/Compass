<?php

declare(strict_types=1);

namespace Compass\Writers;

final class JsonWriter
{
    public function write(array $spec, string $path): void
    {
        file_put_contents(
            $path,
            json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );
    }
}
