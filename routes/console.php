<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Backup Database Harian untuk Lingkungan Shared Hosting / Production
Schedule::command('madrasah:backup')->dailyAt('01:00');
