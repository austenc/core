<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTesteventExamTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('testevent_exam', function ($table) {
            $table->integer('testevent_id')->unsigned();
            $table->integer('exam_id')->unsigned();
            $table->integer('open_seats')->unsigned()->nullable();
            $table->integer('reserved_seats')->unsigned()->nullable();
            $table->boolean('is_paper')->default(false);

            // composite key
            $table->primary(array('testevent_id', 'exam_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('testevent_exam');
    }
}
