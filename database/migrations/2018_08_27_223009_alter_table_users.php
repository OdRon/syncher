<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('users', function (Blueprint $table) {
        //     $table->string('old_password')->nullable();
        //     $table->string('old_password')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // if (Schema::hasColumn('users', 'name'))
        // {
        //     Schema::table('users', function (Blueprint $table)
        //     {
        //         $table->dropColumn('name');
        //     });
        // }
    }
}
