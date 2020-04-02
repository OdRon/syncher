<?php

namespace App\Observers;

use App\CovidSample;

class CovidSampleObserver
{
    /**
     * Handle the covid sample "created" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function created(CovidSample $covidSample)
    {
        //
    }

    /**
     * Handle the covid sample "updated" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function updated(CovidSample $covidSample)
    {
        //
    }

    /**
     * Handle the covid sample "deleted" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function deleted(CovidSample $covidSample)
    {
        //
    }

    /**
     * Handle the covid sample "restored" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function restored(CovidSample $covidSample)
    {
        //
    }

    /**
     * Handle the covid sample "force deleted" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function forceDeleted(CovidSample $covidSample)
    {
        //
    }
}
