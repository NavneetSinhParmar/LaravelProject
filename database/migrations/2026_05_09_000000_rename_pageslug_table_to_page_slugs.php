<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pageslug') && ! Schema::hasTable('page_slugs')) {
            Schema::rename('pageslug', 'page_slugs');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('page_slugs') && ! Schema::hasTable('pageslug')) {
            Schema::rename('page_slugs', 'pageslug');
        }
    }
};
