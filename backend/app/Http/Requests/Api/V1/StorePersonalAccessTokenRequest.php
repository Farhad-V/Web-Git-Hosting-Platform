<?php

namespace App\Http\Requests\Api\V1;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StorePersonalAccessTokenRequest extends FormRequest
{
    /**
     * Determine whether the authenticated user may create a token.
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
            'current_password' => [
                'required',
                'string',
                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail,
                ): void {
                    $user = $this->user();

                    if (
                        $user === null
                        || ! is_string($value)
                        || ! Hash::check($value, $user->password)
                    ) {
                        $fail(
                            'The current password is incorrect.',
                        );
                    }
                },
            ],
            'abilities' => [
                'required',
                'array',
                'min:1',
                'max:2',
            ],
            'abilities.*' => [
                'required',
                'string',
                'distinct',
                Rule::in([
                    'repository:read',
                    'repository:write',
                ]),
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now',
                'before_or_equal:'.now()
                    ->addYear()
                    ->toISOString(),
            ],
        ];
    }

    /**
     * Prepare input before validation.
     */
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');

        if (is_string($name)) {
            $name = trim($name);
        }

        $abilities = $this->input('abilities');

        if (is_array($abilities)) {
            $abilities = array_values(
                array_filter(
                    $abilities,
                    static fn (mixed $ability): bool => is_string($ability),
                ),
            );
        }

        $this->merge([
            'name' => $name,
            'abilities' => $abilities,
        ]);
    }
}
