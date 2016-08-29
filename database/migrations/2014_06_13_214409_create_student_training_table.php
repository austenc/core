<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentTrainingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_training', function ($table) {
            $table->increments('id');
            $table->integer('student_id')->unsigned();
            $table->integer('discipline_id')->unsigned();
            $table->integer('training_id')->unsigned();
            $table->integer('facility_id')->unsigned()->nullable()->default(null);
            $table->integer('instructor_id')->unsigned();
            $table->enum('status', ['attending', 'passed', 'failed'])->default('attending');
            $table->text('reason')->nullable()->default(null);

            // Required Hours
            $table->decimal('classroom_hours')->nullable()->default(null);
            $table->decimal('distance_hours')->nullable()->default(null);
            $table->decimal('lab_hours')->nullable()->default(null);
            $table->decimal('traineeship_hours')->nullable()->default(null);
            $table->decimal('clinical_hours')->nullable()->default(null);

            // Timestamps
            $table->date('started')->nullable()->default(null);
            $table->date('ended')->nullable()->default(null);
            $table->date('expires')->nullable()->default(null);
            $table->timestamps();
            $table->timestamp('archived_at')->nullable()->default(null);
            $table->morphs('creator');

            // Indexes
            $table->index('student_id');
            $table->index('training_id');
            $table->index('facility_id');
            $table->index('instructor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('student_training');
    }
}
