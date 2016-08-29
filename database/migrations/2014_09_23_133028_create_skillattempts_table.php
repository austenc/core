<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkillattemptsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skillattempts', function ($table) {
            // Structure
            $table->increments('id');
            $table->integer('skillexam_id')->unsigned();
            $table->integer('skilltest_id')->unsigned()->nullable()->default(null);
            $table->integer('student_id')->unsigned();
            $table->integer('facility_id')->unsigned();
            $table->integer('student_training_id')->unsigned()->nullable()->default(null);
            $table->integer('testevent_id')->unsigned();
            $table->enum('funding_source', ['facility', 'self', 'agency'])->nullable()->default(null);
            $table->string('attendance', 20)->nullable()->default(null);
            $table->enum('status', [
            'assigned',
            'pending',
            'started',
            'passed',
            'failed',
            'rescheduled',
            'unscored',
            'noshow'
            ])->nullable()->default('assigned');
            $table->enum('payment_status', [
                'free',
                'paid',
                'unpaid'
            ])->nullable()->default('unpaid');
            $table->enum('payable_status', [
                'free',
                'paid',
                'unpaid'
            ])->nullable()->default('unpaid');
            $table->enum('billing_status', [
                'free',
                'uninvoiced',
                'invoiced',
                'paid'
            ])->nullable()->default('uninvoiced');
            $table->boolean('archived')->default(false);
            $table->boolean('hold')->default(false);
            $table->text('anomalies')->nullable()->default(null);
            $table->datetime('start_time')->nullable()->default(null);
            $table->datetime('end_time')->nullable()->default(null);
            $table->integer('printed_by')->unsigned()->nullable()->default(null);
            $table->string('image_file_name')->nullable();
            $table->integer('image_file_size')->nullable();
            $table->string('image_content_type')->nullable();
            $table->timestamp('image_updated_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('student_id');
            $table->index('testevent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('skillattempts');
    }
}
