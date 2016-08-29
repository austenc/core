<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnemiesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creates the testitems table
        Schema::create('enemies', function ($table) {
            $table->integer('testitem_id');
            $table->integer('enemy_id');
            $table->timestamps();
            $table->primary(['testitem_id', 'enemy_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('enemies');
    }
}
