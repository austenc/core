<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStudentAdaTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_ada', function (Blueprint $table) {
            $table->integer('student_id');
            $table->integer('ada_id');
            $table->enum('status', ['pending', 'accepted', 'denied'])->default('pending');
            $table->text('notes');
            $table->nullableTimestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('student_ada');
    }
}
