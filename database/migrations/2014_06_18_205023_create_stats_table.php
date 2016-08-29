<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creates the testitems table
        Schema::create('stats', function ($table) {
            $table->increments('id');
            $table->integer('testitem_id');
            $table->string('client');
            $table->integer('count');
            $table->float('difficulty')->nullable();
            $table->float('discrimination')->nullable();
            $table->float('guessing')->nullable();
            $table->float('pvalue')->nullable();
            $table->float('angoff')->nullable();
            $table->float('pbs')->nullable();
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
        Schema::drop('stats');
    }
}
