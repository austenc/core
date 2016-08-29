<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkilltestsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skilltests', function ($table) {
            $table->increments('id');
            $table->integer('skillexam_id')->unsigned();
            $table->integer('legacy_id')->unsigned()->nullable()->default(null);
            $table->integer('parent_id')->unsigned()->nullable()->default(null);
            $table->string('header');
            $table->enum('status', ['active', 'archived', 'draft'])->default('draft');
            $table->string('minimum', 5)->default(80);
            $table->text('legacy_data')->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->text('comments')->nullable()->default(null);
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
        Schema::drop('skilltests');
    }
}
