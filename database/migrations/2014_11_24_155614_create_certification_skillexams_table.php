<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCertificationSkillexamsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certification_skillexams', function ($table) {
            $table->integer('certification_id')->unsigned();
            $table->integer('skillexam_id')->unsigned();
            
            // composite keys
            $table->primary(['certification_id', 'skillexam_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('certification_skillexams');
    }
}
