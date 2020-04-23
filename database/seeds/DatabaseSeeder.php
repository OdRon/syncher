<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        // $this->call(KitsSeeder::class);
        // $this->call(GeneralConsumablesSeeder::class);
        // $this->call(AllocationContactSeeder::class);
        // $this->call(ReportsSeeder::class);
        $this->call(CovidKitsSeeder::class);
    }
}
