<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonials extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',        // client name
        'designation', // job role
        'company',     // company name
        'message',     // review text
        'image',       // profile image
        'rating',      // star rating
        'page_slug',   // page location
        'sort_order',       // display order
        'status',      // active status
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'boolean',
        'rating' => 'integer',
    ];
}
