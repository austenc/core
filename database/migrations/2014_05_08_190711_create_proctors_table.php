<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProctorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $statusOptions = ['active', 'archive', 'hold', 'locked'];

        Schema::create('proctors', function ($table) use ($statusOptions) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('first');
            $table->string('middle')->nullable();
            $table->string('last');
            $table->date('birthdate')->nullable()->default(null);
            $table->enum('gender', array('Male', 'Female'))->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('phone')->nullable();
            $table->string('alt_phone')->nullable();
            $table->string('fax')->nullable();
            $table->text('comments')->nullable()->default(null);
            $table->enum('status', $statusOptions)->default('active')->comment = "Sets multiple states on the proctors account to include complete lockout and hold as needed.";
            $table->timestamps();
            $table->timestamp('synced_at')->nullable()->default(null);
            $table->softDeletes();
        });

            // Since the Schema Builder doesn't support the 'SET' datatype, we have to do this
            $table_prefix = DB::getTablePrefix();
        DB::statement("ALTER TABLE `" . $table_prefix . "proctors` CHANGE `status` `status` SET('" . implode("','", $statusOptions) . "') DEFAULT 'active';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('proctors');
    }
}
