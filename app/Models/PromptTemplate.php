<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'system_role',
        'strict_rules',
        'identity_block',
        'style_block',
        'task',
        'constraints',
        'output_rules',
        'version',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
