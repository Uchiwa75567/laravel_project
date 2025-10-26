<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^[\+]?[1-9][\d]{0,15}$/', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['sometimes', 'string', 'in:admin,manager,user'],
            'is_active' => ['sometimes', 'boolean'],
            'preferences' => ['nullable', 'array'],
            'preferences.language' => ['nullable', 'string', 'in:fr,en,es,de'],
            'preferences.theme' => ['nullable', 'string', 'in:light,dark'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'phone.regex' => 'Le numéro de téléphone doit être au format international.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.in' => 'Le rôle doit être admin, manager ou user.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'prénom',
            'last_name' => 'nom',
            'email' => 'adresse email',
            'phone' => 'téléphone',
            'password' => 'mot de passe',
            'role' => 'rôle',
            'is_active' => 'statut actif',
            'preferences' => 'préférences',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default role if not provided
        if (!$this->has('role')) {
            $this->merge(['role' => 'user']);
        }

        // Set default is_active if not provided
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        // Generate name from first_name and last_name if not provided
        if (!$this->has('name') && $this->has('first_name') && $this->has('last_name')) {
            $this->merge([
                'name' => trim($this->first_name . ' ' . $this->last_name)
            ]);
        }
    }
}
