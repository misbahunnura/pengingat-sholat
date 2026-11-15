<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanSalat extends Model
{
    protected $table = 'catatan_salat';

    protected $fillable = ['user_id', 'tanggal', 'waktu'];
}
