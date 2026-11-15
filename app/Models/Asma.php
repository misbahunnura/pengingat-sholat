<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asma extends Model
{
    protected $table = 'asma';

    protected $fillable = ['nomor', 'arab', 'latin', 'arti'];
}
