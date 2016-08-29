<?php namespace Hdmaster\Core\Seeds;

use Illuminate\Database\Seeder;
use Hdmaster\Core\Seeds\TrainingSeeder;
use Hdmaster\Core\Seeds\ExamSeeder;
use Hdmaster\Core\Seeds\CertificationSeeder;
use Hdmaster\Core\Seeds\TestitemSeeder;
use Hdmaster\Core\Seeds\TestplanSeeder;
use Hdmaster\Core\Seeds\TestformSeeder;

class TestbankSeeder extends Seeder
{

    public function run()
    {
        // create disciplines
        // requires: N/A
        $this->call(DisciplineSeeder::class);

        // requires: N/A
        $this->call(TrainingSeeder::class);

        // requires: trainings
        // creates:  subjects
        $this->call(ExamSeeder::class);

        // requires: trainings,exams
        $this->call(CertificationSeeder::class);

        // requires: exams
        // creates:  testitems,stats,distractors
        $this->call(TestitemSeeder::class);

        // requires: exams,subjects,testitems
        $this->call(TestplanSeeder::class);
        
        // requires: exams,testplans,testitems,subjects
        $this->call(TestformSeeder::class);
    }
}
