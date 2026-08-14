<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\RepositoryVisibility;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRepositoryRequest extends FormRequest
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
                'required',
                'string',
                'min:1',
                'max:100',
                'regex:/\A[a-zA-Z0-9][a-zA-Z0-9._-]*\z/',
                'not_regex:/\.git\z/i',
                Rule::notIn(['.', '..']),
            ],
            'slug' => [
                'required',
                'string',
                'max:100',
                'regex:/\A[a-z0-9][a-z0-9-]*\z/',
                Rule::unique('repositories', 'slug')
                    ->where(
                        fn (Builder $query): Builder => $query->where(
                            'owner_id',
                            $this->user()->id,
                        ),
                    )
                    ->withoutTrashed(),
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'visibility' => [
                'required',
                Rule::enum(RepositoryVisibility::class),
            ],
        ];
    }

    /**
     * Prepare input before validation.
     */
    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));

        $description = $this->input('description');

        if (is_string($description)) {
            $description = trim($description);
            $description = $description === '' ? null : $description;
        }

        $this->merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $description,
            'visibility' => $this->input(
                'visibility',
                RepositoryVisibility::PrivateAccess->value,
            ),
        ]);
    }
}
