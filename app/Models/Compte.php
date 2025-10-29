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
        'devise',
        'is_active',
        'client_id',
        'date_ouverture',
        'last_transaction_at',
        'date_debut_blocage',
        'date_fin_blocage',
        'motif_blocage',
        'is_archived',
        'archived_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'date_ouverture' => 'datetime',
        'last_transaction_at' => 'datetime',
        'date_debut_blocage' => 'datetime',
        'date_fin_blocage' => 'datetime',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
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

        // Archive the compte when it's soft deleted
        static::deleting(function ($compte) {
            $compte->archive();
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

    /**
     * Scope a query to only include archived comptes.
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope a query to only include non-archived comptes.
     */
    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope a query to only include blocked comptes.
     */
    public function scopeBlocked($query)
    {
        return $query->whereNotNull('date_debut_blocage')
                    ->where('date_debut_blocage', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('date_fin_blocage')
                          ->orWhere('date_fin_blocage', '>', now());
                    });
    }

    /**
     * Check if the compte is currently blocked.
     */
    public function isBlocked(): bool
    {
        $blocked = $this->date_debut_blocage &&
                   $this->date_debut_blocage->isPast() &&
                   (!$this->date_fin_blocage || $this->date_fin_blocage->isFuture());

        // If blocked, automatically archive
        if ($blocked && !$this->is_archived) {
            $this->archive();
        }

        return $blocked;
    }

    /**
     * Archive the compte and its transactions.
     */
    public function archive()
    {
        $this->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        // Archive related transactions
        $this->transactions()->update(['is_archived' => true, 'archived_at' => now()]);
    }

    /**
     * Unarchive the compte and its transactions.
     */
    public function unarchive()
    {
        $this->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);

        // Unarchive related transactions
        $this->transactions()->update(['is_archived' => false, 'archived_at' => null]);
    }

    /**
     * Get the transactions for the compte.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the formatted balance with currency.
     */

    /**
     * Check if the account has sufficient balance.
     */

    /**
     * Update the last transaction timestamp.
     */
    public function updateLastTransaction()
    {
        $this->update(['last_transaction_at' => now()]);
    }
}
