<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
        'title',
        'slug', // url slug
        'subtitle',
        'content',
        'image',
        'link',
        'page_slug', // page identifier
        'category_id', // category reference
        'json_data', // extra data
        'meta_title', // seo title
        'meta_description', // seo description
        'order', // display order
        'is_featured', // featured flag
        'status', // active status
    ];

    protected $casts = [
        'json_data' => 'array',
        'status' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(PortfolioCategory::class);
    }
}