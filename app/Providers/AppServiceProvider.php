<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\Mailcoach\Domain\Automation\Models\Automation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        $this->bootRoute();
        $this->bootAutomationActivityLog();
    }

    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Route::mailcoach('/');
    }

    /**
     * Log automation create/update to the activity log via plain Eloquent
     * model events, rather than overriding config('mailcoach.models.automation')
     * with a LogsActivity subclass: Mailcoach resolves its own AutomationPolicy
     * through Laravel's namespace-based policy guessing (Models\Automation ->
     * Policies\AutomationPolicy), which breaks silently for any overridden model
     * class living outside that namespace (killed the "Automations" nav item and
     * 403'd the Settings/Actions/Run tabs when tried).
     */
    public function bootAutomationActivityLog(): void
    {
        $tracked = ['name', 'status', 'email_list_id', 'interval', 'repeat_enabled', 'repeat_only_after_halt'];

        Automation::created(function (Automation $automation) use ($tracked) {
            activity('automation')
                ->performedOn($automation)
                ->event('created')
                ->withChanges([
                    'attributes' => $automation->only($tracked),
                ])
                ->log('created');
        });

        Automation::updated(function (Automation $automation) use ($tracked) {
            $changed = array_intersect($tracked, array_keys($automation->getChanges()));

            if (empty($changed)) {
                return;
            }

            activity('automation')
                ->performedOn($automation)
                ->event('updated')
                ->withChanges([
                    'attributes' => $automation->only($changed),
                    'old' => collect($changed)->mapWithKeys(fn (string $key) => [$key => $automation->getOriginal($key)])->all(),
                ])
                ->log('updated');
        });
    }
}
