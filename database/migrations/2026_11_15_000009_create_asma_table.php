<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asma', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('nomor')->unique();
            $table->string('arab', 100);
            $table->string('latin', 100);
            $table->string('arti', 200);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asma');
    }
};
