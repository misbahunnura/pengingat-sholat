<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dzikir', function (Blueprint $table) {
            $table->id();
            $table->string('waktu', 10)->index(); // pagi | petang | keduanya
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->string('judul', 150);
            $table->text('arab');
            $table->text('latin');
            $table->text('arti');
            $table->unsignedSmallInteger('jumlah')->default(1);
            $table->string('sumber', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dzikir');
    }
};
