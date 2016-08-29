<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestitemVocabTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creates the testitems table
        Schema::create('testitem_vocab', function ($table) {
            $table->integer('testitem_id');
            $table->integer('vocab_id');
            $table->timestamps();

            $table->primary(['testitem_id', 'vocab_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('testitem_vocab');
    }
}
