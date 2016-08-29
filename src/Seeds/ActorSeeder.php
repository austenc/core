<?php namespace Hdmaster\Core\Seeds;

use Illuminate\Database\Seeder;
use \Facility;
use \User;
use \Role;
use \Discipline;
use \Actor;

class ActorSeeder extends Seeder
{

    public $faker;
    public $userRole;

    public function run()
    {
        $this->command->info('Start ActorSeeder!');

        \Eloquent::unguard();
        
        $this->faker    = \Faker\Factory::create();
        $this->userRole = Role::where('name', '=', 'Actor')->first();
        $total          = 50;

        // dont seed if table already has records
        if (\DB::table('actors')->exists()) {
            return;
        }

        // get all disciplines with test sites
        $disciplines = Discipline::with('testSites')->get();

        // each proctor
        foreach (range(1, $total) as $i) {
            // create the actor
            $actor = $this->createActor();

            // choose disciplines for the actor
            $numDisc          = rand(1, $disciplines->count());
            $random           = $disciplines->random($numDisc);
            $actorDisciplines = $numDisc == 1 ? [$random] : $random;

            // each discipline the proctor works in
            foreach ($actorDisciplines as $discipline) {
                // add training programs (only programs that work within current discipline)
                $numPrograms = rand(2, $discipline->testSites->count());
                $actorPrograms = $discipline->testSites->random($numPrograms);

                // each program
                foreach ($actorPrograms as $program) {
                    // attach proctor to program with license
                    $actor->facilities()->attach($program->id, [
                        'discipline_id' => $discipline->id,
                        'tm_license'    => $actor->generateTestmasterLicense(),
                        'active'        => true
                    ]);
                }
            }
        }

        $this->command->info('Actors -- '.$total.' seeded!');
    }

    /**
     * Creates a fake proctor record with attached user
     */
    private function createActor()
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

        // Create proctor record
        $actor = Actor::create([
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
        $user->userable_type = $actor->getMorphClass();
        $user->userable_id   = $actor->id;
        $user->save();

        return $actor;
    }
}
