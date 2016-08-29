<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillAdasTable extends Migration
{

    private $adas = array(
        [
            'name'        => 'Amplified Stethoscope',
            'abbrev'      => 'ABP',
            'extend_time' => null
        ],
        [
            'name'   => 'Electronic BP Cuff',
            'abbrev' => 'EBP',
            'extend_time' => null
        ],
        [
            'name'   => 'Extended Time Written',
            'abbrev' => 'ETW',
            'extend_time' => null
        ],
        [
            'name'   => 'Extended Time Skill',
            'abbrev' => 'ETS',
            'extend_time' => null
        ],
        [
            'name'   => 'Large Print Written Test',
            'abbrev' => 'LGP',
            'extend_time' => null
        ],
        [
            'name'        => 'Reader Marker',
            'abbrev'      => 'REM',
            'extend_time' => 30
        ],
        [
            'name'        => 'Reader Marker with Extended Time',
            'abbrev'      => 'RMX',
            'extend_time' => 30
        ],
        [
            'name'        => 'Reader Marker with Extended Time - Calculator',
            'abbrev'      => 'RMC',
            'extend_time' => 30
        ],
        [
            'name'   => 'Spanish Version',
            'abbrev' => 'SPA',
            'extend_time' => null
        ],
        [
            'name'   => 'Written Skill Scenario Provided',
            'abbrev' => 'SSW',
            'extend_time' => null
        ],
        [
            'name'   => 'Secluded Test Room',
            'abbrev' => 'STR',
            'extend_time' => null
        ],
        [
            'name'   => 'Other',
            'abbrev' => 'OTH',
            'extend_time' => null
        ],
        [
            'name'        => 'Reader Marker with Double Time',
            'abbrev'      => 'RMD',
            'extend_time' => 30
        ],
        [
            'name'        => 'Reader Marker with Double Time - Calculator',
            'abbrev'      => 'RDC',
            'extend_time' => 30
        ],
        [
            'name'        => 'Reader Only',
            'abbrev'      => 'REA',
            'extend_time' => 30
        ],
        [
            'name'        => 'Reader with Extended Time',
            'abbrev'      => 'REX',
            'extend_time' => 30
        ],
        [
            'name'        => 'Double Time with Calculator',
            'abbrev'      => 'DTC',
            'extend_time' => 30
        ],
        [
            'name'        => 'Extended Time with Calculator',
            'abbrev'      => 'ETC',
            'extend_time' => 30
        ],
        [
            'name'        => 'Double Time Written',
            'abbrev'      => 'DTW',
            'extend_time' => null
        ],
        [
            'name'        => 'Double Time Skill Testing',
            'abbrev'      => 'DTS',
            'extend_time' => 30
        ],
        [
            'name'        => 'Secluded testing with Extended Time - Calculator',
            'abbrev'      => 'SEC',
            'extend_time' => 30
        ],
        [
            'name'        => 'Secluded testing with Extended Time',
            'abbrev'      => 'SET',
            'extend_time' => 30
        ],
        [
            'name'        => 'Secluded testing with Double Time',
            'abbrev'      => 'SDT',
            'extend_time' => 30
        ],
        [
            'name'        => 'Interpreter for Skills Only',
            'abbrev'      => 'INS',
            'extend_time' => null
        ],
        [
            'name'        => 'Interpreter for Written Only',
            'abbrev'      => 'INW',
            'extend_time' => null
        ],
        [
            'name'        => 'Interpreter for Both Tests',
            'abbrev'      => 'INT',
            'extend_time' => null
        ],
        [
            'name'        => 'Reader or Oral with Extended Time',
            'abbrev'      => 'ROX',
            'extend_time' => 30
        ],
        [
            'name'        => 'Reader, Extended Time and Calculator',
            'abbrev'      => 'REC',
            'extend_time' => 30
        ],
        [
            'name'        => 'Reader with Double Time',
            'abbrev'      => 'RDT',
            'extend_time' => 30
        ],
        [
            'name'        => 'Double Time Calculator and Reader Only',
            'abbrev'      => 'DCR',
            'extend_time' => null
        ],
        [
            'name'        => 'Either Reader or Oral, Extra Time and Calculator',
            'abbrev'      => 'EXC',
            'extend_time' => 30
        ],
        [
            'name'        => 'Extended Time Both',
            'abbrev'      => 'ETB',
            'extend_time' => 30
        ],
        [
            'name'        => 'Reader with Calculator Only',
            'abbrev'      => 'RWC',
            'extend_time' => 30
        ]
    );

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Clear out the ADA's table if there's anything in there
        DB::table('adas')->truncate();
        DB::table('adas')->insert($this->adas);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('adas')->truncate();
    }
}
