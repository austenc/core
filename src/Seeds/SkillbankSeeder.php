<?php namespace Hdmaster\Core\Seeds;

use Hdmaster\Core\Seeds\InputSeeder;
use Hdmaster\Core\Seeds\SkillexamSeeder;
use Hdmaster\Core\Seeds\SkilltaskSeeder;
use Hdmaster\Core\Seeds\SkilltestSeeder;
use Hdmaster\Core\Seeds\ExamRequirementSeeder;
use Illuminate\Database\Seeder;

class SkillbankSeeder extends Seeder
{

    public function run()
    {
        // requires: none
        $this->call(InputSeeder::class);

        // requires: trainings, exams
        $this->call(SkillexamSeeder::class);

        // requires: input fields,skillexams
        // creates:	 tasks,setups,steps
        $this->call(SkilltaskSeeder::class);

        // requires: skillexams,tasks,trainings,exams
        $this->call(SkilltestSeeder::class);

        // requires: skillexams, exams
        //$this->call(ExamRequirementSeeder::class);
    }
}
