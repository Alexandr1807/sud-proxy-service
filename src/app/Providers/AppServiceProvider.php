<?php

namespace App\Providers;

use App\Services\SudClient;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use GuzzleHttp\Client as GuzzleClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем SudClient в контейнере:
        $this->app->bind(SudClient::class, function ($app) {
            // Здесь мы создаём сам GuzzleClient с нужными опциями
            $guzzle = new GuzzleClient([
                'base_uri' => config('services.my_sud.base_uri'),
                'timeout'  => 5.0,
            ]);

            return new SudClient(
                $guzzle,
                config('services.my_sud.api_key'),
            );
        });
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::enablePasswordGrant();
//        Passport::routes();
    }
}
