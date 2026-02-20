<?php

declare(strict_types=1);

namespace Compass\Writers;

use Symfony\Component\Yaml\Yaml;

final class YamlWriter
{
    public function write(array $spec, string $path): void
    {
        file_put_contents($path, Yaml::dump($spec, 10, 2, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_NUMERIC_KEY_AS_STRING));
    }
}
