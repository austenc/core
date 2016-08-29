<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisciplineAndSpecialRateToPayablesRates extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payables_rates', function (Blueprint $table) {
            $table->integer('discipline_id')->after('id');
            $table->decimal('special_rate', 10, 2)->after('knowledge_rate');
        });

        \Eloquent::unguard();

        DB::table('payables_rates')->where('id', 1)->update(['discipline_id' => 1, 'level_name' => 'CNA Level 1', 'special_rate' => '0.00']);
        DB::table('payables_rates')->where('id', 2)->update(['discipline_id' => 1, 'level_name' => 'CNA Level 2', 'special_rate' => "0.00"]);
        DB::table('payables_rates')->where('id', 3)->update(['discipline_id' => 1, 'level_name' => 'CNA Level 3', 'special_rate' => '0.00']);
        DB::table('payables_rates')->where('id', 4)->update(['discipline_id' => 2, 'level_name' => 'CMA Level 1', 'special_rate' => '24.00', 'oral_rate' => '0.00', 'knowledge_rate' => '10.00', 'skill_rate' => '0.00']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payables_rates', function (Blueprint $table) {
            $table->dropColumn('discipline_id');
            $table->dropColumn('special_rate');
        });
    }
}
