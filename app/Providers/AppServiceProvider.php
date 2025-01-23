<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Enforce safety and performance checks
        DB::prohibitDestructiveCommands(app()->isProduction());
        Model::preventLazyLoading(! app()->isProduction());
        // Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        // Disable wrapping API resources
        JsonResource::withoutWrapping();

        /**
         * @see https://laravel.com/docs/eloquent-relationships#custom-polymorphic-types
         */
        Relation::enforceMorphMap([
            'team' => Team::class,
            'user' => User::class,
        ]);

        // Overwrite who receives mails outside production when filled
        if (! App::isProduction() && ($mail = config('mail.always_to'))) {
            Mail::alwaysTo($mail);

            AboutCommand::add('Environment', static fn () => ['Mail always to' => $mail]);
        }

        $this->registerHttpMacros();
    }

    private function registerHttpMacros(): void
    {
        Http::macro('ohDear', function () {
            return Http::baseUrl('https://ohdear.app/api')
                ->withToken(config('schedule-monitor.oh_dear.api_token'));
        });
    }
}
