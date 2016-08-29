<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTesteventsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('testevents', function ($table) {
            $table->increments('id');
            $table->integer('discipline_id')->unsigned();
            $table->integer('facility_id')->unsigned();
            $table->integer('observer_id')->unsigned();
            $table->boolean('is_mentor')->default(false); // whether the observer is mentoring or not
            $table->integer('proctor_id')->unsigned()->nullable()->default(null);
            $table->integer('actor_id')->unsigned()->nullable()->default(null);
            $table->string('actor_type')->nullable()->default(null);
            $table->string('proctor_type')->nullable()->default(null);
            $table->date('test_date');
            $table->time('start_time');
            $table->boolean('locked')->default(false);
            $table->boolean('is_paper')->default(false);
            $table->boolean('is_regional')->default(true);
            $table->string('start_code')->nullable();
            $table->timestamp('ended')->nullable();
            $table->text('comments')->nullable();
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
        Schema::drop('testevents');
    }
}
