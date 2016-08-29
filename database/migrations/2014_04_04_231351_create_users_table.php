<?php

use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{

    /**
     * Run the migrations. 
     *
     * NOTE: this migration is (mostly) based upon the zizaco/confide migration
     * (ConfideSetupUsersTable), but having it named as such causes composer 
     * ambiguous class warnings
     *
     * @return  void
     */
    public function up()
    {
        if (! Schema::hasTable('users')) {
            // Creates the users table
            Schema::create('users', function ($table) {
                $table->increments('id');
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('confirmation_code');
                $table->string('remember_token');
                $table->boolean('confirmed')->default(false);
                $table->integer('userable_id')->unsigned()->nullable()->default(null);
                $table->string('userable_type')->nullable()->default(null);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('password_reminders')) {
            // Creates password reminders table
            Schema::create('password_reminders', function ($t) {
                $t->string('email');
                $t->string('token');
                $t->timestamp('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        if (Schema::hasTable('password_reminders')) {
            Schema::drop('password_reminders');
        }

        if (Schema::hasTable('users')) {
            Schema::drop('users');
        }
    }
}
