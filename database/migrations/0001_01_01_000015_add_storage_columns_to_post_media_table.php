<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_media', function (Blueprint $table) {
            $table->string('disk', 255)->nullable()->after('url');
            $table->string('path', 2048)->nullable()->after('disk');
            $table->string('original_name', 255)->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('post_media', function (Blueprint $table) {
            $table->dropColumn(['disk', 'path', 'original_name']);
        });
    }
};
