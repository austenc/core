<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTesteventSkillexamTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('testevent_skillexam', function ($table) {
            $table->integer('testevent_id')->unsigned();
            $table->integer('skillexam_id')->unsigned();
            $table->integer('open_seats')->unsigned()->nullable();
            $table->integer('reserved_seats')->unsigned()->nullable();

            // composite key
            $table->primary(['testevent_id', 'skillexam_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('testevent_skillexam');
    }
}
