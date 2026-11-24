<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'waktu',
        'ukuran_kb',
        'jenis',
        'status',
        'file_path',
    ];

    protected $casts = [
        'waktu' => 'datetime',
        'ukuran_kb' => 'integer',
    ];
}

