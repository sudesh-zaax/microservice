<?php

namespace App\Providers;

use App\Interfaces\AuthenticationRepositoryInterface;
use App\Repositories\AuthenticationRepository;
use App\Services\PolicyServiceClient;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PolicyServiceClient::class, function ($app) {
            return new PolicyServiceClient();
        });
        $this->app->bind(AuthenticationRepositoryInterface::class, AuthenticationRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(now()->addDays(value: 15));
        //Passport::tokensExpireIn(now()->addMinutes(3));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
