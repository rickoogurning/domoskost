<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tagihan';

    protected $fillable = [
        'penghuni_id',
        'periode_bulan',
        'periode_tahun',
        'tanggal_terbit',
        'tanggal_jatuh_tempo',
        'total_tagihan',
        'denda',
        'status_tagihan',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'tanggal_terbit' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'total_tagihan' => 'decimal:2',
        'denda' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['periode_string', 'sisa_hari', 'total_dibayar', 'sisa_tagihan'];

    /**
     * Relationship with Penghuni
     */
    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(Penghuni::class);
    }

    /**
     * Relationship with Detail Tagihan
     */
    public function detailTagihan(): HasMany
    {
        return $this->hasMany(DetailTagihan::class);
    }

    /**
     * Relationship with Pembayaran
     */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Relationship with Created By User
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get period string
     */
    public function getPeriodeStringAttribute(): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $bulan[$this->periode_bulan] . ' ' . $this->periode_tahun;
    }

    /**
     * Get remaining days until due date
     */
    public function getSisaHariAttribute(): int
    {
        return Carbon::parse($this->tanggal_jatuh_tempo)->diffInDays(now(), false);
    }

    /**
     * Get total paid amount
     */
    public function getTotalDibayarAttribute(): float
    {
        return $this->pembayaran()
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('jumlah_bayar');
    }

    /**
     * Get remaining bill amount
     */
    public function getSisaTagihanAttribute(): float
    {
        return $this->total_tagihan - $this->total_dibayar;
    }

    /**
     * Scope for unpaid bills
     */
    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status_tagihan', ['Lunas']);
    }

    /**
     * Scope for overdue bills
     */
    public function scopeOverdue($query)
    {
        return $query->where('tanggal_jatuh_tempo', '<', now())
                    ->whereNotIn('status_tagihan', ['Lunas']);
    }

    /**
     * Scope by period
     */
    public function scopeByPeriod($query, $bulan, $tahun)
    {
        return $query->where('periode_bulan', $bulan)
                    ->where('periode_tahun', $tahun);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_tagihan', $status);
    }

    /**
     * Check if bill is overdue
     */
    public function isOverdue(): bool
    {
        return $this->tanggal_jatuh_tempo < now() && $this->status_tagihan !== 'Lunas';
    }

    /**
     * Check if bill is paid
     */
    public function isPaid(): bool
    {
        return $this->status_tagihan === 'Lunas';
    }

    /**
     * Calculate penalty for overdue
     */
    public function calculatePenalty(): float
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $overdueDays = abs($this->sisa_hari);
        $dailyPenalty = 10000; // Rp 10,000 per day
        $maxDays = 30; // Maximum 30 days penalty

        return min($overdueDays, $maxDays) * $dailyPenalty;
    }

    /**
     * Update status based on payments
     */
    public function updateStatus(): void
    {
        $totalPaid = $this->total_dibayar;
        
        if ($totalPaid >= $this->total_tagihan) {
            $this->status_tagihan = 'Lunas';
        } elseif ($totalPaid > 0) {
            $this->status_tagihan = 'Dibayar Sebagian';
        } elseif ($this->isOverdue()) {
            $this->status_tagihan = 'Terlambat';
        } else {
            $this->status_tagihan = 'Belum Dibayar';
        }
        
        // Update penalty if overdue
        if ($this->isOverdue()) {
            $this->denda = $this->calculatePenalty();
        }
        
        $this->save();
    }
}
