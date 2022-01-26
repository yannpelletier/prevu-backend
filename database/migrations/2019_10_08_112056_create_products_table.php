<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('extension')->nullable();
            $table->string('file_id')->nullable();
            $table->string('custom_thumbnail_id')->nullable();
            $table->string('demo_id')->nullable();
            $table->integer('user_id');

            $table->longText('filters')->nullable();

            $table->string('name')->nullable();
            $table->mediumText('description')->nullable();
            $table->string('slug')->nullable();
            $table->integer('price');
            $table->string('currency', 3)->nullable();

            $table->integer('views');
            $table->integer('add_to_carts');
            $table->integer('sales');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
