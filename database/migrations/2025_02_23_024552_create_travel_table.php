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
        Schema::create('travels', function (Blueprint $table) {
            $table->id();
            $table->string('place');
            $table->string('when');
            $table->string('amount')->nullable();
            $table->string('mode_of_transport');
            $table->text('accommodation');
            $table->text('advice');
            $table->text('entertainment');
            $table->text('general_impression');
            $table->string('cover')->nullable();
            $table->timestamp('cover_updated_at')->nullable();
            $table->integer('order');
            $table->boolean('published')->default(false);
            $table->unsignedBigInteger('user_id'); // Внешний ключ
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps(); // created_at и updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel');
    }
};
