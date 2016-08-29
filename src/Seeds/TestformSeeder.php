<?php namespace Hdmaster\Core\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Collection as Collection;
use Illuminate\Database\Seeder;
use \Exam;
use \Subject;
use \Testform;
use \Testplan;

class TestformSeeder extends Seeder
{

    public function run()
    {
        $faker = Faker::create();
        $exams = Exam::all();

        foreach ($exams as $exam) {
            foreach (range(1, 25) as $index) {
                // get a random testplan
                $plan = Testplan::where('exam_id', '=', $exam->id)
                        ->where('client', '=', \Config::get('core.client.abbrev'))
                        ->get()->random(1);

                // get its items by subject
                $planItems = $plan->items_by_subject;
                $items = new Collection;

                // fill up with items of each subject
                foreach ($planItems as $subject => $numItems) {
                    if ($numItems > 0) {
                        $subItems = Subject::with('testitems')->find($subject)->testitems->random($numItems);

                        // make sure it's a collection since random returns mixed
                        if (is_array($subItems)) {
                            $subItems = new Collection($subItems);
                        } elseif ($subItems instanceof Testitem) {
                            $subItems = new Collection([$subItems]);
                        }


                        // add to the collection
                        $items = $items->merge($subItems);
                    }
                }

                // Create the form
                $form = Testform::create([
                    'exam_id'         => $exam->id,
                    'testplan_id'     => $plan->id,
                    'legacy_id'       => 0,
                    'name'            => $exam->name.' Testform #'.$index,
                    'client'          => \Config::get('core.client.abbrev'),
                    'minimum'         => \Config::get('core.testplans.minimum_score'),
                    'oral'            => $faker->numberBetween(0, 1),
                    'spanish'         => $faker->numberBetween(0, 1),
                    'status'          => $faker->randomElement(['active', 'archived', 'draft']),
                    'header'          => null,
                    'footer'          => null,
                    'english_source'  => null,
                    'scramble_source' => null
                ]);

                $ids = [];
                $count = 1;
                foreach ($items as $item) {
                    if (is_object($item)) {
                        $ids[$item->id] = ['ordinal' => $count];
                        $count++;
                    }
                }

                $form->testitems()->sync($ids);
            }
        }
    }
}
