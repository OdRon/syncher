<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNhrlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nhrl', function (Blueprint $table) {
            $table->increments('id');
            $table->string('c_posted')->nullable();
            $table->string('label_id')->nullable();
            $table->string('login_date')->nullable();
            $table->string('patient')->nullable();
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
        Schema::dropIfExists('nhrl');
    }
}
