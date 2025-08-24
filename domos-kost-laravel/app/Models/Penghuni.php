<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Penghuni extends Model
{
    use HasFactory;

    protected $table = 'penghuni';

    protected $fillable = [
        'user_id',
        'kamar_id',
        'no_ktp',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'pekerjaan',
        'nama_kontak_darurat',
        'telp_kontak_darurat',
        'tanggal_masuk',
        'tanggal_keluar',
        'status_penghuni',
        'foto_ktp',
        'catatan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['umur', 'lama_tinggal'];

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Kamar
     */
    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    /**
     * Relationship with Tagihan
     */
    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    /**
     * Relationship with Order Laundry
     */
    public function orderLaundry(): HasMany
    {
        return $this->hasMany(OrderLaundry::class);
    }

    /**
     * Get current age
     */
    public function getUmurAttribute(): ?int
    {
        if (!$this->tanggal_lahir) {
            return null;
        }
        return Carbon::parse($this->tanggal_lahir)->age;
    }

    /**
     * Get duration of stay
     */
    public function getLamaTinggalAttribute(): ?string
    {
        if (!$this->tanggal_masuk) {
            return null;
        }
        
        $endDate = $this->tanggal_keluar ?? now();
        $diff = Carbon::parse($this->tanggal_masuk)->diff($endDate);
        
        $years = $diff->y;
        $months = $diff->m;
        $days = $diff->d;
        
        $result = [];
        if ($years > 0) $result[] = $years . ' tahun';
        if ($months > 0) $result[] = $months . ' bulan';
        if ($days > 0 && $years == 0) $result[] = $days . ' hari';
        
        return empty($result) ? '0 hari' : implode(' ', $result);
    }

    /**
     * Scope for active penghuni
     */
    public function scopeActive($query)
    {
        return $query->where('status_penghuni', 'Aktif');
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_penghuni', $status);
    }

    /**
     * Scope by gender
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('jenis_kelamin', $gender);
    }

    /**
     * Get unpaid bills count
     */
    public function getUnpaidBillsCountAttribute(): int
    {
        return $this->tagihan()
            ->whereNotIn('status_tagihan', ['Lunas'])
            ->count();
    }

    /**
     * Get active laundry orders count
     */
    public function getActiveLaundryCountAttribute(): int
    {
        return $this->orderLaundry()
            ->whereNotIn('status_order', ['Selesai', 'Dibatalkan'])
            ->count();
    }

    /**
     * Check if penghuni is active
     */
    public function isActive(): bool
    {
        return $this->status_penghuni === 'Aktif';
    }
}
