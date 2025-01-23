<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class OhDearStopMaintenancePeriodCommand extends Command
{
    protected $signature = 'oh-dear:stop-maintenance-period';

    protected $description = 'Stop maintenance period in Oh Dear.';

    public function handle(): int
    {
        $siteId = config('schedule-monitor.oh_dear.site_id');

        $this->stopMaintenancePeriod($siteId);
        $this->unsnoozeApplicationHealth($siteId);

        return static::SUCCESS;
    }

    private function stopMaintenancePeriod(int $siteId): void
    {
        Http::ohDear()->retry(3)->post("sites/$siteId/stop-maintenance");
    }

    private function unsnoozeApplicationHealth(int $siteId): void
    {
        // Fetch the checks for the site from Oh Dear
        $result = Http::ohDear()->get("sites/$siteId");
        $checks = collect($result->json('checks') ?: []);

        // Find the application health check
        $applicationHealth = $checks->firstWhere('type', 'application_health');

        if ($applicationHealth) {
            Http::ohDear()->retry(3)->post("checks/{$applicationHealth['id']}/unsnooze");
        }
    }
}
