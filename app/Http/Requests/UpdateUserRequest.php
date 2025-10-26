<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'phone' => ['nullable', 'string', 'regex:/^[\+]?[1-9][\d]{0,15}$/', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
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
        // Only update name if first_name or last_name are being updated
        if (($this->has('first_name') || $this->has('last_name')) && !$this->has('name')) {
            $firstName = $this->first_name ?? $this->route('user')?->first_name;
            $lastName = $this->last_name ?? $this->route('user')?->last_name;

            if ($firstName && $lastName) {
                $this->merge([
                    'name' => trim($firstName . ' ' . $lastName)
                ]);
            }
        }

        // Remove password fields if empty
        if ($this->password === '') {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }
}
