<?php namespace Hdmaster\Core\Seeds;

use \Training;
use \User;
use \Role;
use \Instructor;
use \Facility;
use \Discipline;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{

    public function run()
    {
        $this->command->info('Start InstructorSeeder!');

        \Eloquent::unguard();

        $faker          = \Faker\Factory::create();
        $numInstructors = 40;

        // dont seed if table already has records
        if (\DB::table('instructors')->exists()) {
            return;
        }

        $trainingIds   = Training::get()->lists('id')->all();
        $facilities    = Facility::with('disciplines')->has('disciplines')->where('actions', 'LIKE', '%Training%')->orderBy('name')->get();
        $tmpInstructor = new Instructor;

        // all trainings required for each certification
        // quick lookup so we dont have to do it inside loop
        $disciplines = Discipline::with('training')->get();

        $avFacByDisp = [];
        foreach ($facilities as $f) {
            foreach ($f->disciplines as $d) {
                $avFacByDisp[$d->id][] = $f->id;
            }
        }

        $u = new User;
        foreach (range(1, $numInstructors) as $i) {
            $gender = $faker->randomElement(array('male', 'female'));
            $first  = $faker->firstName($gender);
            $last   = $faker->lastName($gender);

            // Create user record
            $user = User::create([
                'username'              => $u->unique_username($last, $first),
                'email'                 => $faker->unique()->safeEmail,
                'password'              => 'testing',
                'password_confirmation' => 'testing',
                'confirmed'             => true,
            ]);

            // Attach correct role
            $role = Role::where('name', '=', 'Instructor')->first();
            $user->attachRole($role);

            // Create instructor record
            $instructor = Instructor::create([
                'user_id'   => $user->id,
                'first'     => $first,
                'last'      => $last,
                'birthdate' => $faker->date('Y-m-d', '-20 years'),
                'gender'    => ucfirst($gender),
                'license'   => $tmpInstructor->generateUniqueLicense(),        // generate a fake RN license num
                'expires'   => date('Y-m-t', strtotime('+2 years')),
                'address'   => $faker->streetAddress,
                'city'      => $faker->city,
                'state'     => $faker->stateAbbr,
                'zip'       => $faker->postcode,
                'phone'     => $faker->numerify('##########'),
                'alt_phone' => $faker->boolean(30) ? $faker->numerify('##########') : null,
                'comments'  => $faker->boolean(30) ? $faker->paragraph() : null
            ]);

            // update userable
            $user->userable_type = $instructor->getMorphClass();
            $user->userable_id   = $instructor->id;
            $user->save();

            // choose disciplines
            $numDisc = rand(1, $disciplines->count());
            $random = $disciplines->random($numDisc);
            // force disciplines to array
            $currInsDisciplines = $numDisc == 1 ? [$random] : $random;

            // each discipline this instructor does
            $disciplineIds = [];
            foreach ($currInsDisciplines as $i => $d) {
                $disciplineIds[] = $d->id;

                // instructor teaches all trainings for this discipline
                $instructor->teaching_trainings()->attach($d->training->lists('id')->all());

                // pull some random training programs doing the current discipline
                $trPrograms = $faker->randomElements($avFacByDisp[$d->id]);
                foreach ($trPrograms as $programId) {
                    $newLicense = $instructor->generateTestmasterLicense();
                    
                    $instructor->facilities()->attach($programId, [
                        'discipline_id' => $d->id,
                        'tm_license'    => $newLicense,
                        'active'        => $faker->boolean(80)
                    ]);
                }
            }

            // set instructor disciplines
            $instructor->disciplines()->sync($disciplineIds);
        }

        $this->command->info('Instructors -- '.$numInstructors.' seeded!');
    }
}
