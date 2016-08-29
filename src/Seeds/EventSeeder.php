<?php namespace Hdmaster\Core\Seeds;

use Illuminate\Database\Seeder;
use Hdmaster\Core\Seeds\TesteventSeeder;

class EventSeeder extends Seeder
{

    public function run()
    {
        // requires: facilities,exams,testforms,proctors,skilltests,skilltasks
        $this->call(TesteventSeeder::class);
    }
}
