<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkilltestTasksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skilltest_tasks', function ($table) {
            $table->integer('skilltest_id')->unsigned();
            $table->integer('skilltask_id')->unsigned();
            $table->integer('ordinal')->unsigned();
            
            // composite keys
            $table->primary(array('skilltest_id', 'skilltask_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('skilltest_tasks');
    }
}
