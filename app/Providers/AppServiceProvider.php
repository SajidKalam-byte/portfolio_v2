<?php

namespace App\Providers;

use App\Models\User;
use App\Services\PortfolioContent;
use App\Support\ResumeFile;
use App\Support\Settings;
use App\Support\Site;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Settings::class);
        $this->app->singleton(ResumeFile::class);
        $this->app->singleton(Site::class);
        $this->app->singleton(PortfolioContent::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Absolute URLs (canonical, sitemap, Open Graph) always use APP_URL in production,
        // even when the host proxies requests or serves several hostnames.
        if ($this->app->isProduction() && filled(config('app.url'))) {
            URL::forceRootUrl(config('app.url'));

            if (str_starts_with(config('app.url'), 'https://')) {
                URL::forceScheme('https');
            }
        }

        Model::shouldBeStrict(! $this->app->isProduction());

        Gate::define('access-admin', fn (User $user) => $user->isAdmin());

        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('admin-login', fn (Request $request) => [
            Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perHour(30)->by($request->ip()),
        ]);

        View::composer('*', function ($view) {
            $view->with('site', $this->app->make(Site::class));
        });
    }
}
