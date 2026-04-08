<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'file_path',
    ];

    public function storyGenerations()
    {
        return $this->hasMany(StoryGeneration::class, 'template_id');
    }
}
