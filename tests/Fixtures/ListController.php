<?php

declare(strict_types=1);

namespace Compass\Tests\Fixtures;

final class ListController
{
    public function __invoke(): UserResource
    {
        // Fixture controller - no request
    }
}
