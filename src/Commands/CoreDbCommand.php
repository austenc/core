<?php namespace Hdmaster\Core\Commands;

use Illuminate\Console\Command;

class CoreDbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmu:db 
        { --all : Shortcut to force publish, reset, and seed all at once. }
        { --force : Force the migrations to be overridden when published. }
        { --reset : Runs artisan migrate:reset before the core migrations. }
        { --seed : Run the core package database seeders. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the core migrations to /database/migrations and migrate';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {   
        // grab any input options
        $options = $this->option(); 

        if($options['all'] === true) {
            $options['force'] = true;
            $options['reset'] = true;
            $options['seed']  = true;
        }

        $publishOptions = [
            '--provider' => 'Hdmaster\Core\CoreServiceProvider',
            '--tag'      => ['migrations'],
        ];

        // If 'force' flag set, add it to publish command
        if ($options['force']) {
            $publishOptions['--force'] = true;
        }

        // Publish the package migrations
        $this->info('Publishing core migrations...');
        $this->call('vendor:publish', $publishOptions);

        // Reset the database first? 
        if ($options['reset']) {
            $this->call('migrate:reset');
        }

        // Run the migrations
        $this->call('migrate');

        // Seed the database?
        if ($options['seed']) {
            $this->call('tmu:seed');
        }
    }
}
