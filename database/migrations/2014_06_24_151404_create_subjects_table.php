<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subjects', function ($table) {
            $table->increments('id');
            $table->integer('exam_id')->unsigned();
            $table->integer('old_number')->unsigned()->nullable()->default(null);
            $table->integer('report_as')->unsigned()->nullable()->default(null);
            $table->string('name');
            $table->string('client');
            $table->timestamps();
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
        Schema::drop('subjects');
    }
}
