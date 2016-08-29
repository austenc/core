<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillRolesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Are there any roles? don't do anything if so
        if (Role::all()->isEmpty()) {
            // Define all Roles
            Role::create(['name' => 'Admin']);
            Role::create(['name' => 'Staff']);
            Role::create(['name' => 'Student']);
            Role::create(['name' => 'Actor']);
            Role::create(['name' => 'Proctor']);
            Role::create(['name' => 'Observer']);
            Role::create(['name' => 'Instructor']);
            Role::create(['name' => 'Facility']);
            Role::create(['name' => 'Agency']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("SET foreign_key_checks = 0");
        DB::table('roles')->truncate();
    }
}
