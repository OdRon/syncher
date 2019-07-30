<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsumptionDetailBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('consumption_detail_breakdowns', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->bigInteger('original_consumption_details_breakdown_id')->nullable();
        //     $table->bigInteger('consumption_details_id');
        //     $table->integer('consumption_breakdown_id');
        //     $table->string('consumption_breakdown_type');
        //     $table->float('opening')->default(0);
        //     $table->float('consumed')->default(0);
        //     $table->float('qty_received')->default(0);
        //     $table->float('wasted')->default(0);
        //     $table->float('issued_out')->default(0);
        //     $table->float('issued_in')->default(0);
        //     $table->float('closing')->default(0);
        //     $table->float('requested')->default(0);
        //     $table->tinyInteger('synched')->default(0)->comment("0:Awaiting synching; 1:Synched; 2:Update awaiting synching;");
        //     $table->date('datesynched')->nullable();
        //     $table->softDeletes();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consumption_detail_breakdowns');
    }
}
