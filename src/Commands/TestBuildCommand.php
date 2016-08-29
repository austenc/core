<?php namespace Hdmaster\Core\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \Config;

class TestBuildCommand extends Command
{

    // directory and pointer to dump.sql for tests
    private $fileDir;
    private $filePath;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'test:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Builds / sets up testing database for use with codeception.';

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
        // get the base path from the --path option
        $path = base_path().'/'.ltrim($this->option('path'), '/');

        $this->fileDir  = rtrim($path, '/').'/_data';
        $this->filePath = $this->fileDir.'/dump.sql';


        // Start the command
        $this->info('Building testing database...');
        $connection = Config::get('database.default');
        $user       = Config::get('database.connections.'.$connection.'.username');
        $pass       = Config::get('database.connections.'.$connection.'.password');
        $otherDb    = Config::get('database.connections.'.$connection.'.database');

        // make sure the file exists to dump to
        echo system('mkdir -p '.$this->fileDir.' && touch '.$this->filePath);

        // dump the main db, replace the file with > operator
        echo system('mysqldump -u'.$user.' -p'.$pass.' '.$otherDb.' > '.$this->filePath);

        // now clear the database and recreate it
        echo system('mysql -u'.$user.' -p'.$pass.' -e "drop database codeception; create database codeception;"');
        // use dumpfile to populate database initially
        echo system('mysql -u'.$user.' -p'.$pass.' codeception < '.$this->filePath);

        $this->info('Testing database dump complete.');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            // array('example', InputArgument::OPTIONAL, 'An example argument.'),
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
            array('path', null, InputOption::VALUE_OPTIONAL, 'An optional path to the tests folder (relative to app root)', '/tests')
            // array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }
}
