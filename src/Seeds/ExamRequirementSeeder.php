<?php namespace Hdmaster\Core\Seeds;

use Faker\Factory as Faker;
use \Skillexam;
use \Exam;
use Illuminate\Database\Seeder;

class ExamRequirementSeeder extends Seeder
{

    public function run()
    {
        \Eloquent::unguard();

        $faker = Faker::create();

        \DB::table('exam_requirements')->delete();
        \DB::table('exam_skill_requirements')->delete();

        $this->command->info('Skill and Exam Requirements cleared!');

        // get exams with discipline info
        $exams = Exam::with([
            'discipline.exams',
            'discipline.skills'
        ])->get();

        foreach ($exams as $ex) {
            // no requirement half the time
            if ($faker->boolean()) {
                continue;
            }

            if (! $ex->discipline->skills->isEmpty()) {
                $sk = $ex->discipline->skills->random(1);

                if ($faker->boolean(70)) {
                    $ex->required_skills()->attach($sk->id, ['status' => 'coreq']);
                    $sk->required_exams()->attach($ex->id, ['status' => 'coreq']);

                    $this->command->info('Skill #'.$sk->id.' corequirement for Exam #'.$ex->id.'!');
                }

                // prereq
                else {
                    $ex->required_skills()->attach($skillId, ['status' => 'prereq']);

                    $this->command->info('Skill #'.$sk->id.' prerequirement for Exam #'.$ex->id.'!');
                }
            }
        }
    }
}
