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
        Schema::table('testimonials', function (Blueprint $table) {
            if (!Schema::hasColumn('testimonials', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('testimonials', 'designation')) {
                $table->string('designation')->nullable()->after('name');
            }
            if (!Schema::hasColumn('testimonials', 'company')) {
                $table->string('company')->nullable()->after('designation');
            }
            if (!Schema::hasColumn('testimonials', 'message')) {
                $table->text('message')->nullable()->after('company');
            }
            if (!Schema::hasColumn('testimonials', 'image')) {
                $table->string('image')->nullable()->after('message');
            }
            if (!Schema::hasColumn('testimonials', 'rating')) {
                $table->integer('rating')->default(5)->after('image');
            }
            if (!Schema::hasColumn('testimonials', 'page_slug')) {
                $table->string('page_slug')->nullable()->after('rating');
            }
            if (!Schema::hasColumn('testimonials', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('page_slug');
            }
            if (!Schema::hasColumn('testimonials', 'status')) {
                $table->boolean('status')->default(1)->after('sort_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['name', 'designation', 'company', 'message', 'image', 'rating', 'page_slug', 'sort_order', 'status']);
        });
    }
};
