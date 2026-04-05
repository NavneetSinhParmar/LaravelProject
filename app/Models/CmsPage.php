<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = [
        'page_slug',
        'section_key',
        'title',
        'subtitle',
        'content',
        'image',
        'link',
        'json_data',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'json_data' => 'array',
            'status' => 'boolean',
        ];
    }
}
