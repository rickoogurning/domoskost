<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTagihan extends Model
{
    use HasFactory;

    protected $table = 'detail_tagihan';

    protected $fillable = [
        'tagihan_id',
        'jenis_tagihan',
        'deskripsi',
        'quantity',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Relationship with Tagihan
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('jenis_tagihan', $type);
    }

    /**
     * Boot method to auto-calculate subtotal
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->subtotal = $model->quantity * $model->harga_satuan;
        });
    }
}
