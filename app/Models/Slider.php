<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_slug',
        'section_key',
        'title',
        'subtitle',
        'description',
        'html_content',
        'image',
        'link',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
