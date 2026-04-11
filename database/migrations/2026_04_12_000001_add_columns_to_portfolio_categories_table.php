<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('portfolio_categories', 'name')) {
                $table->string('name')->after('id');
            }
            if (! Schema::hasColumn('portfolio_categories', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (! Schema::hasColumn('portfolio_categories', 'status')) {
                $table->boolean('status')->default(true)->after('slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_categories', function (Blueprint $table): void {
            $table->dropColumn(['name', 'slug', 'status']);
        });
    }
};
