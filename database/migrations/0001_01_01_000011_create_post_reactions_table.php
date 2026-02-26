<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_reactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20);
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
            $table->index(['post_id', 'type'], 'post_reactions_post_type_index');
            $table->index(['user_id', 'created_at'], 'post_reactions_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_reactions');
    }
};
