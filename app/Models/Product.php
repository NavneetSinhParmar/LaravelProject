<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'primary_image',
        'download_file',
        'category_id',
        'seo_tags',
        'download_count',
        'image',
        'price',
        'json_data',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'download_count' => 'integer',
            'price' => 'decimal:2',
            'json_data' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PortfolioCategory::class, 'category_id');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(ProductDownload::class);
    }
}
