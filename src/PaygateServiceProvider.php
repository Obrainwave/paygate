<?php

namespace Obrainwave\Paygate;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PaygateServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         */
        $package
            ->name('paygate')
            ->hasConfigFile();
            // ->hasViews()
            // ->hasRoutes(['web'])
            // ->hasCommand(SkeletonCommand::class);
    }
}