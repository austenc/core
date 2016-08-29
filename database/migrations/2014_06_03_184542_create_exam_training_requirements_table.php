<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamTrainingRequirementsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_training_requirements', function ($table) {
            $table->integer('exam_id')->unsigned();
            $table->integer('training_id')->unsigned();
            
            // composite keys
            $table->primary(['exam_id', 'training_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('exam_training_requirements');
    }
}
