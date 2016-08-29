<?php namespace Hdmaster\Core\Seeds;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use \Discipline;

class DisciplineSeeder extends Seeder
{

    public function run()
    {
        \Eloquent::unguard();
        $faker = Faker::create();

        $a = new Discipline;
        $a->name = "Discipline A";
        $a->abbrev = "DA";
        $a->description = "Some notes about Discipline A";
        $a->save();

        $b = new Discipline;
        $b->name = "Discipline B";
        $b->abbrev = "DB";
        $b->description = "Some notes about Discipline B";
        $b->save();
    }
}
