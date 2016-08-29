<?php namespace Hdmaster\Core\Seeds;

use Hdmaster\Core\Seeds\AdaSeeder;
use Hdmaster\Core\Seeds\FacilitySeeder;
use Hdmaster\Core\Seeds\InstructorSeeder;
use Hdmaster\Core\Seeds\ProctorSeeder;
use Hdmaster\Core\Seeds\ActorSeeder;
use Hdmaster\Core\Seeds\ObserverSeeder;
use Hdmaster\Core\Seeds\StudentSeeder;
use Illuminate\Database\Seeder;

class PeopleSeeder extends Seeder
{

    public function run()
    {
        // requires: roles,disciplines
        $this->call(FacilitySeeder::class);

        // requires; user,roles,facilities,disciplines
        $this->call(ObserverSeeder::class);
        $this->call(ProctorSeeder::class);
        $this->call(ActorSeeder::class);
        
        // requires: user,roles,trainings,facilities,disciplines
        $this->call(InstructorSeeder::class);
        
        // requires: user,roles,trainings,facilities,instructors,disciplines
        // creates: student training records
        $this->call(StudentSeeder::class);
    }
}
