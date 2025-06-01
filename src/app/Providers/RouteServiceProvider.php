<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Этот маршрут будет применяться к URL по умолчанию при генерации URL.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Содержит namespace для ваших контроллеров (если вы хотите, чтобы Laravel автоматически
     * дописывал префикс App\Http\Controllers\ перед вашими маршрутами).
     *
     * Если вы НЕ хотите, чтобы Laravel автоматически подставлял namespace, можно оставить null.
     * Тогда в файлах маршрутов придётся указывать полный путь (например, [App\Http\Controllers\MyController::class, ...]).
     *
     * @var string|null
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * Bootstrap any application services.
     *
     * Здесь можно настраивать RateLimiter и т. п.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // 1) Веб-маршруты (middleware 'web', файл routes/web.php)
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            // 2) API-маршруты (middleware 'api', файл routes/api.php)
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));
        });
    }

    /**
     * Настройка рейт-лимитов (чаще всего для API).
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // Пример: лимит 60 запросов в минуту для каждого уникального token|ip
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
