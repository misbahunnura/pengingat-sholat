<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan_salat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('waktu', 10);
            $table->timestamps();
            $table->unique(['user_id', 'tanggal', 'waktu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_salat');
    }
};
