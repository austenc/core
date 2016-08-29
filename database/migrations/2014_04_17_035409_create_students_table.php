<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $statusOptions = ['active', 'archive', 'hold', 'locked'];

         // Creates the students table
           Schema::create('students', function ($table) use ($statusOptions) {
                  $table->increments('id');
                  $table->integer('user_id');
                  $table->string('first');
                  $table->string('middle')->nullable();
                  $table->string('last');
                  $table->string('ssn');
                  $table->string('ssn_hash');
                  $table->date('birthdate');
                  $table->string('phone')->nullable()->default(null);
                  $table->string('alt_phone')->nullable()->default(null);
                  $table->boolean('is_unlisted')->default(false);
                  $table->boolean('is_oral')->default(false)->comment = "Current Oral scheduling status. Assign Oral knowledge Testform?";
                  $table->enum('gender', ['Male', 'Female'])->nullable();
                  $table->string('address');
                  $table->string('city');
                  $table->string('state');
                  $table->string('zip');
                  $table->integer('creator_id')->unsigned();
                  $table->string('creator_type');
                  $table->text('comments')->nullable()->default(null);
                  $table->enum('status', $statusOptions)->comment = "Sets multiple states on the student account to include complete lockout and hold as needed.";
                  $table->timestamp('synced_at')->nullable()->default(null);
                  $table->timestamp('activated_at')->nullable()->default(null);
                  $table->timestamp('deactivated_at')->nullable()->default(null);
                  $table->nullableTimestamps();

                  $table->index('user_id');
            });

            // Since the Schema Builder doesn't support the 'SET' datatype, we have to do this
            $table_prefix = DB::getTablePrefix();
        DB::statement("ALTER TABLE `" . $table_prefix . "students` CHANGE `status` `status` SET('" . implode("','", $statusOptions) . "') DEFAULT 'active';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('students');
    }
}
