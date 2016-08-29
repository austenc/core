<?php namespace Hdmaster\Core\Seeds;

use Illuminate\Database\Seeder;
use \Training;
use \Discipline;

class TrainingSeeder extends Seeder
{

    public function run()
    {
        \Eloquent::unguard();

        // dont seed if table already has records
        if (\DB::table('trainings')->exists()) {
            return;
        }

        // create trainings
        $core    = Training::create(['discipline_id' => Discipline::all()->random(1)->id, 'name' => 'Core', 'abbrev' => 'CR']);
        $hcl    = Training::create(['discipline_id' => Discipline::all()->random(1)->id, 'name' => 'Home & Community Living', 'abbrev' => 'HCL']);
        $iadl    = Training::create(['discipline_id' => Discipline::all()->random(1)->id, 'name' => 'Instrumental Activities of Daily Living', 'abbrev' => 'IADL']);
        $ps    = Training::create(['discipline_id' => Discipline::all()->random(1)->id, 'name' => 'Personal Support', 'abbrev' => 'PS']);
        $padl    = Training::create(['discipline_id' => Discipline::all()->random(1)->id, 'name' => 'Personal Activities of Daily Living', 'abbrev' => 'PADL']);
        $hmm    = Training::create(['discipline_id' => Discipline::all()->random(1)->id, 'name' => 'Health Monitoring & Maintenance', 'abbrev' => 'HMM']);


        $allDisciplines = Discipline::with('training')->get();

        // add some requirements from within same discipline
        foreach ($allDisciplines as $discipline) {
            // no trainings under this discipline?
            // goto next discipline..
            if ($discipline->training->isEmpty()) {
                continue;
            }

            // each training within the discipline
            $currBaseTraining = '';
            foreach ($discipline->training as $tr) {
                if (empty($currBaseTraining)) {
                    $currBaseTraining = $tr;
                    continue;
                }

                $tr->required_trainings()->attach($currBaseTraining->id);
            }
        }

        $this->command->info('Trainings -- 6 seeded!');
    }
}
