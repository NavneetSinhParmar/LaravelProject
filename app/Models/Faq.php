<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faqs';

    protected $fillable = [
        'pageslug',
        'category',
        'question',
        'answer',
        'status',
        'order',
        'is_featured',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_featured' => 'boolean',
        'order' => 'integer',
    ];

    public $timestamps = true;
}
