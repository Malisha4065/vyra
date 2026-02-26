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
        Schema::create('follow_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('requester_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('requestee_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending | accepted | rejected
            $table->timestamps();

            // One pending request at a time between two users
            $table->unique(['requester_id', 'requestee_id']);

            // Fast lookup of pending requests for a user
            $table->index(['requestee_id', 'status'], 'follow_requests_requestee_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_requests');
    }
};
