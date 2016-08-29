<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trainings', function ($table) {
            $table->increments('id');
            $table->integer('discipline_id')->unsigned();
            $table->text('name');
            $table->text('abbrev')->nullable()->default(null);
            $table->decimal('price')->nullable()->default(null);
            $table->integer('valid_for')->unsigned()->default(24)->comment = "# months student training is valid for";

            // Required hours
            $table->decimal('classroom_hours')->nullable()->default(null);
            $table->decimal('distance_hours')->nullable()->default(null);
            $table->decimal('lab_hours')->nullable()->default(null);
            $table->decimal('traineeship_hours')->nullable()->default(null);
            $table->decimal('clinical_hours')->nullable()->default(null);

            $table->text('comments')->nullable()->default(null);
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
        Schema::drop('trainings');
    }
}
