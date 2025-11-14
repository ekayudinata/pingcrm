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
        Schema::dropIfExists('passkeys');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the passkeys table with the original structure
        Schema::create('passkeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('authenticatable_id')->constrained()->cascadeOnDelete();
            $table->text('name');
            $table->text('credential_id');
            $table->json('data');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }
};
