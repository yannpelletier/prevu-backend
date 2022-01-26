<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInfosThumbnailTypeAndCompilationJobIdToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('thumbnail_type')->nullable();
            $table->longText('infos')->nullable();
            $table->integer('compilation_job_id')->nullable();
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
            $table->dropColumn('thumbnail_type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('infos');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('compilation_job_id');
        });
    }
}
