<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamSkillRequirementsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_skill_requirements', function ($table) {
            $table->integer('exam_id')->unsigned();
            $table->integer('skillexam_id')->unsigned();
            $table->enum('status', ['prereq', 'coreq'])->default('prereq');

            // composite keys
            $table->primary(['exam_id', 'skillexam_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('exam_skill_requirements');
    }
}
