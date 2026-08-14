<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\RepositoryVisibility;
use App\Models\Repository;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
        $repository = $this->route('repository');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:1',
                'max:100',
                'regex:/\A[a-zA-Z0-9][a-zA-Z0-9._-]*\z/',
                'not_regex:/\.git\z/i',
                Rule::notIn(['.', '..']),
            ],
            'slug' => [
                'sometimes',
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
                    ->ignore(
                        $repository instanceof Repository
                            ? $repository->getKey()
                            : null,
                    )
                    ->withoutTrashed(),
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],
            'visibility' => [
                'sometimes',
                Rule::enum(RepositoryVisibility::class),
            ],
        ];
    }

    /**
     * Prepare input before validation.
     */
    protected function prepareForValidation(): void
    {
        $prepared = [];

        if ($this->has('name')) {
            $name = trim((string) $this->input('name'));

            $prepared['name'] = $name;
            $prepared['slug'] = Str::slug($name);
        }

        if ($this->has('description')) {
            $description = $this->input('description');

            if (is_string($description)) {
                $description = trim($description);
                $description = $description === '' ? null : $description;
            }

            $prepared['description'] = $description;
        }

        if ($this->has('visibility')) {
            $prepared['visibility'] = $this->input('visibility');
        }

        $this->merge($prepared);
    }
}
