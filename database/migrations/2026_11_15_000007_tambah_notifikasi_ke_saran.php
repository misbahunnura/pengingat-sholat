<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saran', function (Blueprint $table) {
            // true = admin baru membalas / mengubah status dan pengguna belum membukanya
            $table->boolean('perlu_dilihat')->default(false)->after('balasan');
            $table->index(['user_id', 'perlu_dilihat']);
        });
    }

    public function down(): void
    {
        Schema::table('saran', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'perlu_dilihat']);
            $table->dropColumn('perlu_dilihat');
        });
    }
};
