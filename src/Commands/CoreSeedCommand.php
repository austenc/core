<?php namespace Hdmaster\Core\Commands;

use Illuminate\Console\Command;

class CoreSeedCommand extends Command
{
    protected $signature = 'tmu:seed';
    protected $description = 'Run the core TMU seeders';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('db:seed', [
            '--class' => 'Hdmaster\Core\Seeds\DatabaseSeeder'
        ]);
    }
}
