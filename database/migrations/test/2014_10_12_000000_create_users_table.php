<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('user_type_id');
            $table->tinyInteger('lab_id');
            $table->Integer('facility_id');
            $table->string('surname');
            $table->string('oname');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->Integer('level')->nullable();
            $table->string('telephone')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->string('old_password')->nullable();
            $table->timestamp('last_access')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
