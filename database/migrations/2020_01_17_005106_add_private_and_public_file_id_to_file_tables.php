<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrivateAndPublicFileIdToFileTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('file_id', 'public_file_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('private_file_id')->nullable();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('file_id', 'private_file_id');
        });

        Schema::table('assets', function(Blueprint $table){
            $table->renameColumn('file_id', 'public_file_id');
        });

        Schema::table('watermarks', function(Blueprint $table){
            $table->renameColumn('file_id', 'public_file_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('private_file_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('public_file_id', 'file_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('private_file_id', 'file_id');
        });

        Schema::table('assets', function(Blueprint $table){
            $table->renameColumn('public_file_id', 'file_id');
        });

        Schema::table('watermarks', function(Blueprint $table){
            $table->renameColumn('public_file_id', 'file_id');
        });
}
}
