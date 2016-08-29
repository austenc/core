<?php namespace Hdmaster\Core\Seeds;

use Faker\Factory as Faker;
use \Exam;
use \Training;
use \Subject;
use \Skillexam;
use \Discipline;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{

    public function run()
    {
        \Eloquent::unguard();
        $faker = Faker::create();

        // dont seed if table already has records
        if (\DB::table('exams')->exists()) {
            return;
        }

        $disciplines = Discipline::all();

        $psp = Exam::create([
            'discipline_id' => $disciplines->random(1)->id,
            'name'            => "Personal Support Professional",
            'abbrev'        => "PSP",
            'slug'            => "personal-support-professional",
            'max_attempts'    => 3,
            'has_paper'        => 1
        ]);

        $hsp = Exam::create([
            'discipline_id' => $disciplines->random(1)->id,
            'name'            => "Health Support Professional",
            'abbrev'        => "HSP",
            'slug'            => "health-support-professional",
            'max_attempts'    => 3,
            'has_paper'        => 0
        ]);

        $clp = Exam::create([
            'discipline_id' => $disciplines->random(1)->id,
            'name'            => "Community Living Professional",
            'abbrev'        => "CLP",
            'slug'            => "community-living-professional",
            'max_attempts'    => 5,
            'has_paper'        => 0
        ]);

        // add requirements
        $allExams = Exam::with([
            'discipline.training'
        ])->get();
        foreach ($allExams as $exam) {

            // get all trainings under discipline
            $discTr = $exam->discipline->training;

            // add training reqs
            if (! $exam->discipline->training->isEmpty()) {
                $training = $exam->discipline->training->random(1);

                $exam->required_trainings()->attach($training->id);
            }

            // add subjects
            $this->createSubjectsFor($exam);
        }

        // Send success message
        $this->command->info('Exams -- 3 seeded!');
    }

    public function createSubjectsFor($exam)
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $index) {
            $subject = Subject::create([
                'name'                 => implode(' ', $faker->words(2)),
                'exam_id'             => $exam->id,
                'client'             => \Config::get('core.client.abbrev')
            ]);
        }
    }
}
