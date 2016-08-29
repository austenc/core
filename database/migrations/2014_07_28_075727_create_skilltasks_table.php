<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkilltasksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skilltasks', function ($table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable()->default(null);
            $table->integer('legacy_id')->nullable()->default(null);
            $table->string('title');
            $table->string('long_title')->nullable()->default(null);
            $table->text('scenario');
            $table->text('note')->nullable()->default(null);
            $table->boolean('setup_review')->default(false);
            $table->string('weight', 2);
            $table->integer('minimum');
            $table->integer('avg_time')->nullable();
            $table->enum('status', ['active', 'archived', 'draft'])->default('draft');
            $table->string('fed_classification')->nullable();
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
        Schema::drop('skilltasks');
    }
}
