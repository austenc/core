<?php namespace Hdmaster\Core\Seeds;

use \Facility;
use \User;
use \Discipline;
use \Role;
use \Observer;
use Illuminate\Database\Seeder;

class ObserverSeeder extends Seeder
{

    public $faker;
    public $userRole;

    public function run()
    {
        $this->command->info('Start ObserverSeeder!');

        \Eloquent::unguard();

        $this->faker    = \Faker\Factory::create();
        $this->userRole = Role::where('name', '=', 'Observer')->first();
        $total          = 50;

        // dont seed if table already has records
        if (\DB::table('observers')->exists()) {
            return;
        }

        // get all disciplines with test sites
        $disciplines = Discipline::with('testSites')->get();

        foreach (range(1, $total) as $i) {
            // create the proctor
            $observer = $this->createObserver();

            // choose disciplines for the observer
            $numDisc        = 1; //rand(1, $disciplines->count());
            $obsDisciplines = $disciplines->random($numDisc);
            $obsDisciplines = $numDisc == 1 ? [$obsDisciplines] : $obsDisciplines;
            
            // each discipline the proctor works in
            foreach ($obsDisciplines as $discipline) {

                // add training programs (only programs that work within current discipline)
                $numPrograms = rand(2, $discipline->testSites->count());
                $obsPrograms = $discipline->testSites->random($numPrograms);

                // each program
                foreach ($obsPrograms as $program) {
                    // attach proctor to program with license
                    $observer->facilities()->attach($program->id, [
                        'discipline_id' => $discipline->id,
                        'tm_license'    => $observer->generateTestmasterLicense(),
                        'active'        => true
                    ]);
                }
            }
        }

        $this->command->info('Observers -- '.$total.' seeded!');
    }

    /**
     * Creates a fake observer record with attached user
     */
    private function createObserver()
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

        // Attach role to user
        $user->attachRole($this->userRole);

        // Create observer record
        $observer = Observer::create([
            'user_id'   => $user->id,
            'first'     => $first,
            'last'      => $last,
            'birthdate' => $this->faker->date('Y-m-d', '-20 years'),
            'license'   => null,
            'phone'     => $this->faker->numerify('##########'),
            'alt_phone' => $this->faker->boolean(30) ? $this->faker->numerify('##########') : null,
            'gender'    => ucfirst($gender),
            'address'   => $this->faker->streetAddress,
            'city'      => $this->faker->city,
            'state'     => $this->faker->stateAbbr,
            'zip'       => $this->faker->postcode,
            'comments'  => $this->faker->boolean(30) ? $this->faker->sentence() : null
        ]);

        // polymorphic
        $user->userable_type = $observer->getMorphClass();
        $user->userable_id   = $observer->id;
        $user->save();

        return $observer;
    }
}
