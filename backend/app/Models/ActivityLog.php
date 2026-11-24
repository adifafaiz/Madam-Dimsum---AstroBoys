<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'aksi',
        'tabel',
        'data_lama',
        'data_baru',
        'waktu',
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
        'waktu' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

