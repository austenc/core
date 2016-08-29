<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkillexamExamRequirementsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skillexam_exam_requirements', function ($table) {
            $table->integer('skillexam_id')->unsigned();
            $table->integer('exam_id')->unsigned();
            $table->enum('status', ['prereq', 'coreq'])->default('prereq');
            
            // composite keys
            $table->primary(['skillexam_id', 'exam_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('skillexam_exam_requirements');
    }
}
