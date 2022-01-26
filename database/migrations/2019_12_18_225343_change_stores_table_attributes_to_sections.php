<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStoresTableAttributesToSections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('attributes');
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->longText('root_sections')->nullable();
            $table->longText('custom_sections')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->longText('attributes')->nullable();
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('root_sections');
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('custom_sections');
        });
    }
}
