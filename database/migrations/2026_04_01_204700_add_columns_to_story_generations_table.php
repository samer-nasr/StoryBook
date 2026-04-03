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
        Schema::table('story_generations', function (Blueprint $table) {
            $table->string('batch_id')->nullable()->after('output_path');
            $table->string('character_image_path')->nullable()->after('batch_id');
            $table->string('pdf_path')->nullable()->after('character_image_path');
            $table->text('prompt')->nullable()->after('pdf_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('story_generations', function (Blueprint $table) {
            $table->dropColumn(['batch_id', 'character_image_path', 'pdf_path', 'prompt']);
        });
    }
};
