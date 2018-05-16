<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMothersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mothers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('original_mother_id')->unsigned()->index();
            $table->string('ccc_no', 50)->nullable();
            // $table->integer('fcode')->unsigned()->nullable();
            $table->integer('facility_id')->unsigned()->index();
            $table->tinyInteger('hiv_status')->unsigned()->nullable();
            $table->tinyInteger('synched')->default(0);
            $table->date('datesynched')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'ccc_no'], 'mother_unq_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mothers');
    }
}
