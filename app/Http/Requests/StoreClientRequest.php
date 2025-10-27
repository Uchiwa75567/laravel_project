<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du client est obligatoire.',
            'email.required' => 'L\'email du client est obligatoire.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé par un autre client.',
            'phone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            'address.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
            'city.max' => 'La ville ne peut pas dépasser 100 caractères.',
            'country.max' => 'Le pays ne peut pas dépasser 100 caractères.',
            'postal_code.max' => 'Le code postal ne peut pas dépasser 20 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom du client',
            'email' => 'email',
            'phone' => 'téléphone',
            'address' => 'adresse',
            'city' => 'ville',
            'country' => 'pays',
            'postal_code' => 'code postal',
            'is_active' => 'statut actif',
        ];
    }
}
