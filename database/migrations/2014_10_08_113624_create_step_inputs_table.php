<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStepInputsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('step_inputs', function ($table) {
            $table->integer('input_id')->unsigned();
            $table->integer('step_id')->unsigned();

            // composite keys
            $table->primary(['input_id', 'step_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('step_inputs');
    }
}
