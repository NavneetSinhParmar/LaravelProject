<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('client', function (Blueprint $table) {
            if (!Schema::hasColumn('client', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('client', 'logo')) {
                $table->string('logo')->nullable()->after('name');
            }
            if (!Schema::hasColumn('client', 'link')) {
                $table->string('link')->nullable()->after('logo');
            }
            if (!Schema::hasColumn('client', 'page_slug')) {
                $table->string('page_slug')->nullable()->after('link');
            }
            if (!Schema::hasColumn('client', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('page_slug');
            }
            if (!Schema::hasColumn('client', 'status')) {
                $table->boolean('status')->default(1)->after('sort_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->dropColumn(['name', 'logo', 'link', 'page_slug', 'sort_order', 'status']);
        });
    }
};
