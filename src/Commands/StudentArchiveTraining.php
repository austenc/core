<?php namespace Hdmaster\Core\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class StudentArchiveTraining extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tmu:StudentArchiveTraining';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-archiving of expired Student Training.';

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
        $trainArchive = StudentTraining::where('expires', '<', date("Y-m-d"))->whereNull('archived_at')->get();

        $filePath = Config::get('log.path') . "trainings/";
        // Check directory exists
        if (! is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $fileName = $filePath . date("Ymd") . "archive.txt";
        $logFile  = fopen($fileName, "a");

        if (! $trainArchive->isEmpty()) {
            foreach ($trainArchive as $t) {
                $t->archived_at = date("Y-m-d H:i:s");
                $t->save();
                DB::table('instructor_student')->where('instructor_student.student_id', '=', $t->id)->update(['active' => false]);
                fwrite($logFile, "Student Training ID: " . $t->id . " Training End Date: " . $t->ended . " Expire Date: " . $t->expires . " Archived At: " . date("Y-m-d H:i:s") . "\n");
            }
        } else {
            fwrite($logFile, "No Student Training Expired.\n");
        }
        fclose($logFile);
    }
}
