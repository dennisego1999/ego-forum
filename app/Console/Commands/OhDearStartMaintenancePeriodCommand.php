<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class OhDearStartMaintenancePeriodCommand extends Command
{
    protected $signature = 'oh-dear:start-maintenance-period';

    protected $description = 'Start maintenance period in Oh Dear.';

    public function handle(): int
    {
        $siteId = config('schedule-monitor.oh_dear.site_id');

        $this->startMaintenancePeriod($siteId);
        $this->snoozeApplicationHealth($siteId);

        return static::SUCCESS;
    }

    private function startMaintenancePeriod(int $siteId): void
    {
        Http::ohDear()->retry(3)->post("sites/$siteId/start-maintenance");
    }

    private function snoozeApplicationHealth(int $siteId): void
    {
        // Fetch the checks for the site from Oh Dear
        $result = Http::ohDear()->get("sites/$siteId");
        $checks = collect($result->json('checks') ?: []);

        // Find the application health check
        $applicationHealth = $checks->firstWhere('type', 'application_health');

        if ($applicationHealth) {
            Http::ohDear()->retry(3)->post("checks/{$applicationHealth['id']}/snooze", [
                'minutes' => 60,
            ]);
        }
    }
}
