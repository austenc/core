<?php namespace Hdmaster\Core\Seeds;

use \Skilltask;
use \SkilltaskStep;
use \SkilltaskSetup;
use \Skillexam;
use \InputField;
use Illuminate\Database\Seeder;

class SkilltaskSeeder extends Seeder
{
    public function run()
    {
        \Eloquent::unguard();
        $faker = \Faker\Factory::create();

        // dont seed if table already has records
        if (\DB::table('skilltasks')->exists()) {
            return;
        }

        // bbcode input fields: dropdown, textbox, radio
        $bbcodes = InputField::lists('id')->all();

        $num_tasks = $faker->numberBetween(40, 80);
        $weights = ['I','1','2','3','4','5'];
        $availStatus = ['archived', 'active', 'draft'];
        $usedInputs = [];
        $created_steps = 0;
        $created_setups = 0;

        // all skillexam ids
        $skill_exam_ids = Skillexam::lists('id')->all();

        // array holding all task enemies
        $task_enemies = [];

        foreach (range(1, $num_tasks) as $i) {
            $created_setups = 0;
            $created_steps = 0;

            $status = $faker->randomElement($availStatus);

            // create the skilltask
            $task = Skilltask::create([
                'title'    => ucwords(implode(' ', $faker->words(3))),
                'scenario' => $faker->paragraph(3),
                'note'     => $faker->paragraph(2),
                'weight'   => $faker->randomElement($weights),
                'minimum'  => $faker->numberBetween(50, 100),
                'status'   => $status
            ]);

            // attach task to exams
            $num_attached_exams = $faker->randomElements($skill_exam_ids, $faker->numberBetween(1, count($skill_exam_ids)));
            foreach ($num_attached_exams as $skillexam_id) {
                $task->skillexams()->attach($skillexam_id);
            }


            // include some enemies?
            /*if($faker->boolean(20))
            {
                // grab all available tasks
                $available_enemy_ids = Skilltask::where('id', '!=', $task->id)->lists('id')->all();

                // if there are available enemy ids, attempt to add some enemies
                if($available_enemy_ids)
                {
                    // grab a subset of the tasks, set them as enemies?
                    $task_enemies = $faker->randomElements($available_enemy_ids, $faker->numberBetween(1, count($available_enemy_ids)));

                    foreach($task_enemies as $enemy_id)
                    {
                        // add the enemy
                        $task->enemies()->attach($enemy_id);
                        // add the reverse enemy relation
                        Skilltask::find($enemy_id)->enemies()->attach($task->id);
                    }
                }
            }
            */
        
            // if archived, set archived_at timestamp
            if ($status == "archived") {
                $task->archived_at = date('Y-m-d H:i:s');
                $task->save();
            }

            // create some steps
            $num_steps = $faker->numberBetween(2, 10);
            $key_step = $faker->numberBetween(1, $num_steps);
            foreach (range(1, $num_steps) as $s) {
                $outcome = implode(' ', $faker->words(15));

                $step = SkilltaskStep::create([
                    'skilltask_id'        => $task->id,
                    'weight'            => $faker->randomElement($weights),
                    'is_key'            => ($s == $key_step) ? 1 : 0,
                    'ordinal'            => $s,
                    'expected_outcome'    => $outcome,
                    'comments'            => $faker->boolean() ? $faker->paragraph(2) : null
                ]);

                // add some bbcode?
                if ($faker->boolean(20)) {
                    $foundInput    = false;        // have we found an unused input yet?
                    $inputAttempt  = 0;            // current input attempt
                    $maxAttempts   = 10;        // max tries until giving up on finding unused step input
                    while (! $foundInput && $inputAttempt < $maxAttempts) {
                        // add random bbcode to end of the current step outcome
                        $input_id = $faker->randomElement($bbcodes);

                        // check this bbcode hasnt already been used in a previous step
                        if (! in_array($input_id, $usedInputs)) {
                            // found an unused input!
                            $foundInput = true;
                            $usedInputs[] = $input_id;

                            // update step outcome with bbcode tag
                            $outcome = $outcome.' [input id="'.$input_id.'"]';
                            $step->expected_outcome = $outcome;
                            $step->save();

                            // connect new input to step
                            $step->inputs()->attach($input_id);
                        }

                        $inputAttempt++;
                    } // end WHILE
                } // end IF step input

                
                $created_steps++;
            }
            
            // create some setups?
            if ($faker->boolean(20)) {
                $num_setups = $faker->numberBetween(1, 3);

                foreach (range(1, $num_setups) as $s) {
                    SkilltaskSetup::create([
                        'skilltask_id'    => $task->id,
                        'setup'            => implode(' ', $faker->words(30)),
                        'comments'        => $faker->boolean() ? $faker->paragraph(2) : null,
                    ]);

                    $created_setups++;
                }
            } // if SETUPS


            $this->command->info('Skilltask #'.$task->id.' -- '.$created_steps.' Steps '.$created_setups.' Setups seeded!');
        } // end FOREACH num tasks
    }
}
