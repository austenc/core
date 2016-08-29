<?php namespace Hdmaster\Core\Models\InputField;

use Input;
use Validator;
use \SkilltaskStep;

class InputField extends \Eloquent
{
    
    protected $fillable = ['type', 'answer', 'tolerance', 'value'];

    protected $rules = [
        'type'            => 'required'
    ];

    /**
     * A certification may require several trainings
     */
    public function steps()
    {
        return $this->belongsToMany(SkilltaskStep::class, 'step_inputs', 'input_id', 'step_id');
    }


    /**
     * Accessors
     */
    public function getOptionAttribute()
    {
        $x    = [];
        $opts = explode('|', $this->value);

        foreach ($opts as $opt) {
            $vars  = explode(',', $opt);
            if (isset($vars[1])) {
                $x[] = $vars[1];
            }
        }

        return $x;
    }
    public function getValuesAttribute()
    {
        $x    = [];
        $opts = explode('|', $this->value);

        foreach ($opts as $opt) {
            $vars  = explode(',', $opt);
            if (isset($vars[0])) {
                $x[] = $vars[0];
            }
        }

        return $x;
    }
    public function getBbcodeAttribute()
    {
        return '[input id="'.$this->id.'"]';
    }
    // returns the index the answer is found at ($i)
    // useful for setting selected radio in view
    public function getAnswerKeyAttribute()
    {
        $key = '';

        foreach ($this->values as $i => $v) {
            if ($v == $this->answer) {
                $key = $i;
            }
        }

        return $key;
    }


    public function addWithInput()
    {
        $type      = Input::get('type');
        $tolerance = ($type == 'textbox') ? Input::get('tolerance') : null;
        $opts      = Input::get('option');
        $vals      = Input::get('value');
        // answer
        $answerKey  = Input::get('answer');
        $textAnswer = Input::get('text_answer');
        //$answer     = ($type == 'textbox') ? $textAnswer : 
        $answer = $textAnswer;

        // get skill step
        $stepId = Input::get('step_id');
        $step   = SkilltaskStep::find($stepId);
        if (is_null($step)) {
            return false;
        }
            
        // option||value
        $valArr    = [];
        foreach ($opts as $i => $opt) {
            // skip blank options
            if (empty($opt)) {
                continue;
            }

            // value specified? otherwise use text option lowercase
            $val = isset($vals[$i]) && !empty($vals[$i]) ? $vals[$i] : strtolower($opt);

            // is this option the answer?
            if ($type != 'textbox' && $i == $answerKey) {
                $answer = $val;
            }

            $valArr[] = $val.','.$opt;
        }
        $valStr = ($type == 'textbox') ? null : implode('|', $valArr);

        // create new variable input
        $input = InputField::create([
            'type'      => $type,
            'answer'    => $answer,
            'tolerance' => $tolerance,
            'value'     => $valStr
        ]);

        // attach new input to step
        $step->inputs()->attach($input->id);
        // update step outcome with new input tag bbcode
        $step->expected_outcome = $step->expected_outcome.' [input id="'.$input->id.'"]';
        $step->save();
        
        return $input->id;
    }

    /**
     * Update an existing input
     */
    public function updateWithInput()
    {
        if ($this->type == 'textbox') {
            $this->tolerance = Input::get('tolerance', null);
            $this->answer    = Input::get('text_answer');
        } elseif ($this->type == 'radio' || $this->type == 'dropdown') {
            $answer  = Input::get('answer');        // answer is key at this point [0, 1, 2, ...]
            $options = Input::get('option');
            $values  = Input::get('value');

            $pairs = array();
            foreach ($values as $i => $val) {
                // if no text option, skip
                if (! isset($options[$i]) || empty($options[$i])) {
                    continue;
                }

                // is this value the answer? can answer from key to actual value
                if ($i == $answer) {
                    $answer = $val;
                }

                $text    = isset($options[$i]) && !empty($options[$i]) ? $options[$i] : strtolower($val);
                $pairs[] = $val.','.$text;
            }
            
            // update input
            $this->tolerance = null;
            $this->answer    = $answer;        // now $answer is actual value
            $this->value     = empty($pairs) ? '' : implode('|', $pairs);
        }

        return $this->save();
    }

    /**
     * Validates a new input before inserting
     */
    public function validate()
    {
        $rules    = $this->rules;
        $messages = [];

        if ($this->type == 'textbox') {
            // $rules['text_answer'] = 'required';
            $rules['tolerance']   = 'integer';

            // $messages['text_answer.required'] = 'The answer field is required.';
        } elseif ($this->type == 'radio' || $this->type == 'dropdown') {
            // answer must be associated with non-empty option
            $rules['answer'] = 'required|input_option_answer';
            // must have at least 2 text/value pairs
            // all input options must be unique
            $rules['option'] = 'array|step_inputs|input_option_unique';
            // all input values must be unique
            $rules['value'] = 'array|input_value_unique';
        
            $messages['option.step_inputs']         = 'A minimum of 2 Options are required.';
            $messages['answer.required']            = 'The Answer field is required.';
            $messages['answer.input_option_answer'] = 'The Answer field must have an Option.';
            $messages['option.input_option_unique'] = 'The Option field must contain unique values.';
            $messages['value.input_value_unique']   = 'The Value field must contain unique values.';
        }

        $v = Validator::make(Input::all(), $rules, $messages);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }
}
