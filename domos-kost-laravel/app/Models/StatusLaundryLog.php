<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusLaundryLog extends Model
{
    use HasFactory;

    protected $table = 'status_laundry_log';

    protected $fillable = [
        'order_laundry_id',
        'status_sebelum',
        'status_sesudah',
        'diubah_oleh',
        'catatan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Relationship with Order Laundry
     */
    public function orderLaundry(): BelongsTo
    {
        return $this->belongsTo(OrderLaundry::class);
    }

    /**
     * Relationship with User who made the change
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }

    /**
     * Boot method to auto-set created_at
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }
}
