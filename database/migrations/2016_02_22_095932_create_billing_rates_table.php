<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \BillingRate as BillingRate;

class CreateBillingRatesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Structure
        Schema::create('billing_rates', function ($table) {
            $table->increments('id');
            $table->string('rate_name', 155)->nullable();
            $table->enum('rate_abbrev', [
                'R', 'X', 'W', 'Y', 'A', 'B', 'C', 'D'
            ])->nullable();
            $table->decimal('written_rate', 10, 2)->nullable()->default('30.00');
            $table->decimal('oral_rate', 10, 2)->nullable()->default('40.00');
            $table->decimal('skill_rate', 10, 2)->nullable()->default('85.00');
            $table->decimal('expired_knowledge_rate', 10, 2)->nullable()->default('20.00');
            $table->decimal('expired_skill_rate', 10, 2)->nullable()->default('45.00');
            $table->decimal('expired_knowledge_skill_rate', 10, 2)->nullable()->default('75.00');
            $table->timestamps();
        });

        \Eloquent::unguard();

        BillingRate::create([
            'rate_name'    => 'Paper Regional',
            'rate_abbrev' => 'R',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        BillingRate::create([
            'rate_name'    => 'Paper Flexible',
            'rate_abbrev' => 'X',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        BillingRate::create([
            'rate_name'    => 'Web Flexible',
            'rate_abbrev' => 'W',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        BillingRate::create([
            'rate_name'    => 'Web Regional',
            'rate_abbrev' => 'Y',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        BillingRate::create([
            'rate_name'    => 'Two Flight Paper Regional',
            'rate_abbrev' => 'A',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        BillingRate::create([
            'rate_name'    => 'Two Flight Paper Flexible',
            'rate_abbrev' => 'B',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        BillingRate::create([
            'rate_name'    => 'Two Flight Web Flexible',
            'rate_abbrev' => 'C',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        BillingRate::create([
            'rate_name'    => 'Two Flight Web Regional',
            'rate_abbrev' => 'D',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('billing_rates');
    }
}
