<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Drop the existing foreign key constraint if it exists
        Schema::table('passkeys', function (Blueprint $table) {
            // First, drop the existing foreign key constraint
            $table->dropForeign(['authenticatable_id']);
            
            // Modify the column to match the users.id type (unsigned integer)
            $table->unsignedInteger('authenticatable_id')->change();
        });
        
        // Re-add the foreign key constraint with the correct type
        Schema::table('passkeys', function (Blueprint $table) {
            $table->foreign('authenticatable_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop the foreign key constraint
        Schema::table('passkeys', function (Blueprint $table) {
            $table->dropForeign(['authenticatable_id']);
            
            // Change the column back to bigint unsigned
            $table->unsignedBigInteger('authenticatable_id')->change();
        });
        
        // Re-add the original foreign key constraint
        Schema::table('passkeys', function (Blueprint $table) {
            $table->foreign('authenticatable_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
