<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    use HasFactory;

    /**
     * Explicit table name: the DB migration created `client` (singular).
     */
    protected $table = 'client';

    protected $fillable = [
        'name',        // Clients name
        'logo',        // image file
        'link',        // website url
        'page_slug',   // page location
        'status',      // active status
        'sort_order',   // sort order for display
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'boolean',
    ];
}
