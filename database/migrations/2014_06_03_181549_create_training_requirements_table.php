<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainingRequirementsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_requirements', function ($table) {
            $table->integer('training_id')->unsigned();
            $table->integer('req_training_id')->unsigned();
            
            // composite keys
            $table->primary(array('training_id', 'req_training_id'));

            // foreign key constraints
            //$table->foreign('training_id')->references('id')->on('trainings');
            //$table->foreign('req_training_id')->references('id')->on('trainings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('training_requirements');
    }
}
