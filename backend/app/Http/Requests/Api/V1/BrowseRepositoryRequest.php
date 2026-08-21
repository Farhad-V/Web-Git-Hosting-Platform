<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BrowseRepositoryRequest extends FormRequest
{
    /**
     * Determine whether the request may proceed to policy authorization.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'branch' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'path' => [
                'sometimes',
                'nullable',
                'string',
                'max:4096',
            ],
            'limit' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    /**
     * Normalize scalar query parameters before validation.
     */
    protected function prepareForValidation(): void
    {
        $prepared = [];

        if ($this->has('branch')) {
            $prepared['branch'] = trim(
                (string) $this->input('branch'),
            );
        }

        if ($this->has('path')) {
            $path = $this->input('path');

            if (is_string($path)) {
                $path = trim($path);
            }

            $prepared['path'] = $path;
        }

        $this->merge($prepared);
    }
}
