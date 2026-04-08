<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StoryGeneration extends Model
{
    use HasFactory;

    protected $table = 'story_generations';

    protected $fillable = [
        'name',
        'status',
        'total_pages',
        'processed_pages',
        'output_path',
        'batch_id',
        'character_image_path',
        'pdf_path',
        'prompt',
        'template_id',
    ];

    public function template()
    {
        return $this->belongsTo(StoryTemplate::class, 'template_id');
    }

    /**
     * Atomically increment processed_pages counter.
     * Safe for concurrent updates from multiple queue workers.
     */
    public function incrementProcessedPages(): void
    {
        DB::table($this->table)
            ->where('id', $this->id)
            ->increment('processed_pages');
    }
}
