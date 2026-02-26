<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('url', 2048);
            $table->string('mime_type', 255)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('kind', 20)->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['post_id', 'position'], 'post_media_post_position_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
