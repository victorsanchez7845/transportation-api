<?php

namespace App\Services;

use Airbrake\Notifier;

class AirbrakeService
{
    protected $notifier;

    public function __construct()
    {
        // Initialize the Airbrake Notifier
if (config('services.airbrake.id') && config('services.airbrake.key')) {
    $this->notifier = new Notifier([
        'projectId' => config('services.airbrake.id'),
        'projectKey' => config('services.airbrake.key'),
        'environment' => 'production',
    ]);
}

    /**
     * Report an exception to Airbrake.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function report(\Throwable $exception)
    {
        $this->notifier->notify($exception);
    }

    /**
     * Report a custom error message to Airbrake.
     *
     * @param string $message
     * @return void
     */
    public function reportMessage(string $message)
    {
        $this->notifier->notify(new \Exception($message));
    }
}
