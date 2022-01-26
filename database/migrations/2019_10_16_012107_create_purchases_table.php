<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('seller_id');
            $table->integer('buyer_id');
            $table->integer('product_id');
            $table->string('name')->nullable();
            $table->string('extension')->nullable();
            $table->mediumText('description')->nullable();
            $table->integer('price');
            $table->string('currency', 3)->nullable();
            $table->string('file_id')->nullable();
            $table->string('thumbnail_id')->nullable();
            $table->string('charge_id')->nullable();
            $table->boolean('approved');
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
        Schema::dropIfExists('purchases');
    }
}
