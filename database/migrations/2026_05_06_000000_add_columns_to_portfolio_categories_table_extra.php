<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('portfolio_categories', 'page_slug')) {
                $table->string('page_slug')->nullable()->after('id');
            }

            if (! Schema::hasColumn('portfolio_categories', 'link')) {
                $table->string('link')->nullable()->after('name');
            }

            if (! Schema::hasColumn('portfolio_categories', 'logo')) {
                $table->string('logo')->nullable()->after('link');
            }

            if (! Schema::hasColumn('portfolio_categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('logo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_categories', function (Blueprint $table): void {
            if (Schema::hasColumn('portfolio_categories', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
            if (Schema::hasColumn('portfolio_categories', 'logo')) {
                $table->dropColumn('logo');
            }
            if (Schema::hasColumn('portfolio_categories', 'link')) {
                $table->dropColumn('link');
            }
            if (Schema::hasColumn('portfolio_categories', 'page_slug')) {
                $table->dropColumn('page_slug');
            }
        });
    }
};
