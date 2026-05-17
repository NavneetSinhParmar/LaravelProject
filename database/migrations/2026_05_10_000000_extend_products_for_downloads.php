<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'primary_image')) {
                $table->string('primary_image')->nullable()->after('description');
            }
            if (! Schema::hasColumn('products', 'download_file')) {
                $table->string('download_file')->nullable()->after('primary_image');
            }
            if (! Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('download_file')
                    ->constrained('portfolio_categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('products', 'seo_tags')) {
                $table->text('seo_tags')->nullable()->after('category_id');
            }
            if (! Schema::hasColumn('products', 'download_count')) {
                $table->unsignedInteger('download_count')->default(0)->after('seo_tags');
            }
        });

        if (Schema::hasColumn('products', 'image') && Schema::hasColumn('products', 'primary_image')) {
            \Illuminate\Support\Facades\DB::table('products')
                ->whereNull('primary_image')
                ->whereNotNull('image')
                ->update(['primary_image' => \Illuminate\Support\Facades\DB::raw('image')]);
        }

        Schema::create('product_downloads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->timestamp('downloaded_at');
            $table->index(['product_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_downloads');

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
            foreach (['primary_image', 'download_file', 'seo_tags', 'download_count'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
