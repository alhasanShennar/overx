<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'order',
        'number',
        'title',
        'tagline',
        'card_image',
        'hero_image',
        'overview_title',
        'overview_description',
        'overview_image',
        'process_title',
        'process_description',
        'steps',
        'faqs',
        'is_active',
    ];

    protected $casts = [
        'steps'     => 'array',
        'faqs'      => 'array',
        'is_active' => 'boolean',
    ];
}
