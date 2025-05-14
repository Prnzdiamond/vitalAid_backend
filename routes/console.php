<?php

use App\Services\EventService;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Console\ClosureCommand;
use App\Jobs\ConsultationJobs\CheckInactivityTimeout;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CheckInactivityTimeout())->everyMinute();

Schedule::call(function () {
    // Enhanced logging to help with debugging
    Log::info("Starting scheduled event status check at " . now()->toDateTimeString());

    try {
        // Use dependency injection to get the service
        app()->make(EventService::class)::checkAndCompleteEvents();
        Log::info("Scheduled event status check completed successfully");
    } catch (\Exception $e) {
        Log::error("Error in scheduled event status check: " . $e->getMessage());
    }
})
    ->name('check_events')                 // Name the job for easier identification
    ->everyMinute()                        // Run every minute (more reasonable than every 5 seconds)
    ->withoutOverlapping(10)               // Prevent overlap with a 10-minute timeout
    ->onOneServer();                       // Only run on one server in multi-server setups