<?php namespace Hdmaster\Core\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TestPublishCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'test:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish tests from the core package to use / diverge / extend by client.';

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
        $this->info('Publishing tests from core...');

        $newDir   = base_path();
        $pkgTests = base_path().'/vendor/hdmaster/core/tests';

        if (file_exists($pkgTests)) {
            // found the tests to move, make sure new dir exists
            echo system('mkdir -p '.$newDir);

            // Copy the files from core, but exclude some tests:
            //   - Grabs a list of tests to exclude from .gitignore file
            //   - Passes that list into rsync which syncs package tests with app root
            echo system('sed -e "1,/# excludeTests/d" .gitignore | sed "s/\!//" - | rsync -avz --exclude-from=- '.$pkgTests.' '.$newDir);

            // might as well build codeception
            $this->info('Building codeception environment');
            echo system('cd '.base_path().' && vendor/bin/codecept build');
        } else {
            $this->error('Tests directory not found. Make sure the core package is installed via composer.');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            // array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            // array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }
}
