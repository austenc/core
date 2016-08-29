<?php namespace Hdmaster\Core\Seeds;

use \User;
use \Role;
use \Facility;
use \Discipline;
use \Training;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{

    public function run()
    {
        $this->command->info('Start FacilitySeeder!');

        \Eloquent::unguard();
        
        // dont seed if table already has records
        if (\DB::table('facilities')->exists()) {
            return;
        }

        $faker = \Faker\Factory::create();

        // how many to create?
        $numFacilities = 40;

        $disp        = Discipline::all();
        $trainings   = Training::lists('id')->all();
        $tmpFacility = new Facility;
        $u           = new User;

        foreach (range(1, $numFacilities) as $i) {
            // create name
            $name = implode(' ', $faker->words(3));

            // Create user record
            $user = User::create([
                'username'              => $u->unique_username($name),
                'email'                 => $faker->unique()->safeEmail,
                'password'              => 'testing',
                'password_confirmation' => 'testing',
                'confirmed'             => true,
            ]);


            // Attach facility role
            $role = Role::where('name', '=', 'Facility')->first();
            $user->attachRole($role);

            // Determine facility actions
            $actions = $faker->randomElements($tmpFacility->avActions, rand(1, count($tmpFacility->avActions)));
            
            // Test Site
            $maxSeats = null;
            if (in_array('Testing', $actions)) {
                $maxSeats = $faker->numberBetween(5, 10);
            }

            // Training Program
            $lastApproval = null;
            if (in_array('Training', $actions)) {
                $lastApproval = $faker->date();
            }

            // facility admins
            $admin = $faker->firstName.' '.$faker->lastName.' RN';
            $don   = $faker->firstName.' '.$faker->lastName.' RN';

            // Create facility record
            $f = Facility::create([
                'user_id'       => $user->id,
                'name'          => $name,
                'license'       => null,                                        // state license, null for now							
                'actions'       => implode('|', $actions),
                'phone'         => $faker->numerify('##########'),
                'administrator' => $faker->boolean() ? $admin : null,
                'don'           => $faker->boolean() ? $don : null,
                'fax'           => null,
                'timezone'      => $faker->timezone,
                'max_seats'     => $maxSeats,
                'comments'      => null,
                'expires'       => date("Y-m-t", strtotime("+2 years")),    // last day of month in years
                'address'       => $faker->streetAddress,
                'city'          => $faker->city,
                'state'         => $faker->stateAbbr,
                'zip'           => $faker->postcode,
                'site_type'     => $faker->randomElement($tmpFacility->siteTypes, rand(1, count($tmpFacility->siteTypes))),
                'last_training_approval' => $lastApproval
            ]);

            // update userable
            $user->userable_type = $f->getMorphClass();
            $user->userable_id   = $f->id;
            $user->save();

            // add discipline(s)
            $syncDisc = $faker->randomElements($disp->lists('id')->all(), rand(1, $disp->count()));
            foreach ($syncDisc as $discId) {
                // simulate an old license
                $oldLicense = $faker->boolean(60) ? $faker->numerify('####') : null;

                $f->disciplines()->attach($discId, [
                    'tm_license'  => $tmpFacility->generateTestmasterLicense(),
                    'old_license' => $oldLicense
                ]);
            }
        }
        

        // Testing sites
        $testingSites = Facility::with([
            'affiliated',
            'disciplines'
        ])->where('actions', 'LIKE', '%Testing%')->get();

        // Affiliated Training Programs (for closed event scheduling)
        foreach ($testingSites as $site) {
            // 20% of testsites get an affiliate
            if ($faker->boolean(20) && ! $site->disciplines->isEmpty()) {
                // choose discipline from this facility
                $currDiscipline = $site->disciplines->random(1);

                $currAffiliates = $site->affiliated->filter(function ($aff) use ($currDiscipline) {
                    return $aff->pivot->discipline_id == $currDiscipline->id;
                });

                // facilities that arent available as 
                //  (current facility + current affiliated)
                $unavailableIds = array_merge([$site->id], $currAffiliates->lists('id')->all());

                // all training programs with current discipline 
                //  (also not in unavailable array)
                $available = Facility::whereHas('disciplines', function ($query) use ($currDiscipline) {
                    $query->where('facility_discipline.discipline_id', $currDiscipline->id);
                })->where('actions', 'LIKE', '%Training%')->whereNotIn('id', $unavailableIds)->get();

                // no available affiliate programs? next..
                if ($available->isEmpty()) {
                    continue;
                }

                $affiliate = $available->random(1);

                $site->affiliated()->attach($affiliate->id, [
                    'discipline_id' => $currDiscipline->id
                ]);
            }
        }

        $this->command->info('Facilities -- '.$numFacilities.' seeded!');
    }
}
