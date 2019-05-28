<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShortCodeQueriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('short_code_queries', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('testtype')->comment('1: EID; 2:VL')->nullable();
            $table->string('phoneno', 30)->nullable();
            $table->string('message', 50)->nullable();
            $table->string('mflcode', 10)->nullable();
            $table->string('samplecode', 30)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->dateTime('dateresponded')->nullable();
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
        Schema::dropIfExists('short_code_queries');
    }
}
