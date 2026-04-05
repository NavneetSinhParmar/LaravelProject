<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
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
