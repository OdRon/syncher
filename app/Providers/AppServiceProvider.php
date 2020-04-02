<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Sample;
use App\Viralsample;

use App\Observers\SampleObserver;
use App\Observers\ViralsampleObserver;


use App\Patient;
use App\Viralpatient;
use App\CovidPatient;

use App\Observers\PatientObserver;
use App\Observers\ViralpatientObserver;
use App\Observers\CovidPatientObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(env('APP_SECURE_URL')) \Illuminate\Support\Facades\URL::forceScheme('https');
        
        CovidPatient::observe(CovidPatientObserver::class);
        
        // Sample::observe(SampleObserver::class);
        // Viralsample::observe(ViralsampleObserver::class);
        
        // Patient::observe(PatientObserver::class);
        // Viralpatient::observe(ViralpatientObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
