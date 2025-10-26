<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Client extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'postal_code',
        'is_active',
        'last_order_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_order_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active clients.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include clients with recent orders.
     */
    public function scopeRecentOrders($query)
    {
        return $query->whereNotNull('last_order_at')
                    ->where('last_order_at', '>=', now()->subDays(30));
    }

    /**
     * Get the client's full address.
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address;
        if ($this->city) $address .= ', ' . $this->city;
        if ($this->country) $address .= ', ' . $this->country;
        if ($this->postal_code) $address .= ' ' . $this->postal_code;

        return $address;
    }
}
