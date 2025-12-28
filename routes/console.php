<?php

use App\Jobs\SendDailySalesReportJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(SendDailySalesReportJob::class)
    ->dailyAt('20:00')
    ->timezone('UTC');
