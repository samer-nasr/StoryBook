<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key');
            $table->text('system_role')->nullable();
            $table->longText('strict_rules')->nullable();
            $table->longText('identity_block')->nullable();
            $table->longText('style_block')->nullable();
            $table->longText('task')->nullable();
            $table->longText('constraints')->nullable();
            $table->longText('output_rules')->nullable();
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_templates');
    }
};
