<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'tagihan_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_bayar',
        'bukti_bayar',
        'status_verifikasi',
        'verifikasi_oleh',
        'tanggal_verifikasi',
        'catatan_verifikasi',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'jumlah_bayar' => 'decimal:2',
        'tanggal_verifikasi' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Tagihan
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * Relationship with Verifier
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikasi_oleh');
    }

    /**
     * Scope for verified payments
     */
    public function scopeVerified($query)
    {
        return $query->where('status_verifikasi', 'Terverifikasi');
    }

    /**
     * Scope for pending verification
     */
    public function scopePending($query)
    {
        return $query->where('status_verifikasi', 'Menunggu');
    }

    /**
     * Scope by method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('metode_bayar', $method);
    }

    /**
     * Check if payment is verified
     */
    public function isVerified(): bool
    {
        return $this->status_verifikasi === 'Terverifikasi';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status_verifikasi === 'Menunggu';
    }

    /**
     * Verify payment
     */
    public function verify(User $user, string $catatan = null): void
    {
        $this->update([
            'status_verifikasi' => 'Terverifikasi',
            'verifikasi_oleh' => $user->id,
            'tanggal_verifikasi' => now(),
            'catatan_verifikasi' => $catatan,
        ]);

        // Update tagihan status
        $this->tagihan->updateStatus();
    }

    /**
     * Reject payment
     */
    public function reject(User $user, string $catatan = null): void
    {
        $this->update([
            'status_verifikasi' => 'Ditolak',
            'verifikasi_oleh' => $user->id,
            'tanggal_verifikasi' => now(),
            'catatan_verifikasi' => $catatan,
        ]);
    }
}
