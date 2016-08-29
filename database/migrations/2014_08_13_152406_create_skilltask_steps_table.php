<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkilltaskStepsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skilltask_steps', function ($table) {
            $table->increments('id');
            $table->integer('skilltask_id')->unsigned();
            $table->char('weight', 1);
            $table->boolean('is_key')->default(false);
            $table->integer('ordinal')->unsigned();
            $table->text('expected_outcome');
            $table->text('alt_display')->nullable()->defaut(null);
            $table->text('comments')->nullable()->defaut(null);
            $table->boolean('vinput_review')->default(false);
            $table->text('media')->nullable()->defaut(null);
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
        Schema::drop('skilltask_steps');
    }
}
