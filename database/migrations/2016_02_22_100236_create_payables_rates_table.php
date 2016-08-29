<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \PayableRate as PayableRate;

class CreatePayablesRatesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Structure
        Schema::create('payables_rates', function ($table) {
            $table->increments('id');
            $table->string('level_name', 155);
            $table->decimal('knowledge_rate', 10, 2);
            $table->decimal('oral_rate', 10, 2);
            $table->decimal('skill_rate', 10, 2);
            $table->decimal('ada_rate', 10, 2);
            $table->timestamps();
        });

        \Eloquent::unguard();

        PayableRate::create([
            'level_name'    => 'Level 1',
            'knowledge_rate' => '7.25',
            'oral_rate' => '2.00',
            'skill_rate' => '31.75',
            'ada_rate' => '20.00'
        ]);

        PayableRate::create([
            'level_name' => 'Level 2',
            'knowledge_rate' => '9.25',
            'oral_rate' => '2.00',
            'skill_rate' => '35.75',
            'ada_rate' => '20.00'
        ]);

        PayableRate::create([
            'level_name' => 'Level 3',
            'knowledge_rate' => '9.25',
            'oral_rate' => '2.00',
            'skill_rate' => '40.75',
            'ada_rate' => '20.00'
        ]);

        PayableRate::create([
            'level_name' => 'Level 4',
            'knowledge_rate' => '24.00',
            'oral_rate' => '2.00',
            'skill_rate' => '40.75',
            'ada_rate' => '20.00'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('payables_rates');
    }
}
