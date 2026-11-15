<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dzikir extends Model
{
    protected $table = 'dzikir';

    protected $fillable = ['waktu', 'urutan', 'judul', 'arab', 'latin', 'arti', 'jumlah', 'sumber'];

    public const WAKTU = [
        'pagi' => 'Pagi',
        'petang' => 'Petang',
        'keduanya' => 'Pagi & petang',
    ];
}
