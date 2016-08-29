<?php namespace Hdmaster\Core\Seeds;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use DateTime;
use \Student;
use \Exam;
use \Testattempt;

class TestattemptSeeder extends Seeder
{

    public function run()
    {
        $faker = Faker::create();

        $count = 0;
        $avStatus = ['passed', 'failed'];

        // remove any existing students
        \DB::table('testattempts')->delete();
        $this->command->info('Testattempts cleared!');

        // get a subset of students, attempt to schedule them
        $students    = Student::with('passedTrainings')->get();
        $subStudents = $faker->randomElements($students->lists('id')->all(), $faker->numberBetween(1, $students->count()));

        foreach ($subStudents as $studentId) {
            $student = $students->find($studentId);

            $studentPassedExam = false;
            $baseDateTime      = $faker->dateTimeBetween('-3 years', 'now');
            $score             = 50;

            // pick random knowledge test
            $exam = Exam::with([
                'events' => function ($query) use ($baseDateTime) {
                    $query->where('test_date', '<', $baseDateTime->format('Y-m-d'));
                },
                'active_testforms'
            ])->get()->random(1);

            if (is_null($exam)) {
                continue;
            }

            // check the exam has some events
            if ($exam->events->isEmpty()) {
                continue;
            }

            // until student passes the exam (or we break the loop)
            while (! $studentPassedExam) {
                // get all students failed_testforms (that are not archived)
                $failedTestformIds = $student->failedAttempts()->where('exam_id', $exam->id)->get()->lists('testform_id')->all();

                // remove failed testforms from pool of eligible testforms
                $eligibleTestformIds = array_diff($exam->active_testforms->lists('id')->all(), $failedTestformIds);

                if (empty($eligibleTestformIds)) {
                    continue 2;
                }

                // choose a testform
                $testformId = $eligibleTestformIds[array_rand($eligibleTestformIds)];

                // choose a status
                $status = $faker->randomElement($avStatus);

                if ($status == 'passed') {
                    $score = rand(80, 100);
                    $studentPassedExam = true;
                }

                // student has history! get most recent date
                if (! empty($failedTestformIds)) {
                    $minTime  = strtotime($baseDateTime->format('Y-m-d'));
                    $maxTime  = strtotime("now");
                    $randTime = rand($minTime, $maxTime);

                    // generate new timestamp BETWEEN $baseDateTime and now
                    $baseDateTime = new DateTime(date('Y-m-d', $randTime));
                }

                // should it be archived?
                $archived = $baseDateTime->format('Y-m-d') < date('Y-m-d', strtotime("-2 years")) ? true : false;

                $correct = null;
                // if passed / failed, spoof some answers stuff
                if ($status == 'passed' || $status == 'failed') {
                    $correct = [];
                    $totalBySubject = \Testform::with('testplan')->find($testformId)->testplan->items_by_subject;

                    // Randomize how many they got correct by subject
                    foreach ($totalBySubject as $subId => $max) {
                        $rand = $max > 0 ? rand(0, $max) : 0;
                        $correct[$subId] = $rand;
                    }
                }

                // get most recent training
                $mostRecentTrDate     = "";
                $mostRecentTrainingId = null;
                foreach ($student->passedTrainings as $pt) {
                    if (empty($mostRecentTrDate) || (strtotime($pt->pivot->ended) > strtotime($mostRecentTrDate))) {
                        $mostRecentTrDate     = $pt->pivot->ended;
                        $mostRecentTrainingId = $pt->pivot->id;
                    }
                }

                // skip this if they don't have a training ID to associate
                if (empty($mostRecentTrainingId)) {
                    continue;
                }

                // choose a random event with this exam
                $event = $exam->events->random(1);

                $attemptInfo = [
                    'testevent_id'        => $event->id,
                    'facility_id'         => $event->facility_id,
                    'student_training_id' => $mostRecentTrainingId,
                    'student_id'          => $student->id,
                    'exam_id'             => $exam->id,
                    'testform_id'         => $testformId,
                    'status'              => $status,
                    'archived'            => $archived,
                    'score'               => $score,
                    'start_time'          => $baseDateTime->format('Y-m-d'),
                    'end_time'            => $baseDateTime->format('Y-m-d'),
                    'correct_by_subject'  => $correct
                ];

                if ($student->is_oral) {
                    $attemptInfo['is_oral'] = true;
                }

                // create student history record
                Testattempt::create($attemptInfo);
                
                $count++;
            } // end WHILE 
        } // end FOREACH student 


        $this->command->info('Testattempts -- '.$count.' seeded!');
    }
}
