<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSlug extends Model
{
    protected $table = 'page_slugs';

    protected $fillable = [
        'name',
        'slug',
    ];

    public $timestamps = true;
}
