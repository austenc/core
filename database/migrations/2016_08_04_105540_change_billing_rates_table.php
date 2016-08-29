<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBillingRatesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('billing_rates');
        Schema::create('billing_rates', function ($table) {
            $table->increments('id');
            $table->integer('discipline_id')->nullable();
            $table->string('svc_name', 155)->nullable();
            $table->enum('test_type', [
                'knowledge',
                'oral',
                'skill'
            ])->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->decimal('rate_ns', 10, 2)->nullable();
            $table->timestamps();
        });

        \Eloquent::unguard();

        BillingRate::create([
            'discipline_id' => 1,
            'svc_name' => 'Knowledge',
            'test_type' => 'knowledge',
            'rate' => '30.00',
            'rate_ns' => '30.00'
        ]);

        BillingRate::create([
            'discipline_id' => 1,
            'svc_name' => 'Oral',
            'test_type' => 'oral',
            'rate' => '40.00',
            'rate_ns' => '40.00'
        ]);

        BillingRate::create([
            'discipline_id' => 1,
            'svc_name' => 'Skill',
            'test_type' => 'skill',
            'rate' => '85.00',
            'rate_ns' => '85.00'
        ]);

        BillingRate::create([
            'discipline_id' => 2,
            'svc_name' => 'Med Aide',
            'test_type' => 'knowledge',
            'rate' => '73.00',
            'rate_ns' => '73.00'
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

        DB::table('billing_rates')->insert([
            'rate_name'    => 'Paper Regional',
            'rate_abbrev' => 'R',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        DB::table('billing_rates')->insert([
            'rate_name'    => 'Paper Flexible',
            'rate_abbrev' => 'X',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        DB::table('billing_rates')->insert([
            'rate_name'    => 'Web Flexible',
            'rate_abbrev' => 'W',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        DB::table('billing_rates')->insert([
            'rate_name'    => 'Web Regional',
            'rate_abbrev' => 'Y',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        DB::table('billing_rates')->insert([
            'rate_name'    => 'Two Flight Paper Regional',
            'rate_abbrev' => 'A',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        DB::table('billing_rates')->insert([
            'rate_name'    => 'Two Flight Paper Flexible',
            'rate_abbrev' => 'B',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        DB::table('billing_rates')->insert([
            'rate_name'    => 'Two Flight Web Flexible',
            'rate_abbrev' => 'C',
            'written_rate' => '30.00',
            'oral_rate' => '40.00',
            'skill_rate' => '85.00',
            'expired_knowledge_rate' => '20.00',
            'expired_skill_rate' => '45.00',
            'expired_knowledge_skill_rate' => '75.00'
        ]);
        DB::table('billing_rates')->insert([
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
}
