<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsumptionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consumption_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('original_consumption_details_id')->nullable();
            $table->bigInteger('consumption_id');
            $table->integer('machine_id')->nullable();
            $table->tinyInteger('testtype')->nullable();
            $table->tinyInteger('synched')->default(0)->comment("0:Awaiting synching; 1:Synched; 2:Update awaiting synching;");
            $table->date('datesynched')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('consumption_details');
    }
}
