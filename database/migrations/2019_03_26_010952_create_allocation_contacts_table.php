<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllocationContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allocation_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lab_id');
            $table->text('address');
            $table->string('contact_person');
            $table->string('telephone');
            $table->string('contact_person_2')->nullable();
            $table->string('telephone_2')->nullable();
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
        Schema::dropIfExists('allocation_contacts');
    }
}
