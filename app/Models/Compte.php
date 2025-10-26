<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compte extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero',
        'type',
        'solde',
        'devise',
        'is_active',
        'client_id',
        'date_ouverture',
        'last_transaction_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'solde' => 'decimal:2',
        'is_active' => 'boolean',
        'date_ouverture' => 'datetime',
        'last_transaction_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($compte) {
            if (empty($compte->numero)) {
                $compte->numero = static::generateNumero();
            }
        });
    }

    /**
     * Generate a unique account number.
     */
    public static function generateNumero(): string
    {
        do {
            $numero = 'ACC' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('numero', $numero)->exists());

        return $numero;
    }

    /**
     * Get the client that owns the compte.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope a query to only include active comptes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include comptes of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include comptes with positive balance.
     */
    public function scopeWithBalance($query)
    {
        return $query->where('solde', '>', 0);
    }

    /**
     * Get the formatted balance with currency.
     */
    public function getFormattedSoldeAttribute()
    {
        return number_format($this->solde, 2, ',', ' ') . ' ' . $this->devise;
    }

    /**
     * Check if the account has sufficient balance.
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->solde >= $amount;
    }

    /**
     * Update the last transaction timestamp.
     */
    public function updateLastTransaction()
    {
        $this->update(['last_transaction_at' => now()]);
    }
}
