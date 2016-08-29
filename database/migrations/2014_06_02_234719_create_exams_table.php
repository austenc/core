<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exams', function ($table) {
            $table->increments('id');
            $table->integer('discipline_id')->unsigned();
            $table->text('name');
            $table->text('abbrev');
            $table->text('slug')->nullable()->default(null);
            $table->boolean('has_paper')->default(0);
            $table->decimal('price')->nullable()->default(null);
            $table->decimal('assisted_price')->nullable()->default(null);
            $table->integer('max_attempts')->nullable()->unsigned();
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
        Schema::drop('exams');
    }
}
