<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInputFieldsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('input_fields', function ($table) {
            $table->increments('id');
            $table->enum('type', ['radio', 'dropdown', 'textbox']);
            $table->string('answer')->nullable()->default(null);
            $table->string('tolerance')->nullable()->default(null);
             $table->string('value')->nullable()->default(null);            // for radio & dropdowns
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
        Schema::drop('input_fields');
    }
}
