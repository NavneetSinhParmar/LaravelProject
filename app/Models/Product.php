<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'json_data',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'json_data' => 'array',
            'status' => 'boolean',
        ];
    }
}
