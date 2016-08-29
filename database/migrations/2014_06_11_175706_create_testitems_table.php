<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestitemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creates the testitems table
        Schema::create('testitems', function ($table) {
            $table->increments('id');
            $table->integer('number');
            $table->string('stem');
            $table->integer('answer');
            $table->integer('user_id'); // creator_id
            $table->integer('derivative_of');
            $table->float('weight');
            $table->enum('status', ['active', 'draft'])->default('draft');
            $table->string('auth_source');
            $table->string('comments');
            $table->enum('cognitive_domain', ['Knowledge', 'Comprehension', 'Application']);
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
        Schema::drop('testitems');
    }
}
