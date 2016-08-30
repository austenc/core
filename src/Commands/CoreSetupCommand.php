<?php namespace Hdmaster\Core\Commands;

use Illuminate\Console\Command;

class CoreSetupCommand extends Command
{
    protected $signature = 'tmu:setup';
    protected $description = 'Setup TMU Core assets and migrations.';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        // Publish assets
        $this->call('vendor:publish', [
            '--provider' => 'Hdmaster\Core\CoreServiceProvider',
            '--tag'   => ['public'],
            '--force' => true
        ]);

        // Publish migrations, run them, and run seeders
        $this->call('tmu:db', ['--all' => true]);

        $this->info('Assets published and database setup.');
        $this->info('Please run `npm install` and then `gulp` from project root.');
    }
}
