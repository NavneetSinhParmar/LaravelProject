<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('products', 'short_description')) {
                $table->text('short_description')->nullable()->after('slug');
            }

            if (! Schema::hasColumn('products', 'product_type')) {
                $table->string('product_type')->default('paid')->after('short_description');
            }

            if (! Schema::hasColumn('products', 'discount_price')) {
                $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            }

            if (! Schema::hasColumn('products', 'gallery_images')) {
                $table->json('gallery_images')->nullable()->after('primary_image');
            }

            if (! Schema::hasColumn('products', 'view_count')) {
                $table->unsignedInteger('view_count')->default(0)->after('download_count');
            }

            if (! Schema::hasColumn('products', 'sales_count')) {
                $table->unsignedInteger('sales_count')->default(0)->after('view_count');
            }

            if (! Schema::hasColumn('products', 'seo_title')) {
                $table->string('seo_title')->nullable()->after('seo_tags');
            }

            if (! Schema::hasColumn('products', 'seo_description')) {
                $table->text('seo_description')->nullable()->after('seo_title');
            }

            if (! Schema::hasColumn('products', 'seo_keywords')) {
                $table->text('seo_keywords')->nullable()->after('seo_description');
            }

            if (! Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('seo_keywords');
            }

            if (! Schema::hasColumn('products', 'is_best_seller')) {
                $table->boolean('is_best_seller')->default(false)->after('is_featured');
            }
        });

        Schema::table('product_downloads', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_downloads', 'email')) {
                $table->string('email')->nullable()->after('product_id');
            }

            if (! Schema::hasColumn('product_downloads', 'download_type')) {
                $table->string('download_type')->nullable()->after('email');
            }

            if (! Schema::hasColumn('product_downloads', 'download_count')) {
                $table->unsignedInteger('download_count')->default(1)->after('download_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            foreach ([
                'slug',
                'short_description',
                'product_type',
                'discount_price',
                'gallery_images',
                'view_count',
                'sales_count',
                'seo_title',
                'seo_description',
                'seo_keywords',
                'is_featured',
                'is_best_seller',
            ] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('product_downloads', function (Blueprint $table): void {
            foreach (['email', 'download_type', 'download_count'] as $column) {
                if (Schema::hasColumn('product_downloads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
