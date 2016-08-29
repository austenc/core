<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePendingeventSkillexamTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendingevent_skillexam', function (Blueprint $table) {
            $table->integer('pendingevent_id')->unsigned();
            $table->integer('skillexam_id')->unsigned();
            $table->integer('open_seats')->unsigned()->nullable();
            $table->integer('reserved_seats')->unsigned()->nullable();

            // composite key
            $table->primary(array('pendingevent_id', 'skillexam_id'));
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pendingevent_skillexam');
    }
}
