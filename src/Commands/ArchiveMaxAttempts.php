<?php namespace Hdmaster\Core\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ArchiveMaxAttempts extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tmu:ArchiveMaxAttempts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive skill and knowledge test attempts that have reached max_attempts for Exam type.';
    

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        // max attempts maps
        $skillMaxAttempts = Skillexam::lists('max_attempts', 'id')->all();
        $knowMaxAttempts  = Exam::lists('max_attempts', 'id')->all();

        // get all non-archived knowledge/skill attempts
        $knowledge   = Testattempt::updatedWithinLastDay()->notArchived()->get();
        $skill       = Skillattempt::updatedWithinLastDay()->notArchived()->get();
        $allAttempts = $knowledge->merge($skill);

        // no non-archived test attempts updated within the last day?
        if ($allAttempts->isEmpty()) {
            return;
        }

        $studentIds = $allAttempts->unique('student_id')->lists('student_id')->all();
        $students = Student::with(['failedAttempts', 'failedSkillAttempts'])->whereIn('id', $studentIds)->get();
        if (! $students->isEmpty()) {
            foreach ($students as $student) {
                // STUDENT FAILED KNOWLEDGE ATTEMPTS
                if (! $student->failedAttempts->isEmpty()) {
                    // get counts per each exam
                    $examCounts = [];
                    foreach ($student->failedAttempts as $attempt) {
                        if (! array_key_exists($attempt->exam_id, $examCounts)) {
                            $examCounts[$attempt->exam_id] = 1;
                        } else {
                            $examCounts[$attempt->exam_id]++;
                        }
                    }

                    // check if need archiving
                    foreach ($examCounts as $examId => $count) {
                        $currMaxAttempts = $knowMaxAttempts[$examId];

                        // reached max failed attempts!
                        if ($count >= $currMaxAttempts) {
                            $student->archive();
                            continue 2;    // goto next student
                        }
                    }
                }

                // STUDENT FAILED SKILL ATTEMPTS
                if (! $student->failedSkillAttempts->isEmpty()) {
                    // get counts per each exam
                    $skillCounts = [];
                    foreach ($student->failedSkillAttempts as $attempt) {
                        if (! array_key_exists($attempt->skillexam_id, $skillCounts)) {
                            $skillCounts[$attempt->skillexam_id] = 1;
                        } else {
                            $skillCounts[$attempt->skillexam_id]++;
                        }
                    }

                    // check if need archiving
                    foreach ($skillCounts as $skillId => $count) {
                        $currMaxAttempts = $skillMaxAttempts[$skillId];

                        // reached max failed attempts!
                        if ($count >= $currMaxAttempts) {
                            $student->archive();
                            continue 2;    // goto next student
                        }
                    }
                }
            }
        }
    }
}
