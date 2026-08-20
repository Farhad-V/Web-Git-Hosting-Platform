<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine whether the request may be made.
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
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'username' => [
                'required',
                'string',
                'min:3',
                'max:39',
                'regex:/\A[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\z/',
                Rule::notIn([
                    'admin',
                    'api',
                    'assets',
                    'auth',
                    'login',
                    'register',
                    'repositories',
                    'settings',
                ]),
                'unique:users,username',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    /**
     * Normalize registration input before validation.
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

        if ($this->has('username')) {
            $this->merge([
                'username' => mb_strtolower(
                    trim(
                        (string) $this->input('username'),
                    ),
                ),
            ]);
        }

        if ($this->has('email')) {
            $this->merge([
                'email' => mb_strtolower(
                    trim(
                        (string) $this->input('email'),
                    ),
                ),
            ]);
        }
    }
}