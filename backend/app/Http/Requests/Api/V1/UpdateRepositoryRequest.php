<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\RepositoryVisibility;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRepositoryRequest extends FormRequest
{
    /**
     * Determine whether the user may make this request.
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
            'visibility' => [
                'required',
                Rule::enum(RepositoryVisibility::class),
            ],
        ];
    }
}