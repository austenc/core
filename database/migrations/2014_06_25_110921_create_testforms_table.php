<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('testforms', function ($table) {
            $table->increments('id');
            $table->integer('exam_id')->unsigned();
            $table->integer('testplan_id')->unsigned();
            $table->integer('legacy_id')->unsigned()->nullable()->default(null);
            $table->string('name');
            $table->string('client');
            $table->integer('minimum');
            $table->boolean('oral');
            $table->boolean('spanish');
            $table->enum('status', ['active', 'archived', 'draft'])->default('draft');
            $table->text('header')->nullable()->default(null);
            $table->text('footer')->nullable()->default(null);
            $table->text('legacy_data')->nullable()->default(null);
            $table->integer('english_source')->nullable()->default(null);
            $table->integer('scramble_source')->nullable()->default(null);
            $table->integer('archive_source')->nullable()->default(null);
            $table->timestamp('archived_at')->nullable()->default(null);
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
        Schema::drop('testforms');
    }
}
