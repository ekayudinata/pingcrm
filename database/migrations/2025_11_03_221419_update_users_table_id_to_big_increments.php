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
        // First, drop the foreign key constraint on account_id
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
        
        // Change the id column to bigIncrements
        Schema::table('users', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });
        
        // Re-add the foreign key constraint with the correct type
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('account_id')
                  ->references('id')
                  ->on('accounts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop the foreign key constraint on account_id
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
        
        // Change the id column back to increments
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('id')->change();
        });
        
        // Re-add the original foreign key constraint
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('account_id')
                  ->references('id')
                  ->on('accounts')
                  ->onDelete('cascade');
        });
    }
};
