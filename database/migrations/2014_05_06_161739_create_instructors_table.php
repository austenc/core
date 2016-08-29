<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstructorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $statusOptions = ['active', 'archive', 'hold', 'locked'];

        Schema::create('instructors', function ($table) use ($statusOptions) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('first');
            $table->string('middle')->nullable()->default(null);
            $table->string('last');
            $table->date('birthdate')->nullable()->default(null);
            $table->enum('gender', array('Male', 'Female'))->nullable();
            $table->string('license')->unique()->nullable()->default(null)->comment = "Unique license assigned by State Agency (i.e. RN#)";
            $table->date('expires')->nullable()->default(null);
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('phone')->nullable()->default(null);
            $table->string('alt_phone')->nullable()->default(null);
            $table->text('comments')->nullable()->default(null);
            $table->enum('status', $statusOptions)->default('active')->comment = "Sets multiple states on the instructors account to include complete lockout and hold as needed.";
            $table->timestamp('synced_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
            // Since the Schema Builder doesn't support the 'SET' datatype, we have to do this
            $table_prefix = DB::getTablePrefix();
        DB::statement("ALTER TABLE `" . $table_prefix . "instructors` CHANGE `status` `status` SET('" . implode("','", $statusOptions) . "') DEFAULT 'active';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('instructors');
    }
}
