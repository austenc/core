<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function ($table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('type')->default('info');
                $table->string('subject')->nullable();
                $table->text('body')->nullable();
                $table->integer('object_id')->unsigned();
                $table->string('object_type');
                $table->boolean('is_read')->default(0);
                $table->dateTime('sent_at')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('notifications')) {
            Schema::drop('notifications');
        }
    }
}
