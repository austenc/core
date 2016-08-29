<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkilltaskResponses extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skilltask_responses', function ($table) {
            $table->increments('id');
            $table->integer('skillattempt_id')->unsigned();
            $table->integer('skilltask_id')->unsigned();
            $table->integer('student_id')->unsigned();
            $table->integer('setup_id')->unsigned()->nullable()->default(null);
            $table->text('response');
            $table->enum('status', ['passed', 'failed', 'pending']);
            $table->boolean('archived')->default(false);
            $table->float('score')->nullable();
            $table->string('score_type')->nullable();
            $table->integer('creator_id')->unsigned()->nullable()->default(null);
            $table->string('creator_type')->nullable()->default(null);
            $table->timestamps();

            $table->index('skilltask_id');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('skilltask_responses');
    }
}
