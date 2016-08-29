<?php namespace Hdmaster\Core\Seeds;

use \Facility;
use \User;
use \Discipline;
use \Role;
use \Proctor;
use Illuminate\Database\Seeder;

class ProctorSeeder extends Seeder
{

    public $faker;
    public $userRole;

    public function run()
    {
        $this->command->info('Start ProctorSeeder!');

        \Eloquent::unguard();

        $this->faker    = \Faker\Factory::create();
        $this->userRole = Role::where('name', '=', 'Proctor')->first();
        $total          = 50;

        // dont seed if table already has records
        if (\DB::table('proctors')->exists()) {
            return;
        }

        // get all disciplines with test sites
        $disciplines = Discipline::with('testSites')->get();

        // each proctor
        foreach (range(1, $total) as $i) {
            // create the proctor
            $proctor = $this->createProctor();

            // choose disciplines for the proctor
            $numDisc = rand(1, $disciplines->count());
            $random  = $disciplines->random($numDisc);
            $procDisciplines = $numDisc == 1 ? [$random] : $random;

            // each discipline the proctor works in
            foreach ($procDisciplines as $discipline) {
                // add training programs (only programs that work within current discipline)
                $numPrograms = rand(2, $discipline->testSites->count());
                $procPrograms = $discipline->testSites->random($numPrograms);

                // each program
                foreach ($procPrograms as $program) {
                    // attach proctor to program with license
                    $proctor->facilities()->attach($program->id, [
                        'discipline_id' => $discipline->id,
                        'tm_license'    => $proctor->generateTestmasterLicense(),
                        'active'        => true
                    ]);
                }
            }
        }
    }

    /**
     * Creates a fake proctor record with attached user
     */
    private function createProctor()
    {
        $u      = new User;
        $gender = $this->faker->randomElement(array('male', 'female'));
        $first  = $this->faker->firstName($gender);
        $last   = $this->faker->lastName($gender);

        // Create user record
        $user = User::create([
            'username'              => $u->unique_username($last, $first),
            'email'                 => $this->faker->unique()->safeEmail,
            'password'              => 'testing',
            'password_confirmation' => 'testing',
            'confirmed'             => true,
        ]);

        //$this->command->info('Saved user record!');

        // Attach role to user
        $user->attachRole($this->userRole);

        // Create proctor record
        $proctor = Proctor::create([
            'user_id'   => $user->id,
            'first'     => $first,
            'last'      => $last,
            'birthdate' => $this->faker->date('Y-m-d', '-20 years'),
            'gender'    => ucfirst($gender),
            'address'   => $this->faker->streetAddress,
            'city'      => $this->faker->city,
            'state'     => $this->faker->stateAbbr,
            'zip'       => $this->faker->postcode
        ]);

        // polymorphic
        $user->userable_type = $proctor->getMorphClass();
        $user->userable_id   = $proctor->id;
        $user->save();

        return $proctor;
    }
}
