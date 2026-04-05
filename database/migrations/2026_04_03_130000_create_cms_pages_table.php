<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('page_slug');
            $table->string('section_key');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->json('json_data')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['page_slug', 'section_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
    }
};
