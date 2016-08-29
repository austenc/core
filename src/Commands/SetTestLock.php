<?php namespace Hdmaster\Core\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SetTestLock extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tmu:AutoLockTests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-locks tests based on settings in config/testevents.php.';

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
        // get time constraints from config
        // (paper vs web tests will lock at different times)
        $paperLock    = strtotime(Config::get('commands.set_test_lock.paper_test_lock'));
        $nonpaperLock = strtotime(Config::get('commands.set_test_lock.nonpaper_lock'));

        // get all events that need locking
        // (paper events must be locked a few days before to allow paper test mail delivery)
        $events = Testevent::where(function ($q) use ($paperLock) {
            $q->where('test_date', '<=', date("Y-m-d", $paperLock))
                ->where('test_date', '>=', date("Y-m-d"))
                ->where('is_paper', '=', 1)
                ->where('locked', '=', 0);
        })->orWhere(function ($qw) use ($nonpaperLock) {
            $qw->where('test_date', '=', date("Y-m-d", $nonpaperLock))
                   ->where('locked', '=', 0)
                   ->where('is_paper', '=', 0);
        })->get();

        // logging
        $filePath = Config::get('log.path') . "events/";
        // Check directory exists
        if (! is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $fileName = $filePath . date("Ymd") . "lock.txt";
        $logFile = fopen($fileName, "a");

        if (! $events->isEmpty()) {
            foreach ($events as $evt) {
                // paper or web event?
                $paper = $evt->is_paper ? "Paper" : "Web";

                // ensure event is lockable
                // user has permission, no null assigned tests, etc..
                if (! $evt->lockable) {
                    fwrite($logFile, "Test ID: " . $evt->id . " Facility ID: " . $evt->facility_id . " Test Date: " . $evt->test_date . " Test Type: " . $paper . " - EVENT NOT LOCKABLE\n");
                    continue;
                }

                $evt->locked = true;
                $evt->save();

                // If an event is paper, automatically release the tests too!
                if ($evt->is_paper) {
                    $evt->releaseTests();
                }

                fwrite($logFile, "Test ID: " . $evt->id. " Facility ID: " . $evt->facility_id . " Test Date: " . $evt->test_date . " Test Type: " . $paper . " LOCKED\n");
            }
        } else {
            fwrite($logFile, "No locking of tests required.\n");
        }

        fclose($logFile);
    }
}
