<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePendingscoresTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendingscores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('scoreable_type');
            $table->integer('scoreable_id');
            $table->string('expected_outcome')->nullable()->default(null);
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
        Schema::drop('pendingscores');
    }
}
