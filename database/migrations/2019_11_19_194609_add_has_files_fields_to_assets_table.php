<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasFilesFieldsToAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('extension')->nullable();
            $table->string('file_id')->nullable();
        });

        // For SQLite compatibility in unit tests, we need to drop the column separately.
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('file')->nullable();
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('file_id');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('extension');
        });
    }
}
