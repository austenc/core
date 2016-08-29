<?php namespace Hdmaster\Core\Models\SkilltaskStep;

use Input;
use \Skilltask;
use \InputField;
use \BBCode;
use Validator;

class SkilltaskStep extends \Eloquent
{
    protected $fillable = ['skilltask_id', 'weight', 'is_key', 'ordinal',
                           'expected_outcome', 'alt_display', 'comments',
                           'vinput_review'];

    public static $rules = [
        'weight'           => 'required',
        'expected_outcome' => 'required',
        'ordinal'          => 'sometimes'
    ];


    public function task()
    {
        return $this->belongsTo(Skilltask::class, 'skilltask_id');
    }
    public function inputs()
    {
        return $this->belongsToMany(InputField::class, 'step_inputs', 'step_id', 'input_id');
    }

    /**
     * Saves a new bbcode input for a step
     */
    public function saveInput()
    {
        $type       = Input::get('type');
        $textAnswer = Input::get('text_answer');
        $tolerance  = Input::get('tolerance');

        // multi option/val ... dropdown/radio
        $options = Input::get('option');
        $values  = Input::get('value');
        $answer  = Input::get('answer');
        $valStr = array();        // holds concatenated strings [val,text|val2,text2] 

        // create new input
        $input = InputField::create([
            'type'        => $type
        ]);

        if ($type == 'textbox') {
            $input->tolerance = $tolerance;
            $input->answer    = $textAnswer;
        }
        // dropdown/radio
        else {
            // options/values
            foreach ($options as $i => $opt) {
                if (empty($opt)) {
                    continue;
                }

                // if no value set, use lowercase option
                $val = isset($values[$i]) && !empty($values[$i]) ? $values[$i] : strtolower($opt);
                $valStr[] = $val.','.$opt;

                if (isset($answer) && $i == $answer) {
                    $input->answer = $val;
                    $input->save();
                }
            }

            // store pipe delimited options for input
            $input->value = implode('|', $valStr);
        }

        // save any field changes
        $input->save();

        // now attach new input to current step
        $this->inputs()->attach($input->id);

        // update the expected outcome for the step
        $this->expected_outcome = $this->expected_outcome.'[input id="'.$input->id.'"]';
        $this->save();

        return $input->id;
    }

    /**
     * Parses the expected outcome for variable fields and returns paper version
     */
    public function getPaperAttribute()
    {
        return BBCode::parseInput($this->expected_outcome, $this->id, 'paper');
    }

    /**
     * Parses the expected outcome for variable fields and returns web version
     */
    public function getWebAttribute()
    {
        return BBCode::parseInput($this->expected_outcome, $this->id, 'web');
    }

    /** 
     * Update a single step
     */
    public function updateWithInput()
    {
        $this->weight           = Input::get('weight');
        $this->expected_outcome = Input::get('expected_outcome');
        $this->alt_display      = Input::get('alt_display');
        $this->comments         = Input::get('comments');

        return $this->save();
    }

    public function validate()
    {
        $rules = static::$rules;

        $v = Validator::make($this->attributes, $rules);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }
}
