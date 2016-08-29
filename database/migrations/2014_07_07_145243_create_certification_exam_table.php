<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCertificationExamTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certification_exams', function ($table) {
            $table->integer('certification_id')->unsigned();
            $table->integer('exam_id')->unsigned();
            
            // composite keys
            $table->primary(array('certification_id', 'exam_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('certification_exams');
    }
}
