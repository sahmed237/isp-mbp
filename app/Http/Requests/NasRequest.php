<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('network.devices.create') || $this->user()?->hasRole('Super Administrator') || $this->user()?->hasRole('Network Administrator');
    }

    public function rules(): array
    {
        return [
            'nasname' => ['required', 'string', 'max:128'],
            'shortname' => ['required', 'string', 'max:32'],
            'type' => ['required', 'string', 'max:30'],
            'ports' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'secret' => ['required', 'string', 'min:4', 'max:60'],
            'description' => ['nullable', 'string', 'max:200'],
        ];
    }
}
