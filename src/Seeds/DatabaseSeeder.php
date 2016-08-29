<?php namespace Hdmaster\Core\Seeds;

use Illuminate\Database\Seeder;
use Hdmaster\Core\Seeds\AccountingSeeder;
use Hdmaster\Core\Seeds\AuthSeeder;
use Hdmaster\Core\Seeds\TestbankSeeder;
use Hdmaster\Core\Seeds\SkillbankSeeder;
use Hdmaster\Core\Seeds\PeopleSeeder;
use Hdmaster\Core\Seeds\EventSeeder;
use Hdmaster\Core\Seeds\TestattemptSeeder;
use Hdmaster\Core\Seeds\SkillattemptSeeder;
use Hdmaster\Core\Seeds\StudentScheduleSeeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Eloquent::unguard();
        
        // Knowledge tests
        $this->call(TestbankSeeder::class);

        // Skill tests
        $this->call(SkillbankSeeder::class);

        // People
        $this->call(PeopleSeeder::class);

        // Events
        $this->call(EventSeeder::class);

        // requires: students,exams,testforms,testevents
        $this->call(TestattemptSeeder::class);

        // requires: students,skilltests,events
        // creates: passed/failed skillattempts, along with Skillresponses for all failed attempts
        $this->call(SkillattemptSeeder::class);

        // requires: students, student trainings/exams/skills, testevents, corequired exams/skills
        // creates: skillattempt and testattempt records with assigned status (schedule record)
        //$this->call(StudentScheduleSeeder::class);

        // requires: students, certifications
        // creates: looks at student passed skills/exams to determine if they have a certification
        //$this->call(StudentCertificationSeeder::class);

        $this->command->info('Database seeded!');
    }
}
