<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkillexamTrainingRequirementsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skillexam_training_requirements', function ($table) {
            $table->integer('skillexam_id')->unsigned();
            $table->integer('training_id')->unsigned();
            
            // composite keys
            $table->primary(['skillexam_id', 'training_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('skillexam_training_requirements');
    }
}
