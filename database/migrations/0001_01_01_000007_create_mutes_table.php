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
        Schema::create('mutes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('muter_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('muted_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();

            // Prevent duplicate mutes
            $table->unique(['muter_id', 'muted_id']);

            // "Who have I muted?" for feed filtering
            $table->index('muter_id', 'mutes_muter_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutes');
    }
};
