<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug', //  dropdown choose page sulg 
        'short_description', 
        'description',
        'product_type', // free and paid types choose only default paid
        'price', // 0 for free products and >0 for paid products
        'discount_price', // optional discounted price for paid products
        'status', // active/inactive default active
        'category_id', // dropdown choose category
        'primary_image', // main image for the product required
        'gallery_images', // optional multiple images for the product
        'download_file', // required file for download (zip, pdf, etc.)
        'download_count', // track how many times the product has been downloaded dynamically updated added not in form 
        'view_count', // track how many times the product page has been viewed optional
        'sales_count', // track how many times the product has been purchased optional
        'seo_title', // optional SEO title for better search engine ranking
        'seo_description', // optional SEO description for better search engine ranking
        'seo_keywords', // optional SEO keywords for better search engine ranking
        'is_featured', // boolean to mark product as featured for special display on the website
        'is_best_seller', // boolean to mark product as best seller for special display on the website
        'json_data', // optional JSON field for any additional data or specifications related to the product, allowing for flexible storage of extra information without needing to alter the database schema. This can include things like technical specifications, user manuals, or any other relevant data that doesn't fit into the predefined columns.
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'download_count' => 'integer',
            'view_count' => 'integer',
            'sales_count' => 'integer',
            'is_featured' => 'boolean',
            'is_best_seller' => 'boolean',
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'json_data' => 'array',
            'gallery_images' => 'array',
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
