<?php namespace Hdmaster\Core\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CronCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tmu:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up the cron job for Dispatcher package (command scheduling).';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Setting up cronjob for Dispatcher...');

        // Our basic 'run all the time' dispatcher cronjob
        $cron = '" * * * * * php $PWD/artisan scheduled:run >> /dev/null 2>&1 "';

        // does this exist in the current crontab settings?
        $cmd = 'sudo crontab -l | sudo grep -iq  '. $cron . ' || '

        // if it doesn't exist run teh below (after '||' runs if grep fails)
        . 'sudo crontab -l | { cat; echo ' . $cron . '; } | sudo crontab - ';
        // append to current cronjobs ^^^^^^^^^^^^^^

        echo system($cmd);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
