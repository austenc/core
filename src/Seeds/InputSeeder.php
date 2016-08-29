<?php namespace Hdmaster\Core\Seeds;

use \InputField;
use Illuminate\Database\Seeder;

class InputSeeder extends Seeder
{
    public function run()
    {
        \Eloquent::unguard();

        $faker = \Faker\Factory::create();

        \DB::table('input_fields')->delete();
        $this->command->info('Input Fields cleared!');

        $numInputs = 100;
        $inputTypes = ['textbox', 'dropdown', 'radio'];

        foreach (range(1, $numInputs) as $i) {
            $type = $faker->randomElement($inputTypes);

            $input = InputField::create([
                'type'        => $type,
                'value'        => null
            ]);

            // for textbox, sometimes set tolerance
            if ($input->type == 'textbox') {
                $input->answer = $faker->randomDigitNotNull();
                if ($faker->boolean(50)) {
                    $input->tolerance = $faker->numberBetween(1, 5);
                }
                $input->save();
            }

            // for radio|dropdown, give them options
            if ($input->type == 'radio' || $input->type == 'dropdown') {
                // value,text|value,text
                $values = [];

                $answer = null;
                for ($i = 0; $i < $faker->randomDigitNotNull(); $i++) {
                    $val  = $faker->randomDigitNotNull();
                    $text = ucfirst($faker->word);

                    // should this be the answer?
                    if (is_null($answer) && $faker->boolean()) {
                        $answer = $val;
                    }

                    $values[] = $val.','.$text;
                }

                $input->answer = $answer;
                $input->value  = implode('|', $values);
                $input->save();
            }
        } // end FOREACH

        $this->command->info('Input Fields -- '.$numInputs.' seeded!');
    }
}
