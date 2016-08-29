<?php namespace Hdmaster\Core\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ExpireInstructors extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tmu:ExpireInstructors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron to expire and lock accounts beyond the expire date.';

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
        $instructors = Instructor::where('expires', '<', date("Y-m-d"))->whereNotNull('expires')->get();

        $filePath = Config::get('log.path') . "instructors/";
        // Check directory exists
        if (! is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $fileName = $filePath . date("Ymd") . "expires.txt";
        $logFile  = fopen($fileName, "a");

        if (! $instructors->isEmpty()) {
            foreach ($instructors as $i) {
                // call archive function
                // not using soft-delete anymore
                // instructors.status = 'archive'
                $i->archive();

                // log
                fwrite($logFile, $i->full_name . ", user_id: " . $i->user_id . ", EXPIRED\n");
            }
        } else {
            fwrite($logFile, "No Instructors needed to be expired.\n");
        }

        fclose($logFile);
    }
}
