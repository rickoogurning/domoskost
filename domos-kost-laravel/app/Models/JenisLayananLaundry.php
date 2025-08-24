<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisLayananLaundry extends Model
{
    use HasFactory;

    protected $table = 'jenis_layanan_laundry';

    protected $fillable = [
        'nama_layanan',
        'harga_per_kg',
        'estimasi_hari',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'harga_per_kg' => 'decimal:2',
        'estimasi_hari' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Order Laundry
     */
    public function orderLaundry(): HasMany
    {
        return $this->hasMany(OrderLaundry::class, 'jenis_layanan_id');
    }

    /**
     * Scope for active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_per_kg, 0, ',', '.') . '/kg';
    }

    /**
     * Calculate price for given weight
     */
    public function calculatePrice(float $berat): float
    {
        return $this->harga_per_kg * $berat;
    }
}
