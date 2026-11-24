<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kategori',
        'satuan',
        'harga',
        'stok',
        'stok_minimum',
        'supplier_id',
        'is_active',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * BR-03: Status stok dihitung, tidak disimpan:
     * Habis jika stok = 0, Menipis jika stok < stok_minimum, Aman jika stok >= stok_minimum.
     */
    public function getStatusStokAttribute(): string
    {
        if ($this->stok <= 0) {
            return 'Habis';
        }
        if ($this->stok < $this->stok_minimum) {
            return 'Menipis';
        }
        return 'Aman';
    }

    /**
     * BR-04: Jumlah restock / Perlu Diproduksi = 2 x stok_minimum (hanya untuk Menipis atau Habis)
     */
    public function getPerluDiproduksiAttribute(): int
    {
        if ($this->stok < $this->stok_minimum) {
            return 2 * $this->stok_minimum;
        }
        return 0;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}

