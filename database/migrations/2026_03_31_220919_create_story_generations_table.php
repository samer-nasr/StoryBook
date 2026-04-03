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
        Schema::create('story_generations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->integer('total_pages')->default(0);
            $table->integer('processed_pages')->default(0);
            $table->string('output_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_generations');
    }
};
