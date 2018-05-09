<?php

namespace Yugo\SMSGateway\Providers;

use Illuminate\Support\ServiceProvider;
use Yugo\SMSGateway\Interfaces\SMS;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            realpath(__DIR__.'/../config/message.php') => config_path('message.php'),
        ]);

        $this->loadMigrationsFrom(realpath(__DIR__.'/../database/migrations/'));
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $className = studly_case(strtolower(config('message.vendor', 'smsgatewayme')));
        $classPath = '\Yugo\SMSGateway\Vendors\\'.$className;

        if (!class_exists($classPath)) {
            abort(500, sprintf(
                'SMS vendor %s is not available.',
                $className
            ));
        }

        app()->bind(SMS::class, $classPath);
    }
}
