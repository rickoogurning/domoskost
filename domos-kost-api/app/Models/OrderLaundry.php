<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class OrderLaundry extends Model
{
    use HasFactory;

    protected $table = 'order_laundry';

    protected $fillable = [
        'kode_order',
        'penghuni_id',
        'jenis_layanan_id',
        'petugas_terima_id',
        'petugas_selesai_id',
        'tanggal_terima',
        'tanggal_estimasi_selesai',
        'tanggal_selesai',
        'berat_kg',
        'total_biaya',
        'status_order',
        'status_bayar',
        'catatan_order',
        'foto_bukti_terima',
        'foto_bukti_selesai',
    ];

    protected $casts = [
        'tanggal_terima' => 'datetime',
        'tanggal_estimasi_selesai' => 'date',
        'tanggal_selesai' => 'datetime',
        'berat_kg' => 'decimal:2',
        'total_biaya' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['progress_percentage', 'is_overdue'];

    /**
     * Relationship with Penghuni
     */
    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(Penghuni::class);
    }

    /**
     * Relationship with Jenis Layanan
     */
    public function jenisLayanan(): BelongsTo
    {
        return $this->belongsTo(JenisLayananLaundry::class, 'jenis_layanan_id');
    }

    /**
     * Relationship with Petugas Terima
     */
    public function petugasTerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_terima_id');
    }

    /**
     * Relationship with Petugas Selesai
     */
    public function petugasSelesai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_selesai_id');
    }

    /**
     * Relationship with Status Log
     */
    public function statusLog(): HasMany
    {
        return $this->hasMany(StatusLaundryLog::class);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        $statusProgress = [
            'Diterima' => 20,
            'Dicuci' => 40,
            'Dikeringkan' => 60,
            'Disetrika' => 80,
            'Siap Diambil' => 90,
            'Selesai' => 100,
            'Dibatalkan' => 0,
        ];

        return $statusProgress[$this->status_order] ?? 0;
    }

    /**
     * Check if order is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->tanggal_estimasi_selesai || $this->status_order === 'Selesai') {
            return false;
        }

        return Carbon::parse($this->tanggal_estimasi_selesai)->isPast();
    }

    /**
     * Scope for active orders
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status_order', ['Selesai', 'Dibatalkan']);
    }

    /**
     * Scope for completed orders
     */
    public function scopeCompleted($query)
    {
        return $query->where('status_order', 'Selesai');
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_order', $status);
    }

    /**
     * Scope for overdue orders
     */
    public function scopeOverdue($query)
    {
        return $query->where('tanggal_estimasi_selesai', '<', now())
                    ->whereNotIn('status_order', ['Selesai', 'Dibatalkan']);
    }

    /**
     * Scope for today's orders
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_terima', today());
    }

    /**
     * Generate unique order code
     */
    public static function generateOrderCode(): string
    {
        $prefix = 'LD-' . date('Ym') . '-';
        $lastOrder = static::where('kode_order', 'like', $prefix . '%')
                          ->orderByDesc('id')
                          ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->kode_order, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Update status with logging
     */
    public function updateStatus(string $newStatus, User $user, string $catatan = null): void
    {
        $oldStatus = $this->status_order;
        
        $this->update([
            'status_order' => $newStatus,
            'tanggal_selesai' => $newStatus === 'Selesai' ? now() : null,
            'petugas_selesai_id' => $newStatus === 'Selesai' ? $user->id : $this->petugas_selesai_id,
        ]);

        // Log status change
        $this->statusLog()->create([
            'status_sebelum' => $oldStatus,
            'status_sesudah' => $newStatus,
            'diubah_oleh' => $user->id,
            'catatan' => $catatan,
        ]);

        // Create notification for penghuni when ready for pickup
        if ($newStatus === 'Siap Diambil') {
            $this->penghuni->user->notifikasi()->create([
                'judul' => 'Laundry Siap Diambil',
                'isi_notifikasi' => "Cucian Anda dengan kode {$this->kode_order} sudah selesai dan siap diambil.",
                'tipe_notifikasi' => 'Laundry',
                'link_terkait' => "/penghuni/laundry/{$this->id}",
            ]);
        }
    }

    /**
     * Check if order can be updated to next status
     */
    public function canUpdateToNextStatus(): bool
    {
        return !in_array($this->status_order, ['Selesai', 'Dibatalkan']);
    }

    /**
     * Get next possible status
     */
    public function getNextStatus(): ?string
    {
        $statusFlow = [
            'Diterima' => 'Dicuci',
            'Dicuci' => 'Dikeringkan',
            'Dikeringkan' => 'Disetrika',
            'Disetrika' => 'Siap Diambil',
            'Siap Diambil' => 'Selesai',
        ];

        return $statusFlow[$this->status_order] ?? null;
    }

    /**
     * Boot method to auto-generate order code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->kode_order) {
                $model->kode_order = static::generateOrderCode();
            }
        });
    }
}
