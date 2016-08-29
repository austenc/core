<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePendingeventExamTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendingevent_exam', function (Blueprint $table) {
            $table->integer('pendingevent_id')->unsigned();
            $table->integer('exam_id')->unsigned();
            $table->integer('open_seats')->unsigned()->nullable();
            $table->integer('reserved_seats')->unsigned()->nullable();
            $table->tinyInteger('is_paper')->unsigned();

            // composite key
            $table->primary(array('pendingevent_id', 'exam_id'));
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pendingevent_exam');
    }
}
