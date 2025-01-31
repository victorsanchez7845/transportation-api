<?php

namespace App\Providers;
use Airbrake\Instance;
use Airbrake\ErrorHandler;
use Airbrake\Notifier;
use Illuminate\Support\ServiceProvider;
use App\Services\AirbrakeService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AirbrakeService::class, function () {
            return new AirbrakeService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {   
        //Todas estas líneas de código, fueron agregadas para obtener los errores de la App y mandarlas a Airbrake.
        // Configura la instancia de Airbrake
        /*$notifier = new Notifier([
            'projectId' => config('services.airbrake.id'),
            'projectKey' => config('services.airbrake.key'),
        ]);

        // Establece la instancia en Airbrake\Instance
        Instance::set($notifier);

        // Crea un manejador de errores y regístralo
        $handler = new ErrorHandler($notifier);
        $handler->register();*/

    }
}
