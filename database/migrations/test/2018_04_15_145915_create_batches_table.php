<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->increments('id');
            // $table->bigInteger('original_batch_id')->unsigned()->index();
            $table->double('original_batch_id', 14, 2)->unsigned()->index();
            $table->boolean('highpriority')->default(false)->nullable();
            $table->boolean('input_complete')->default(false)->nullable();
            $table->boolean('batch_full')->default(false)->nullable(); 

            // 0 is default i.e. new
            // 1 is dispatched
            // 2 is staging i.e. all samples are ready, batch awaiting dispatch
            $table->tinyInteger('batch_complete')->unsigned()->default(0)->nullable();
            $table->tinyInteger('site_entry')->unsigned()->default(0)->nullable();

            $table->boolean('sent_email')->default(false)->nullable(); 

            $table->string('entered_by', 20)->nullable();
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->integer('received_by')->unsigned()->nullable();
            $table->integer('printedby')->unsigned()->nullable();

            $table->integer('lab_id')->unsigned()->index();
            $table->integer('facility_id')->unsigned()->index();

            $table->date('datedispatchedfromfacility')->nullable();
            $table->date('datereceived')->nullable()->index();
            $table->date('datedispatched')->nullable();
            $table->date('dateindividualresultprinted')->nullable();
            $table->date('datebatchprinted')->nullable();
            $table->date('dateemailsent')->nullable();

            $table->tinyInteger('synched')->default(0);
            $table->date('datesynched')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batches');
    }
}
