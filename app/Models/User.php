<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'lokasi'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'lokasi' => 'array',
        ];
    }

    public function favorit(): BelongsToMany
    {
        return $this->belongsToMany(Doa::class, 'favorit', 'user_id', 'doa_id')->withTimestamps();
    }

    public function catatan(): HasMany
    {
        return $this->hasMany(CatatanSalat::class);
    }
}
