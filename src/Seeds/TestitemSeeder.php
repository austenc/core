<?php namespace Hdmaster\Core\Seeds;

use \Exam;
use \Testitem;
use \Stat;
use \Distractor;
use \Vocab;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class TestitemSeeder extends Seeder
{

    public function run()
    {
        // Allow mass assignment for now
        \Eloquent::unguard();

        $faker = Faker::create();
        $exams = Exam::with('subjects')->get();

        foreach (range(1, 1000) as $index) {
            // Create the testitem first
            $item = Testitem::create([
                'stem'     => rtrim($faker->sentence(2), '.'),
                'status'   => $faker->randomElement(['draft', 'active']), //, 'archived']),
                'comments' => $faker->sentence
            ]);

            // Create stats record for item
            Stat::create([
                'testitem_id'    => $item->id,
                'pvalue'         => $faker->randomFloat(2, 0.73, 0.93),
                'difficulty'     => $faker->randomFloat(2, -0.75, -0.85),
                'discrimination' => $faker->randomFloat(2, 0.9, 1.1),
                'guessing'       => $faker->randomFloat(2, 0.35, 0.45),
                'angoff'         => $faker->randomFloat(2, 0.74, 0.76),
                'pbs'            => $faker->randomFloat(2, 0.13, 0.14)
            ]);

            // create four distractors
            $distractors = [];
            for ($i=0; $i<5; $i++) {
                $distractors[] = Distractor::create([
                    'content'     => $faker->word,
                    'testitem_id' => $item->id,
                    'ordinal'     => $i+1
                ]);
            }

            // add answer and re-save item
            $item->answer = $distractors[array_rand($distractors)]->id;
            $item->save();

            // Get some random subjects from each exam and sync them to the item
            $toSync = [];
            foreach ($exams as $exam) {
                $subject = $exam->subjects->random(1);

                $toSync[$subject->id] = [
                    'exam_id' => $exam->id,
                    'client' => \Config::get('core.client.abbrev')
                ];
            }

            if (! empty($toSync)) {
                $item->subjects()->sync($toSync);
            }

            // Add a vocab word for this item
            $v = Vocab::create(['word' => $faker->word()]);
            $item->vocab()->sync([$v->id]);
        }
    }
}
