<?php

namespace Obrainwave\Paygate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallPaygateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'paygate:install {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Install Paygate package';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Installing Paygate package...');

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'paygate-migrations',
            '--force' => $this->option('force')
        ]);

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'paygate-config',
            '--force' => $this->option('force')
        ]);

        // Run migrations
        if ($this->confirm('Do you want to run the migrations now?')) {
            $this->call('migrate');
        }

        // Create .env entries
        $this->createEnvEntries();

        $this->info('Paygate package installed successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Configure your payment gateway credentials in .env');
        $this->line('2. Run: php artisan config:cache');
        $this->line('3. Start using Paygate in your application');
    }

    /**
     * Create .env entries
     */
    protected function createEnvEntries()
    {
        $envFile = base_path('.env');
        
        if (!File::exists($envFile)) {
            $this->warn('.env file not found. Please add the following to your .env file:');
            $this->displayEnvEntries();
            return;
        }

        $envContent = File::get($envFile);
        $newEntries = $this->getEnvEntries();

        foreach ($newEntries as $entry) {
            if (strpos($envContent, $entry) === false) {
                $envContent .= "\n" . $entry;
            }
        }

        File::put($envFile, $envContent);
        $this->info('Added Paygate configuration to .env file');
    }

    /**
     * Display env entries
     */
    protected function displayEnvEntries()
    {
        $entries = $this->getEnvEntries();
        foreach ($entries as $entry) {
            $this->line($entry);
        }
    }

    /**
     * Get env entries
     */
    protected function getEnvEntries(): array
    {
        return [
            '',
            '# Paygate Configuration',
            'PAYGATE_DEFAULT_CURRENCY=NGN',
            'PAYGATE_DEFAULT_PROVIDER=paystack',
            'PAYGATE_ENABLE_LOGGING=true',
            'PAYGATE_STORE_PAYMENTS=true',
            '',
            '# Paystack Configuration',
            'PAYSTACK_PUBLIC_KEY=',
            'PAYSTACK_SECRET_KEY=',
            'PAYSTACK_WEBHOOK_SECRET=',
            '',
            '# GTPay Configuration',
            'GTPAY_PUBLIC_KEY=',
            'GTPAY_SECRET_KEY=',
            'GTPAY_WEBHOOK_SECRET=',
            '',
            '# Flutterwave Configuration',
            'FLUTTERWAVE_PUBLIC_KEY=',
            'FLUTTERWAVE_SECRET_KEY=',
            'FLUTTERWAVE_WEBHOOK_SECRET=',
            '',
            '# Monnify Configuration',
            'MONNIFY_API_KEY=',
            'MONNIFY_SECRET_KEY=',
            'MONNIFY_WEBHOOK_SECRET=',
        ];
    }
}
