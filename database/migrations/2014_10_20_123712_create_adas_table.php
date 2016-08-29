<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdasTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('abbrev');
            $table->integer('extend_time')->nullable();
            $table->boolean('paper_only')->default(false);
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
        Schema::drop('adas');
    }
}
