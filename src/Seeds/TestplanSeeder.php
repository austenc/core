<?php namespace Hdmaster\Core\Seeds;

use Faker\Factory as Faker;
use \Subject;
use \Exam;
use \Testplan;
use Illuminate\Database\Seeder;

class TestplanSeeder extends Seeder
{

    public function run()
    {
        \Eloquent::unguard();

        $faker = Faker::create();
        $exams = Exam::all();

        if (! $exams->isEmpty()) {
            foreach ($exams as $exam) {
                foreach (range(1, 1) as $index) {
                    Testplan::create([
                        'name'                => $exam->name.' Example #'.$index,
                        'exam_id'             => $exam->id,
                        'client'              => \Config::get('core.client.abbrev'),
                        'readinglevel'        => \Config::get('core.testplans.readinglevel'),
                        'readinglevel_max'    => \Config::get('core.testplans.readinglevel_max'),
                        'reliability'         => \Config::get('core.testplans.reliability'),
                        'reliability_max'     => \Config::get('core.testplans.reliability_max'),
                        'pvalue'              => \Config::get('core.testplans.pvalue'),
                        'pvalue_max'          => \Config::get('core.testplans.pvalue_max'),
                        'difficulty'          => \Config::get('core.testplans.difficulty'),
                        'difficulty_max'      => \Config::get('core.testplans.difficulty_max'),
                        'discrimination'      => \Config::get('core.testplans.discrimination'),
                        'discrimination_max'  => \Config::get('core.testplans.discrimination_max'),
                        'guessing'            => \Config::get('core.testplans.guessing'),
                        'guessing_max'        => \Config::get('core.testplans.guessing_max'),
                        'cutscore'            => \Config::get('core.testplans.cutscore'),
                        'cutscore_max'        => \Config::get('core.testplans.cutscore_max'),
                        'target_theta'        => \Config::get('core.testplans.target_theta'),
                        'pbs'                 => \Config::get('core.testplans.pbs'),
                        'item_pvalue'         => \Config::get('core.testplans.item_pvalue'),
                        'item_pvalue_max'     => \Config::get('core.testplans.item_pvalue_max'),
                        'max_attempts'        => \Config::get('core.testplans.max_attempts'),
                        'max_pvalue_attempts' => \Config::get('core.testplans.max_pvalue_attempts'),
                        'timelimit'           => \Config::get('core.testplans.timelimit'),
                        'minimum_score'       => \Config::get('core.testplans.minimum_score'),
                        'ignore_stats'        => 100,
                        'items_by_subject'      => $this->makeSubjectsJson($faker, $exam->id)
                    ]);
                }
            }
        }
    }

    private function makeSubjectsJson($faker, $exam_id)
    {
        $subjectIds = Subject::where('exam_id', '=', $exam_id)->lists('id')->all();
        
        $subjects = array();

        foreach ($subjectIds as $id) {
            // need to get number of items for this subject and not exceed max # of items
            $s = Subject::with('testitems')->find($id);
            $count = $s->testitems->count();
            $max = $count > 10 ? 10 : $count;

            // grab between 0 and max for this subject
            $subjects[$id] = $faker->numberBetween(0, $max);
        }

        return json_encode($subjects);
    }
}
