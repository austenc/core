<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilitiesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $statusOptions = ['active', 'archive', 'hold', 'locked'];

        Schema::create('facilities', function ($table) use ($statusOptions) {
                  $table->increments('id');
                  $table->integer('user_id');
                  $table->string('name')->unique();
                  $table->string('license')->unique()->nullable()->default(null)->comment = "Global license, usually assigned by State Agency";
                  $table->date('expires')->nullable()->default(null);
                  $table->text('actions');
                  $table->text('administrator')->nullable()->default(null);
                  $table->text('don')->nullable()->default(null);
                  $table->string('site_type')->nullable()->default(null);
                  $table->string('timezone')->nullable()->default(null);
                  $table->integer('max_seats')->nullable()->default(null);
                  $table->string('address');
                  $table->string('city');
                  $table->string('state');
                  $table->string('zip');
                  $table->string('mail_address')->nullable();
                  $table->string('mail_city')->nullable();
                  $table->string('mail_state')->nullable();
                  $table->string('mail_zip')->nullable();
                  $table->string('phone')->nullable()->default(null);
                  $table->string('alt_phone')->nullable()->default(null);
                  $table->string('fax')->nullable()->default(null);
                  $table->text('directions')->comment = "Custom driving directions";
                  $table->string('driving_map_file_name')->nullable();
                  $table->integer('driving_map_file_size')->nullable();
                  $table->string('driving_map_content_type')->nullable();
                  $table->timestamp('driving_map_updated_at')->nullable();
                  $table->text('comments')->nullable()->default(null);
                  $table->enum('status', $statusOptions)->default('active')->comment = "Sets multiple states on the facilities account to include complete lockout and hold as needed.";
                  $table->boolean('override_sync')->default(false)->comment = "Prevent external source sync. Useful for \'special\' Facilities or Instructors that must be kept (hdmaster, osbn, etc). Still shows up in reports.";
                  $table->timestamp('synced_at')->nullable()->default(null)->comment = "Last time this record was updated by an external source";
                  $table->date('last_training_approval')->nullable()->default(null)->comment = "Last time this record was approved for training by outside Agency";
                  $table->timestamps();
                  $table->softDeletes();
            });

            // Since the Schema Builder doesn't support the 'SET' datatype, we have to do this
            $table_prefix = DB::getTablePrefix();
        DB::statement("ALTER TABLE `" . $table_prefix . "facilities` CHANGE `status` `status` SET('" . implode("','", $statusOptions) . "') DEFAULT 'active';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('facilities');
    }
}
