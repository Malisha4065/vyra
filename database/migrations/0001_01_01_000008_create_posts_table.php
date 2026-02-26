<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->timestamp('published_at')->index();
            $table->timestamps();

            $table->index(['user_id', 'published_at'], 'posts_user_published_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
