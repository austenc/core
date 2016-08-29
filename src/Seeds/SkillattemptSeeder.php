<?php namespace Hdmaster\Core\Seeds;

use Faker\Factory as Faker;
use \Student;
use \Testevent;
use \Skilltest;
use \Skillattempt;
use \Skillexam;
use \SkilltaskResponse;
use Illuminate\Database\Seeder;

class SkillattemptSeeder extends Seeder
{

    public function run()
    {
        $faker = Faker::create();

        $passAttempts = 0;
        $failAttempts = 0;
        $failResp     = 0;
        $passResp     = 0;
        $avStatus     = ['passed', 'failed'];

        // remove any existing attempts and responses
        \DB::table('skillattempts')->delete();
        \DB::table('skilltask_responses')->delete();
        $this->command->info('Skillattempts cleared!');
        $this->command->info('SkilltaskResponses cleared!');

        // get all students
        $students    = Student::with('passedTrainings')->get();
        $subStudents = $faker->randomElements($students->lists('id')->all(), $faker->numberBetween(1, $students->count()));

        foreach ($subStudents as $studentId) {
            $student = $students->find($studentId);

            $studentPassedExam = false;
            $baseDateTime      = $faker->dateTimeBetween('-3 years', 'now');

            // pick random skill test
            $skillexam = Skillexam::with([
                'events' => function ($query) use ($baseDateTime) {
                    $query->where('test_date', '<', $baseDateTime->format('Y-m-d'));
                },
                'active_tests'
            ])->get()->random(1);

            // did we find the skillexam?
            if (is_null($skillexam)) {
                continue;
            }

            // are there any past events containing this exam?
            if ($skillexam->events->isEmpty()) {
                continue;
            }

            // are there any active tests?
            if ($skillexam->active_tests->isEmpty()) {
                continue;
            }

            // get testpool of eligible skilltest ids for student
            $studentEligibleSkilltestIds = $student->getSkilltestPool($skillexam->id);

            // no eligible skilltests for student, goto next
            if (empty($studentEligibleSkilltestIds)) {
                continue;
            }

            // pick one skilltest
            $skilltestId = $faker->randomElement($studentEligibleSkilltestIds);
            $skilltest = Skilltest::with('tasks', 'tasks.steps', 'tasks.steps.inputs')->find($skilltestId);
            if ($skilltest->tasks->isEmpty()) {
                // no tasks in skilltest? goto next student..
                continue;
            }

            // pick a random event
            $selEvent = $skillexam->events->random(1);
            $attStart = date('Y-m-d', strtotime($selEvent->test_date)).' 09:00:00';
            $attEnd   = date('Y-m-d', strtotime($selEvent->test_date)).' 10:00:00';

            // is the event over a year old? if it is, archive records
            $archived = false;
            if ($selEvent->test_date < date('Y-m-d', strtotime("-1 year"))) {
                $archived = true;
            }

            // choose a status
            $status = $faker->randomElement($avStatus);

            if ($status === 'passed') {
                $passAttempts++;
            } else {
                $failAttempts++;
            }

            // get most recent training
            $mostRecentTrDate     = '';
            $mostRecentTrainingId = null;
            foreach ($student->passedTrainings as $pt) {
                if (empty($mostRecentTrDate) || (strtotime($pt->pivot->ended) > strtotime($mostRecentTrDate))) {
                    $mostRecentTrDate     = $pt->pivot->ended;
                    $mostRecentTrainingId = $pt->pivot->id;
                }
            }

            // if there's no training, skip this
            if (empty($mostRecentTrainingId)) {
                continue;
            }

            // schedule student into this event
            $skillattempt = Skillattempt::create([
                'skillexam_id'        => $skillexam->id,
                'skilltest_id'        => $skilltestId,
                'testevent_id'        => $selEvent->id,
                'facility_id'         => $selEvent->facility_id,
                'student_training_id' => $mostRecentTrainingId,
                'student_id'          => $studentId,
                'status'              => $status,
                'archived'            => $archived,
                'start_time'          => $attStart,
                'end_time'            => $attEnd
            ]);


            // only bother giving them skilltest responses (answers) if they FAILED
            if ($status === 'failed') {
                // create a response record foreach skilltask in test
                foreach ($skilltest->tasks as $task) {
                    $respStatus = $faker->randomElement($avStatus);

                    if ($respStatus === 'passed') {
                        $passResp++;
                    } else {
                        $failResp++;
                    }

                    // Steps for this skill task
                    $steps = $task->steps;
                    $stepResponses = [];

                    // Build a json task 'response' to the steps
                    foreach ($steps as $s) {
                        $stepResponses[$s->id]['comment'] = $faker->sentence();
                        $stepResponses[$s->id]['completed'] = rand(0, 1) == 1;

                        // if this step has inputs, let's fill those in too
                        // 
                        // Code below is mosly from SkilltaskResponse organizeTaskData() method,
                        // Commented until further needed, NOTE this code is not complete and
                        // use of BBCode::parseInput will be needed just as in
                        // SkillsController->inProgress()
                        // 
                        // $inputs = $s->inputs;
                        // if($inputs)
                        // {
                        // 	foreach($inputs as $name => $val)
                        // 	{
                        // 		$nameData = explode('-', $name);
                        // 		$stepId = (int) trim($nameData[0]);

                        // 		$stepResponses[$stepId]['data'][] = ['field' => $name, 'value' => $val];
                        // 	}

                        // }
                    }

                    SkilltaskResponse::create([
                        'skillattempt_id' => $skillattempt->id,
                        'skilltask_id'    => $task->id,
                        'student_id'      => $studentId,
                        'status'          => $respStatus,
                        'archived'        => $archived,
                        'response'        => json_encode($stepResponses)
                    ]);
                } // end FOREACH skilltaskresponse
            } // end IF failed skillattempt
        } // end FOREACH student

        $this->command->info('Skillattempts -- '.$passAttempts.' Passed seeded!');
        $this->command->info('Skillattempts -- '.$failAttempts.' Failed seeded!');

        $this->command->info('SkilltaskResponses -- '.$passResp.' Passed seeded!');
        $this->command->info('SkilltaskResponses -- '.$failResp.' Failed seeded!');
    }
}
