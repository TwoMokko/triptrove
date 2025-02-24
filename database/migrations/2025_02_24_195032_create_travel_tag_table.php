<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('travel_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('travel_id'); // Внешний ключ для users
            $table->unsignedBigInteger('tag_id'); // Внешний ключ для roles

            // Создаем внешние ключи
            $table->foreign('travel_id')->references('id')->on('travels')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');

            // Составной первичный ключ
            $table->primary(['travel_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_tag');
    }
};
