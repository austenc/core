<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamRelTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_requirements', function ($table) {
            $table->integer('exam_id')->unsigned();
            $table->integer('req_exam_id')->unsigned();
            
            // composite keys
            $table->primary(array('exam_id', 'req_exam_id'));

            // foreign key constraints
            //$table->foreign('exam_id')->references('id')->on('exams');
            //$table->foreign('req_exam_id')->references('id')->on('exams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('exam_requirements');
    }
}
