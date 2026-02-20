<?php

declare(strict_types=1);

namespace Compass\Tests\Fixtures;

use Illuminate\Foundation\Http\FormRequest;

final class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'age' => 'nullable|integer|min:0|max:150',
            'role' => 'required|in:admin,user,editor',
            'uuid' => 'required|uuid',
        ];
    }
}
