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

Artisan::command('report:hei-partner {contact_id?}', function ($contact_id=null) {
    $str = \App\Report::eid_partner($contact_id);
    $this->info($str);
})->describe('Send hei follow up report for partners.');


Artisan::command('copy:test {limit}', function () {
	ini_set("memory_limit", "-1");
	$limit = $this->argument('limit');
    $this->info($limit);
    $samples = \App\OldSampleView::limit($limit)->offset(0)->get();
    $viralsamples = \App\OldViralsampleView::limit($limit)->offset(0)->get();
    $this->info($samples->first());
    $this->info($viralsamples->first());
})->describe('Test copy limit.');

Artisan::command('copy:eid', function () {
    $str = \App\Copier::copy_eid();
    $this->info($str);
})->describe('Copy Eid results.');

Artisan::command('copy:vl', function () {
    $str = \App\Copier::copy_vl();
    $this->info($str);
})->describe('Copy Vl results.');

Artisan::command('copy:worksheet', function () {
    $str = \App\Copier::copy_worksheet();
    $this->info($str);
})->describe('Copy worksheets.');

Artisan::command('patient:assign', function(){
    $str = \App\Copier::assign_patient_statuses();
    $this->info($str);
})->describe('Assign patient statuses');
