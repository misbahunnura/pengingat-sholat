<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doa', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori')->index();
            $table->text('arab');
            $table->text('latin');
            $table->text('arti');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doa');
    }
};
