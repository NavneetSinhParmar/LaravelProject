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
        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->string('slug')->nullable()->after('title');
            $table->string('subtitle')->nullable()->after('slug');
            $table->text('content')->nullable()->after('subtitle');
            $table->string('image')->nullable()->after('content');
            $table->string('link')->nullable()->after('image');
            $table->string('page_slug')->nullable()->after('link');
            $table->unsignedBigInteger('category_id')->nullable()->after('page_slug');
            $table->json('json_data')->nullable()->after('category_id');
            $table->string('meta_title')->nullable()->after('json_data');
            $table->string('meta_description')->nullable()->after('meta_title');
            $table->integer('order')->default(0)->after('meta_description');
            $table->boolean('is_featured')->default(false)->after('order');
            $table->boolean('status')->default(true)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn([
                'title','slug','subtitle','content','image','link','page_slug','category_id','json_data','meta_title','meta_description','order','is_featured','status'
            ]);
        });
    }
};
