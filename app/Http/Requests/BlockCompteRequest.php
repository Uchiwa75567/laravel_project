<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlockCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assuming authenticated users can block comptes
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date_debut_blocage' => 'required|date|after_or_equal:now',
            'date_fin_blocage' => 'nullable|date|after:date_debut_blocage',
            'motif_blocage' => 'required|string|max:255',
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
            'date_debut_blocage.required' => 'La date de début de blocage est obligatoire.',
            'date_debut_blocage.date' => 'La date de début de blocage doit être une date valide.',
            'date_debut_blocage.after_or_equal' => 'La date de début de blocage doit être aujourd\'hui ou dans le futur.',
            'date_fin_blocage.date' => 'La date de fin de blocage doit être une date valide.',
            'date_fin_blocage.after' => 'La date de fin de blocage doit être après la date de début.',
            'motif_blocage.required' => 'Le motif de blocage est obligatoire.',
            'motif_blocage.string' => 'Le motif de blocage doit être une chaîne de caractères.',
            'motif_blocage.max' => 'Le motif de blocage ne peut pas dépasser 255 caractères.',
        ];
    }
}
