<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilityDisciplineTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_discipline', function ($table) {
            $table->integer('facility_id');
            $table->integer('discipline_id');
            $table->integer('parent_id')->nullable()->default(null);
            $table->string('tm_license')->unique()->comment = "Existing Testmaster or reassigned unique license number.";
            $table->string('old_license')->nullable()->default(null)->comment = "Old Testmaster license number usually conflicting. Result of import multiple disciplines into a single state.";
            $table->boolean('active')->default(true);
        
            // composite keys
            $table->primary(['facility_id', 'discipline_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('facility_discipline');
    }
}
