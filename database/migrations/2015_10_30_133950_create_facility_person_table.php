<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilityPersonTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_person', function ($table) {
            $table->integer('facility_id')->unsigned();
            $table->integer('discipline_id')->unsigned();
            $table->morphs('person');
            $table->string('tm_license')->unique()->nullable()->default(null)->comment = 'Unique TestMaster license relating this person to a Training Program';
            $table->string('old_license')->nullable()->default(null)->comment = "Old license number causing conflict. Usually a result of import multiple disciplines into a single state.";
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('facility_person');
    }
}
