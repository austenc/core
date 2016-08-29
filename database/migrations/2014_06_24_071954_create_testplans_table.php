<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestplansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('testplans', function ($table) {
            $table->increments('id');
            $table->integer('exam_id');
            $table->string('name');
            $table->string('client');
            $table->enum('status', ['active', 'archived'])->default('active');

            // Generation stats
            $table->float('readinglevel');
            $table->float('readinglevel_max');
            $table->float('reliability');
            $table->float('reliability_max');
            $table->float('pvalue');
            $table->float('pvalue_max');
            $table->float('difficulty');
            $table->float('difficulty_max');
            $table->float('discrimination');
            $table->float('discrimination_max');
            $table->float('guessing');
            $table->float('guessing_max');
            $table->float('cutscore');
            $table->float('cutscore_max');
            $table->float('target_theta');
            $table->float('pbs');

            // Item pool parameters
            $table->float('item_pvalue');
            $table->float('item_pvalue_max');

            // Generation parameters
            $table->integer('max_attempts');
            $table->integer('max_pvalue_attempts');
            $table->integer('ignore_stats');

            // Other
            $table->integer('timelimit');
            $table->text('items_by_subject');
            $table->integer('minimum_score');
            $table->text('comments');

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
        Schema::drop('testplans');
    }
}
