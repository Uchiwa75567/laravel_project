<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference',
        'type',
        'montant',
        'devise',
        'description',
        'statut',
        'compte_id',
        'compte_destination_id',
        'date_transaction',
        'metadata',
        'is_archived',
        'archived_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'montant' => 'decimal:2',
        'date_transaction' => 'datetime',
        'metadata' => 'array',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = static::generateReference();
            }
            if (empty($transaction->date_transaction)) {
                $transaction->date_transaction = now();
            }
        });
    }

    /**
     * Generate a unique transaction reference.
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'TXN' . date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Get the compte that owns the transaction.
     */
    public function compte(): BelongsTo
    {
        return $this->belongsTo(Compte::class);
    }

    /**
     * Get the destination compte for transfers.
     */
    public function compteDestination(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'compte_destination_id');
    }

    /**
     * Scope a query to only include transactions of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include transactions with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('statut', $status);
    }

    /**
     * Scope a query to only include transactions within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_transaction', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include credit transactions (deposits, transfers received).
     */
    public function scopeCredits($query)
    {
        return $query->whereIn('type', ['depot', 'virement_recue']);
    }

    /**
     * Scope a query to only include debit transactions (withdrawals, transfers sent).
     */
    public function scopeDebits($query)
    {
        return $query->whereIn('type', ['retrait', 'virement_emis', 'paiement']);
    }

    /**
     * Get the formatted amount with currency.
     */
    public function getFormattedMontantAttribute()
    {
        $sign = in_array($this->type, ['depot', 'virement_recue']) ? '+' : '-';
        return $sign . number_format(abs($this->montant), 2, ',', ' ') . ' ' . $this->devise;
    }

    /**
     * Check if the transaction is a credit (increases balance).
     */
    public function isCredit(): bool
    {
        return in_array($this->type, ['depot', 'virement_recue']);
    }

    /**
     * Check if the transaction is a debit (decreases balance).
     */
    public function isDebit(): bool
    {
        return in_array($this->type, ['retrait', 'virement_emis', 'paiement']);
    }

    /**
     * Check if the transaction is completed.
     */
    public function isCompleted(): bool
    {
        return $this->statut === 'effectuee';
    }

    /**
     * Check if the transaction is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->statut === 'annulee';
    }

    /**
     * Check if the transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->statut === 'en_cours';
    }

    /**
     * Get the client through the compte relationship.
     */
    public function client()
    {
        return $this->hasOneThrough(Client::class, Compte::class, 'id', 'id', 'compte_id', 'client_id');
    }
}
