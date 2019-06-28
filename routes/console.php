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

Artisan::command('alere', function () {
    \App\Poc::alereq();
})->describe('Display an inspiring quote');

Artisan::command('clean:no-gender {type}', function ($type) {
    \App\Synch::correct_no_gender($type);
})->describe('Fix no genders using data from the lab.');

Artisan::command('clean:no-dob {type}', function ($type) {
    \App\Synch::correct_no_dob($type);
})->describe('Fix no dobs using data from the lab.');

Artisan::command('report:hei-partner {contact_id?}', function ($contact_id=null) {
    $str = \App\Report::eid_partner($contact_id);
    $this->info($str);
})->describe('Send hei follow up report for partners.');

Artisan::command('report:hei-county {contact_id?}', function ($contact_id=null) {
    $str = \App\Report::eid_county($contact_id);
    $this->info($str);
})->describe('Send hei follow up report for counties.');

Artisan::command('report:suppression-partner {contact_id?}', function ($contact_id=null) {
    $str = \App\Report::vl_partner($contact_id);
    $this->info($str);
})->describe('Send suppression follow up report for partners.');

Artisan::command('report:suppression-county {contact_id?}', function ($contact_id=null) {
    $str = \App\Report::vl_county($contact_id);
    $this->info($str);
})->describe('Send suppression follow up report for counties.');


Artisan::command('delete-pdfs', function(){
    $str = \App\Report::delete_folder(storage_path('app/hei'));
    $str = \App\Report::delete_folder(storage_path('app/suppression'));
    $this->info($str);
})->describe('Delete pdfs from hard drive.');



Artisan::command('ages {type}', function ($type) {
    $str = \App\Common::set_age($type);
})->describe('Set age for samples that have a dob but no age.');

Artisan::command('facilities', function () {
    $str = \App\Common::add_missing_facilities();
})->describe('Add facilities that are in national DB but are not in API DB.');

Artisan::command('facilities:labs', function () {
    $str = \App\Common::facility_lab();
})->describe('Set favoured lab for each facility.');


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

Artisan::command('copy:users', function(){
    $str = \App\Copier::copy_users();
    $this->info($str);
})->describe('Copy Patients.');

Artisan::command('patient:assign', function(){
    $str = \App\Copier::assign_patient_statuses();
    $this->info($str);
})->describe('Assign patient statuses');

Artisan::command('dispatch:mlab', function(){
    $str = \App\Misc::send_to_mlab_eid();
    $str = \App\Misc::send_to_mlab_vl();
    $this->info($str);
})->describe('Send WRP results to MLAB.');

Artisan::command('get:mlab', function(){
    $str = \App\Misc::get_mlab_facilities();
    $this->info($str);
})->describe('Get Mlab facilities.');

Artisan::command('send:emails', function(){
    $str = \App\Report::send_communication();
    $this->info($str);
})->describe('Send pending emails.');

Artisan::command('test:email', function(){
    $str = \App\Report::test_email();
    $this->info($str);
})->describe('Send test email.');

Artisan::command('test:connection', function(){
    $str = \App\Synch::test_connection();
    $this->info($str);
})->describe('Check connection to the labs.');

Artisan::command('synch:allocations', function(){
    $str = \App\Synch::synch_allocations();
    $this->info($str);
})->describe('Synch Allocations');

Artisan::command('send:negatives2018', function(){
    $str = \App\Random::negatives_report();
    $this->info($str);
})->describe('Send Negatives');
// Artisan::command('testPassword:email', function(){
//     $str = \App\Report::send_password();
//     $this->info($str);
// })->describe('Send Email to all users to tell them of their new passwords');

// Artisan::command('deactivate:users', function(){
//     $str = \App\Copier::deactivate_old_users();
//     $this->info($str);
// })->describe('Deactivaing inactive users');

Artisan::command('recreate:sample_complete_view', function(){
    $str = \App\Random::run_sample_complete_view();
    $this->info($str);
})->describe('Recreate samples view');

Artisan::command('check:mb', function(){
    $str = \App\Random::checkMbNo();
    $this->info($str);
})->describe('Get MB No');