<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePersonalAccessTokenRequest extends FormRequest
{
    /**
     * Determine whether the authenticated user may create a PAT.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:1',
                'max:100',
            ],
        ];
    }

    /**
     * Normalize PAT input before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim(
                    (string) $this->input('name'),
                ),
            ]);
        }
    }
}
