<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilityAffiliatedTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_affiliated', function ($table) {
            $table->integer('facility_id')->unsigned();
            $table->integer('affiliated_id')->unsigned();
            $table->integer('discipline_id')->unsigned();

            // composite key
            $table->primary(['facility_id', 'affiliated_id', 'discipline_id'], 'affiliate_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('facility_affiliated');
    }
}
