<?php namespace Hdmaster\Core\Seeds;

use Lang;
use \Training;
use \Facility;
use \User;
use \Student;
use \Instructor;
use \Role;
use \Config;
use \Discipline;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Start StudentSeeder!');

        \Eloquent::unguard();
        $faker = \Faker\Factory::create();

        // dont seed if table already has records
        if (\DB::table('students')->exists()) {
            return;
        }

        // training expirations
        $allTrainings  = Training::with('required_trainings')->get();
        $trExpirations = $allTrainings->lists('valid_for', 'id')->all();

        $ssnLen                = 9;                // length of a ssn
        $numStudents           = 250;            // create this many students
        $maxTrainingPerStudent = 6;            // maximum number of student training records for a student
        $hasTrainingPct        = 90;            // 80% have trainings
        $statusOpts            = ['passed', 'failed', 'attending'];
        $u                     = new User;
        
        foreach (range(1, $numStudents) as $i) {
            $gender   = $faker->randomElement(array('male', 'female'));
            $first    = $faker->firstName($gender);
            $last     = $faker->lastName($gender);
            
            // Create user record
            $user = User::create([
                'username'              => $u->unique_username($last, $first),
                'email'                 => $faker->unique()->safeEmail,
                'password'              => 'testing',
                'password_confirmation' => 'testing',
                'confirmed'             => true,
            ]);

            // Add student role
            $role = Role::where('name', '=', 'Student')->first();
            $user->attachRole($role);

            // new student fake ssn
            $ssn = str_pad($faker->unique()->randomNumber($ssnLen), $ssnLen, '0', STR_PAD_LEFT);

            $info = [
                'user_id'      => $user->id,
                'first'        => $first,
                'last'         => $last,
                'ssn'          => \Crypt::encrypt($ssn),
                'ssn_hash'     => saltedHash($ssn),
                'birthdate'    => $faker->date('Y-m-d', '-20 years'),
                'phone'        => $faker->numerify('##########'),
                'alt_phone'    => $faker->boolean(30) ? $faker->numerify('##########') : null,
                'gender'       => ucfirst($gender),
                'address'      => $faker->streetAddress,
                'city'         => $faker->city,
                'state'        => $faker->stateAbbr,
                'zip'          => $faker->postcode,
                'creator_type' => '\Admin',
                'creator_id'   => 1
            ];

            // oral student?
            if ($faker->boolean(10)) {
                $info['is_oral'] = true;
            }

            // Create the student record
            $student = Student::create($info);

            // update userable
            $user->userable_type = $student->getMorphClass();
            $user->userable_id   = $student->id;
            $user->save();

            // ---------------------------------------------------------------------------------
            // --------------------------- INITIAL TRAINING ------------------------------------
            // ---------------------------------------------------------------------------------
            $avDisciplines    = $student->availableDisciplines();
            $currDisciplineId = $avDisciplines->random(1)->id;

            // get current discipline
            $currDiscipline = Discipline::with([
                'training.required_trainings',
                'trainingPrograms',
                'trainingPrograms.activeInstructors' => function ($query) use ($currDisciplineId) {
                    $query->wherePivot('discipline_id', $currDisciplineId);
                },
                'trainingPrograms.activeInstructors.teaching_trainings'
            ])->find($currDisciplineId);

            // choose training with no requirements
            $currTrainings = $currDiscipline->training->filter(function ($tr) {
                return $tr->required_trainings->isEmpty();
            });

            // no eligible trainings?
            if ($currTrainings->isEmpty()) {
                continue;
            }

            // choose a training
            $currTrainingId = $currTrainings->random(1)->id;

            // loop thru all training programs for this discipline
            // if the current program has instructors, look into them
            // if instructor is teaching the current training we are looking for, stop looping
            // if we get to end of loop without finding a legit instructor, try next discipline? (log error?)
            $currProgramId    = '';
            $currInstructorId = '';
            foreach ($currDiscipline->trainingPrograms->shuffle() as $program) {
                // each active instructor at this program
                foreach ($program->activeInstructors->shuffle() as $instructor) {
                    // does this instructor teach the current training?
                    if (in_array($currTrainingId, $instructor->teaching_trainings->lists('id')->all())) {
                        $currProgramId    = $program->id;
                        $currInstructorId = $instructor->id;
                    }
                }
            }

            // ensure instructor was found for this discipline/training
            if (empty($currInstructorId)) {
                // delete student and user record? goto next
                // log entry for now to see how many times this case will appear
                Log::info('Student '.$student->id.' could not find Instructor for initial training. Discipline/Training '.$currDisciplineId.'/'.$currTrainingId);
                dd('ERROR: STUDENTSEEDER - COULD NOT FIND ELIGIBLE INSTRUCTOR FOR INITIAL STUDENT TRAINING RECORD');
                continue;
            }

            // choose status for the initial training
            $currStatus = $faker->randomElement($statusOpts);

            // initial training dates
            $startDate     = $faker->dateTimeBetween('-1 year', 'now');
            $trStartedDate = $startDate->format('Y-m-d');
            $trEndedDate   = null;
            $trExpires     = null;
            $reason        = null;
            $expired       = false;
            // initial training hours
            $classHours    = null;
            $distHours     = null;
            $labHours      = null;
            $traineeHours  = null;

            // status
            if (in_array($currStatus, ['passed', 'failed'])) {
                $endedDate   = $startDate->modify("+1 month");
                $trEndedDate = $endedDate->format('Y-m-d');

                // PASSED TRAINING
                if ($currStatus == "passed") {
                    // set hours
                    $classHours   = $faker->boolean() ? $faker->numberBetween(0, 150) : null;
                    $distHours    = $faker->boolean() ? $faker->numberBetween(0, 150) : null;
                    $labHours     = $faker->boolean() ? $faker->numberBetween(0, 150) : null;
                    $traineeHours = $faker->boolean() ? $faker->numberBetween(0, 150) : null;

                    // EXPIRED?
                    $trExpires = $endedDate->modify('+ '.$trExpirations[$currTrainingId].' months')->format('Y-m-d');

                    if (strtotime($trExpires) < time()) {
                        $expired = true;
                    }
                } else {
                    $reason = $faker->randomElement(Lang::get('core::training.reasons'));
                }
            }
            
            // initial training record data
            $initTrainingInfo = [
                'discipline_id'     => $currDisciplineId,
                'facility_id'       => $currProgramId,
                'instructor_id'     => $currInstructorId,
                'status'            => $currStatus,
                'reason'            => $reason,
                'classroom_hours'   => $classHours,
                'distance_hours'    => $distHours,
                'lab_hours'         => $labHours,
                'traineeship_hours' => $traineeHours,
                'started'           => $trStartedDate,
                'ended'             => $trEndedDate,
                'expires'           => $trExpires,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
                'creator_type'      => '\Admin',
                'creator_id'        => 1
            ];

            // expired training
            if ($expired) {
                $initTrainingInfo['archived_at'] = date('Y-m-d H:i:s');

                // if training is archived, unset all active instructors
                // no one currently owns this student
                \DB::table('instructor_student')->where('student_id', '=', $student->id)
                    ->update(['active' => false]);
            }
            // active training
            else {
                // instructor doing the training "owns" this student now
                $student->setCurrentInstructor($currInstructorId);
            }

            // add initial training to new student
            $student->trainings()->attach($currTrainingId, $initTrainingInfo);
            // ---------------------------------------------------------------------------------
        } // end FOREACH numStudents

        $this->command->info('Students -- '.$numStudents.' seeded!');
    }
}
