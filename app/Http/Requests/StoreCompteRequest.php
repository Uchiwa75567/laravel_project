<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string|in:epargne,cheque',
            'solde' => 'nullable|numeric|min:0|max:999999.99',
            'devise' => 'required|string|size:3|in:EUR,USD,GBP,CAD',
            'is_active' => 'nullable|boolean',
            'client_id' => 'required|uuid|exists:clients,id',
            'date_ouverture' => 'nullable|date|before_or_equal:today',
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
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être : epargne ou cheque.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde ne peut pas être négatif.',
            'solde.max' => 'Le solde ne peut pas dépasser 999 999,99 €.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.size' => 'La devise doit contenir exactement 3 caractères.',
            'devise.in' => 'La devise doit être : EUR, USD, GBP ou CAD.',
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'date_ouverture.date' => 'La date d\'ouverture doit être une date valide.',
            'date_ouverture.before_or_equal' => 'La date d\'ouverture ne peut pas être dans le futur.',
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
            'type' => 'type de compte',
            'solde' => 'solde',
            'devise' => 'devise',
            'is_active' => 'statut actif',
            'client_id' => 'client',
            'date_ouverture' => 'date d\'ouverture',
        ];
    }
}
