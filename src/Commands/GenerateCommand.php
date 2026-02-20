<?php

declare(strict_types=1);

namespace Compass\Commands;

use Compass\CompassGenerator;
use Illuminate\Console\Command;

final class GenerateCommand extends Command
{
    protected $signature = 'compass:generate';

    protected $description = 'Generate OpenAPI documentation from your Laravel routes';

    public function handle(CompassGenerator $generator): int
    {
        $this->info('Generating OpenAPI documentation...');

        $files = $generator->writeFiles();

        foreach ($files as $file) {
            $this->line("  ✓ Written: {$file}");
        }

        $this->newLine();
        $this->info('Done!');

        return self::SUCCESS;
    }
}
