<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestattemptsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('testattempts', function ($table) {
            // Structure
            $table->increments('id');
            $table->integer('testevent_id')->unsigned();
            $table->integer('facility_id')->unsigned();
            $table->integer('student_training_id')->unsigned()->nullable()->default(null);
            $table->integer('student_id')->unsigned();
            $table->integer('exam_id')->unsigned();
            $table->integer('testform_id')->unsigned()->nullable()->default(null);
            $table->text('answers')->nullable();
            $table->datetime('start_time')->nullable();
            $table->datetime('end_time')->nullable();
            $table->float('score')->nullable();
            $table->text('legacy_data')->nullable()->default(null)->comment = "Dataline used to create this record during import from testmaster files";
            $table->integer('correct_answers')->nullable();
            $table->text('correct_by_subject')->nullable();
            $table->integer('total_questions')->nullable();
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
            $table->boolean('is_oral')->default(false);
            $table->enum('seat_type', ['open', 'reserved'])->default('open');
            $table->text('attendance')->nullable();
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
        Schema::drop('testattempts');
    }
}
