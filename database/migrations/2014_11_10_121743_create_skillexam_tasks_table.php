<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkillexamTasksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skillexam_tasks', function ($table) {
            $table->integer('skillexam_id')->unsigned();
            $table->integer('skilltask_id')->unsigned();
            
            // composite keys
            $table->primary(['skillexam_id', 'skilltask_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('skillexam_tasks');
    }
}
