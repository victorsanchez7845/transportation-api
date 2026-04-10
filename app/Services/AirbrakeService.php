<?php

namespace App\Services;

use Airbrake\Notifier;

class AirbrakeService
{
    protected $notifier;

    public function __construct()
    {
        if (config('services.airbrake.id') && config('services.airbrake.key')) {
            $this->notifier = new Notifier([
                'projectId' => config('services.airbrake.id'),
                'projectKey' => config('services.airbrake.key'),
                'environment' => 'production',
            ]);
        }
    }

    public function report(\Throwable $exception)
    {
        if ($this->notifier) {
            $this->notifier->notify($exception);
        }
    }

    public function reportMessage(string $message)
    {
        if ($this->notifier) {
            $this->notifier->notify(new \Exception($message));
        }
    }
}
