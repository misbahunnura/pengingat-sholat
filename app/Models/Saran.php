<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Saran extends Model
{
    protected $table = 'saran';

    // status & balasan hanya diubah admin lewat controller admin (bukan dari formulir pengguna)
    protected $fillable = ['user_id', 'kategori', 'isi'];

    public const KATEGORI = ['Fitur baru', 'Perbaikan', 'Tampilan', 'Lainnya'];

    public const STATUS = [
        'baru' => 'Baru',
        'diproses' => 'Sedang dikerjakan',
        'selesai' => 'Selesai',
        'ditolak' => 'Tidak dikerjakan',
    ];

    protected function casts(): array
    {
        return ['perlu_dilihat' => 'boolean'];
    }

    /**
     * Angka pada lencana notifikasi.
     * Admin: jumlah saran berstatus "baru" yang belum dibalas. Pengguna: jumlah saran miliknya yang baru dibalas/diubah admin.
     */
    public static function lencana(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return $user->is_admin
            ? static::where('status', 'baru')->whereNull('balasan')->count()
            : static::where('user_id', $user->id)->where('perlu_dilihat', true)->count();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
