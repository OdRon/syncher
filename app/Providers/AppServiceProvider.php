<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Sample;
use App\Viralsample;

use App\Observers\SampleObserver;
use App\Observers\ViralsampleObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Sample::observe(SampleObserver::class);
        Viralsample::observe(ViralsampleObserver::class);
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
