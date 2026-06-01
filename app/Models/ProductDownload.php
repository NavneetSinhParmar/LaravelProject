<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDownload extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'fingerprint',
        'product_type',
        'action_type',
        'download_type',
        'download_count',
        'downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
