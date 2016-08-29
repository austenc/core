<?php namespace Hdmaster\Core\Seeds;

use Faker\Factory as Faker;
use \Training;
use \Skillexam;
use \Exam;
use \Discipline;
use Illuminate\Database\Seeder;

class SkillexamSeeder extends Seeder
{
    public function run()
    {
        \Eloquent::unguard();
        $faker = Faker::create();

        $numExams       = 5;
        $allTrainingIds = Training::lists('id')->all();
        $disciplines    = Discipline::with('training')->get();

        foreach (range(1, $numExams) as $i) {
            $nameWords = $faker->words(3);
            
            $abbrev = '';
            foreach ($nameWords as $word) {
                $abbrev .= substr($word, 0, 1);
            }

            $currDiscipline = $disciplines->random(1);

            // create skillexam
            $skillexam = Skillexam::create([
                'discipline_id' => $currDiscipline->id,
                'name'            => ucwords(implode(' ', $nameWords)),
                'abbrev'        => strtoupper($abbrev),
                'slug'            => strtolower(implode('-', $nameWords)),
                'max_attempts'    => $faker->randomDigit(),
                'comments'        => ($faker->boolean(30) ? $faker->paragraph(3) : null)
            ]);

            // required trainings
            if (! $currDiscipline->training->isEmpty()) {
                $skillexam->required_trainings()->attach($currDiscipline->training->random(1)->id);
            }
        }

        $this->command->info('Skillexams -- '.$numExams.' seeded!');
    }
}
