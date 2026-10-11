<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\RepositoryVisibility;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
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
            'name' => [
                'required_without:visibility',
                'string',
                'min:1',
                'max:100',
                'regex:/\A[a-z0-9](?:[a-z0-9._-]{0,98}[a-z0-9])?\z/',
                'not_regex:/\.\./',
                Rule::unique('repositories', 'name')->ignore($this->route('repository')->id)
                    ->where(
                        fn (Builder $query): Builder => $query->where(
                            'owner_id',
                            $this->user()->id,
                        ),
                    ),
            ],
            'visibility' => [
                'required_without:name',
                Rule::enum(RepositoryVisibility::class),
            ],
        ];
    }

    /**
     * Prepare input before validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge([
                'name' => mb_strtolower(
                    trim((string) $this->input('name')),
                ),
            ]);
        }

    }
}
