<?php

declare(strict_types=1);

namespace Compass\Tests;

use Compass\CompassServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CompassServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('compass.title', 'Test API');
        $app['config']->set('compass.version', '1.0.0');
        $app['config']->set('compass.output.path', sys_get_temp_dir() . '/compass-test');
        $app['config']->set('compass.ui.enabled', false);
    }
}
