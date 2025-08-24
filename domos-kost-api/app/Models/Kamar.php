<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kamar extends Model
{
    use HasFactory;

    protected $table = 'kamar';

    protected $fillable = [
        'kode_kamar',
        'lantai',
        'tipe_kamar',
        'tarif_bulanan',
        'fasilitas',
        'status_kamar',
        'foto_kamar',
    ];

    protected $casts = [
        'lantai' => 'integer',
        'tarif_bulanan' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Penghuni
     */
    public function penghuni(): HasMany
    {
        return $this->hasMany(Penghuni::class);
    }

    /**
     * Get current active penghuni
     */
    public function penghuniAktif(): HasOne
    {
        return $this->hasOne(Penghuni::class)->where('status_penghuni', 'Aktif');
    }

    /**
     * Scope for available rooms
     */
    public function scopeAvailable($query)
    {
        return $query->where('status_kamar', 'Tersedia');
    }

    /**
     * Scope for occupied rooms
     */
    public function scopeOccupied($query)
    {
        return $query->where('status_kamar', 'Terisi');
    }

    /**
     * Scope by floor
     */
    public function scopeByFloor($query, $lantai)
    {
        return $query->where('lantai', $lantai);
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, $tipe)
    {
        return $query->where('tipe_kamar', $tipe);
    }

    /**
     * Check if room is available
     */
    public function isAvailable(): bool
    {
        return $this->status_kamar === 'Tersedia';
    }

    /**
     * Check if room is occupied
     */
    public function isOccupied(): bool
    {
        return $this->status_kamar === 'Terisi';
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->tarif_bulanan, 0, ',', '.');
    }
}
