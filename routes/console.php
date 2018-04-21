<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('synch:test {limit}', function () {
	ini_set("memory_limit", "-1");
	$limit = $this->argument('limit');
    $this->info($limit);
    $samples = \App\OldSampleView::limit($limit)->offset(0)->get();
    $viralsamples = \App\OldViralsampleView::limit($limit)->offset(0)->get();
    $this->info($samples->first());
    $this->info($viralsamples->first());
})->describe('Test synch limit.');

Artisan::command('synch:eid', function () {
    $str = \App\Synch::synch_eid();
    $this->info($str);
})->describe('Synch Eid results.');

Artisan::command('synch:vl', function () {
    $str = \App\Synch::synch_vl();
    $this->info($str);
})->describe('Synch Vl results.');
