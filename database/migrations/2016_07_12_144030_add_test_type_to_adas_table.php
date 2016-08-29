<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTestTypeToAdasTable extends Migration
{
    protected $typeByAbbrev = [
        'ABP' => 'skill',
        'EBP' => 'skill',
        'ETW' => 'knowledge',
        'ETS' => 'skill',
        'LGP' => 'knowledge',
        'REM' => 'knowledge',
        'RMX' => 'knowledge',
        'RMC' => 'knowledge',
        'SPA' => 'knowledge',
        'SSW' => 'knowledge',
        'STR' => 'knowledge',
        'OTH' => 'both',
        'RMD' => 'knowledge',
        'RDC' => 'knowledge',
        'REA' => 'knowledge',
        'REX' => 'knowledge',
        'DTC' => 'knowledge',
        'ETC' => 'knowledge',
        'DTW' => 'knowledge',
        'DTS' => 'skill',
        'SEC' => 'knowledge',
        'SET' => 'knowledge',
        'SDT' => 'knowledge',
        'INS' => 'skill',
        'INW' => 'knowledge',
        'INT' => 'both',
        'ROX' => 'knowledge',
        'REC' => 'knowledge',
        'RDT' => 'knowledge',
        'DCR' => 'knowledge',
        'EXC' => 'knowledge',
        'ETB' => 'both',
        'RWC' => 'knowledge',
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the field to the ADA's table
        Schema::table('adas', function (Blueprint $table) {
            $table->enum('test_type', ['knowledge', 'skill', 'both'])->default('knowledge');
        });

        // Go through all ada's and set the test type based on abbrev
        $adas = \Ada::all();
        foreach ($adas as $ada) {
            if (array_key_exists($ada->abbrev, $this->typeByAbbrev)) {
                $ada->test_type = $this->typeByAbbrev[$ada->abbrev];
                $ada->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adas', function (Blueprint $table) {
            $table->dropColumn('test_type');
        });
    }
}
