<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePendingeventsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendingevents', function ($table) {
            $table->increments('id');
            $table->integer('facility_id')->unsigned()->nullable();
            $table->integer('discipline_id')->unsigned()->nullable();
            $table->integer('observer_id')->unsigned()->nullable()->default(null);
            $table->integer('proctor_id')->unsigned()->nullable()->default(null);
            $table->string('proctor_type')->nullable()->default(null);
            $table->integer('actor_id')->unsigned()->nullable();
            $table->string('actor_type')->nullable()->default(null);
            $table->date('test_date')->nullable();
            $table->time('start_time')->nullable();
            $table->boolean('locked')->default(false);
            $table->boolean('is_paper')->default(false);
            $table->boolean('is_mentor')->default(false);
            $table->boolean('is_regional')->default(true);
            $table->string('start_code')->nullable();
            $table->timestamp('ended')->nullable();
            $table->text('comments')->nullable();
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
        Schema::drop('pendingevents');
    }
}
