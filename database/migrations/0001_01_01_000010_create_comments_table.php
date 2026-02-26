<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('parent_comment_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['post_id', 'created_at'], 'comments_post_created_index');
            $table->index(['user_id', 'created_at'], 'comments_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
