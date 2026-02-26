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
        Schema::create('follows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('follower_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('followee_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();

            // Prevent duplicate follows
            $table->unique(['follower_id', 'followee_id']);

            // Efficiently list a user's followers
            $table->index('followee_id', 'follows_followee_id_index');

            // Efficiently list who a user follows
            $table->index('follower_id', 'follows_follower_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
