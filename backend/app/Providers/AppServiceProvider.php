<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        // 開発環境では lazy loading を防止する
        Model::preventLazyLoading(! app()->isProduction());

        /**
         * @param  Request  $request
         * @return Limit
         */
        $loginLimiter = function (Request $request): Limit {
            /** @var ?string $email */
            $email = $request->input('email');

            return Limit::perMinute(5)->by(
                strtolower(trim(is_string($email) ? $email : '')).'|'.$request->ip()
            );
        };

        RateLimiter::for('login', $loginLimiter);
    }
}
