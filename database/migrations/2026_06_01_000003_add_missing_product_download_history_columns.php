<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_downloads', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_downloads', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index();
            }

            if (! Schema::hasColumn('product_downloads', 'user_agent')) {
                $table->string('user_agent')->nullable();
            }

            if (! Schema::hasColumn('product_downloads', 'fingerprint')) {
                $table->string('fingerprint')->nullable();
            }

            if (! Schema::hasColumn('product_downloads', 'product_type')) {
                $table->string('product_type')->nullable();
            }

            if (! Schema::hasColumn('product_downloads', 'action_type')) {
                $table->string('action_type')->nullable();
            }

            if (! Schema::hasColumn('product_downloads', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_downloads', function (Blueprint $table): void {
            if (Schema::hasColumn('product_downloads', 'user_id')) {
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('product_downloads', 'user_agent')) {
                $table->dropColumn('user_agent');
            }

            if (Schema::hasColumn('product_downloads', 'fingerprint')) {
                $table->dropColumn('fingerprint');
            }

            if (Schema::hasColumn('product_downloads', 'product_type')) {
                $table->dropColumn('product_type');
            }

            if (Schema::hasColumn('product_downloads', 'action_type')) {
                $table->dropColumn('action_type');
            }

            if (Schema::hasColumn('product_downloads', 'created_at')) {
                $table->dropTimestamps();
            }
        });
    }
};
