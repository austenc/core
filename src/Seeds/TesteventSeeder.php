<?php namespace Hdmaster\Core\Seeds;

use Faker\Factory as Faker;
use DateTime;
use \Facility;
use \Exam;
use \Testevent;
use \Proctor;
use \Skillexam;
use \Discipline;
use Illuminate\Support\Collection as Collection;
use Illuminate\Database\Seeder;

class TesteventSeeder extends Seeder
{

    public function run()
    {
        $this->command->info('Start TesteventSeeder!');

        \Eloquent::unguard();
        $faker = Faker::create();

        $numEvents = 150;

        $disciplines = Discipline::with([
            'testSites',
            'testSites.activeObservers',
            'testSites.activeObservers.disciplines',
            'testSites.activeProctors',
            'testSites.activeProctors.disciplines',
            'testSites.activeActors',
            'testSites.activeActors.disciplines',
            'exams',
            'skills'
        ])->get();

        foreach (range(1, $numEvents) as $i) {
            // choose a discipline to create a testevent for
            $discipline = $disciplines->random(1);
            $disciplineId = $discipline->id;

            // choose test site
            $testSite = $discipline->testSites->random(1);

            // choose observer
            // all observers working within the current discipline
            $observers = $testSite->activeObservers->filter(function ($obs) use ($disciplineId) {
                if (in_array($disciplineId, $obs->disciplines->lists('id')->all())) {
                    return true;
                }
            });

            // if test site has no observers, goto next event
            // every event needs at least an observer on test team
            if ($observers->isEmpty()) {
                continue;
            }

            // choose observer
            $observer = $observers->random(1);

            // dates
            // start/end cutoffs (first 5 events will happen today)
            $startDate = $i <= 5 ? new DateTime() : $faker->dateTimeBetween('-1 years', '+1 year');
            $endDate   = $faker->time();

            // event info
            $eventInfo = [
                'discipline_id' => $disciplineId,
                'observer_id'   => $observer->id,
                'facility_id'   => $testSite->id,
                'test_date'     => $startDate,
                'start_time'    => $endDate,
                'is_paper'      => $faker->boolean(50),
                'is_regional'   => $faker->boolean(75)
            ];

            // Create new event
            $event = Testevent::create($eventInfo);

            // choose exams
            if (! $discipline->exams->isEmpty()) {
                foreach ($discipline->exams as $e) {
                    // knowledge exams
                    $currExam     = Exam::with('active_testforms')->find($e->id);
                    $numTestforms = $currExam->active_testforms->count();

                    // attach event exam
                    $event->exams()->attach($e->id, [
                        'open_seats'     => $faker->numberBetween(1, $numTestforms),
                        'reserved_seats' => null,
                        'is_paper'         => 0
                    ]);
                }
            }

            // choose skill exams
            $hasSkill = false;
            if (! $discipline->skills->isEmpty()) {
                foreach ($discipline->skills as $s) {
                    // skill exams
                    $currSkill    = Skillexam::with('active_tests')->find($s->id);
                    $numTestforms = $currSkill->active_tests->count();

                    // attach event skillexam
                    $event->skills()->attach($s->id, [
                        'open_seats'     => $faker->numberBetween(1, $numTestforms),
                        'reserved_seats' => null,
                    ]);

                    $hasSkill = true;
                }
            }

            // ------------------------------------------------------------------------------------
            // -------------------------------- UPDATE EVENT --------------------------------------
            // ------------------------------------------------------------------------------------
            $updateInfo = [
                'proctor_id'   => null,
                'proctor_type' => null,
                'actor_id'     => null,
                'actor_type'   => null,
            ];

            // if event has skill exams..
            // update event with proctor and actor
            if ($hasSkill) {
                // choose proctor
                $proctors = $testSite->activeProctors->filter(function ($proc) use ($disciplineId) {
                    if (in_array($disciplineId, $proc->disciplines->lists('id')->all())) {
                        return true;
                    }
                });
                if (! $proctors->isEmpty()) {
                    $proctor = $proctors->random(1);

                    $updateInfo['proctor_id']   = $proctor->id;
                    $updateInfo['proctor_type'] = $proctor->getMorphClass();
                }

                // choose actor
                $actors = $testSite->activeActors->filter(function ($act) use ($disciplineId) {
                    if (in_array($disciplineId, $act->disciplines->lists('id')->all())) {
                        return true;
                    }
                });
                if (! $actors->isEmpty()) {
                    $actor = $actors->random(1);
                    
                    $updateInfo['actor_id']   = $actor->id;
                    $updateInfo['actor_type'] = $actor->getMorphClass();
                }
            }
            
            // past event? (modify info to reflect past event)
            if ($startDate->getTimestamp() <= time()) {
                $event->locked     = 1;
                $event->start_code = 'ABCD'; //$faker->bothify('?#??');	// generate random alphanumeric start code

                // set event ended date?
                if (date('Y-m-d', $startDate->getTimestamp()) < date('Y-m-d')) {
                    $event->ended = date('Y-m-d H:i:s');
                }
            }

            // update event
            $event->update($updateInfo);
            // ------------------------------------------------------------------------------------
        }

        $this->command->info('Testevents -- '.$numEvents.' seeded!');
    }
}
