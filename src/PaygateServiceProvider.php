<?php

namespace Obrainwave\Paygate;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Obrainwave\Paygate\Services\PaymentService;
use Obrainwave\Paygate\Contracts\PaymentServiceInterface;
use Obrainwave\Paygate\Commands\InstallPaygateCommand;

class PaygateServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('paygate')
            ->hasConfigFile()
            ->hasMigrations([
                'create_payments_table'
            ])
            ->hasRoutes(['web', 'api'])
            ->hasCommand(InstallPaygateCommand::class);
    }

    public function register(): void
    {
        parent::register();

        // Register the payment service
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->singleton(PaymentService::class);
    }

    public function boot(): void
    {
        parent::boot();

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'paygate-migrations');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/paygate.php' => config_path('paygate.php'),
        ], 'paygate-config');
    }
}