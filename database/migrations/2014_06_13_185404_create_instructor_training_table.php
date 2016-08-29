<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstructorTrainingTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instructor_training', function ($table) {
            $table->integer('instructor_id')->unsigned();
            $table->integer('training_id')->unsigned();

            $table->primary(array('instructor_id', 'training_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('instructor_training');
    }
}
