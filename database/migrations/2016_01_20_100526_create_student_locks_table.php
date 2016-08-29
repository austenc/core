<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentLocksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_locks', function ($table) {
            $table->increments('id');
            $table->integer('student_id')->unsigned();
            $table->text('comments');
            $table->text('instructions');
            $table->enum('lock_status', ['active', 'resolved'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('student_locks');
    }
}
