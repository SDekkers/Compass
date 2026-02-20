<?php

declare(strict_types=1);

namespace Compass\Tests\Fixtures;

final class UserController
{
    public function __invoke(StoreUserRequest $request): UserResource
    {
        // Fixture controller
    }
}
