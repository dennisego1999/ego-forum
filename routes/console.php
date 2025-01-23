<?php

// use Illuminate\Cache\Console\PruneStaleTagsCommand;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Queue\Console\PruneFailedJobsCommand;
use Illuminate\Support\Facades\Schedule;
use Laravel\Telescope\Console\PruneCommand as TelescopePruneCommand;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

// Vendor models to be pruned
$vendorModels = [
    HealthCheckResultHistoryItem::class,
    MonitoredScheduledTaskLogItem::class,
];

// Maintenance
// Schedule::command(PruneStaleTagsCommand::class, ['redis'])->hourly();
Schedule::command(TelescopePruneCommand::class)->daily();
Schedule::command(PruneCommand::class)->daily();
Schedule::command(PruneCommand::class, ['--model' => $vendorModels])->daily();
Schedule::command(PruneFailedJobsCommand::class, ['--hours' => 24 * 30])->weekly();
