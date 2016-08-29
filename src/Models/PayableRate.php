<?php namespace Hdmaster\Core\Models\PayableRate;

use Log;
use Input;
use Validator;

class PayableRate extends \Eloquent
{

    protected $table = 'payables_rates';

    protected $fillable = [
        'level_name',
        'knowledge_rate',
        'special_rate',
        'oral_rate',
        'skill_rate',
        'ada_rate'
    ];

    public $errors;

    protected static $rules = [
        'level_name'        => 'required',
        'knowledge_rate'    => 'required|numeric',
        'special_rate'      => 'required|numeric',
        'oral_rate'            => 'required|numeric',
        'skill_rate'        => 'required|numeric',
        'ada_rate'          => 'required|numeric'
    ];

    public function addWithInput()
    {
        $p = new PayableRate;
        $p->level_name = Input::get('level_name');
        $p->discipline_id = Input::get('discipline_id');
        $p->knowledge_rate = Input::get('knowledge_rate');
        $p->special_rate = Input::get('special_rate');
        $p->oral_rate = Input::get('oral_rate');
        $p->skill_rate = Input::get('skill_rate');
        $p->ada_rate = Input::get('ada_rate');

        if ($p->save()) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    public function updateWithInput()
    {
        $p = PayableRate::find(Input::get('id'));
        $p->discipline_id = Input::get('discipline_id');
        $p->level_name = Input::get('level_name');
        $p->knowledge_rate = Input::get('knowledge_rate');
        $p->special_rate = Input::get('special_rate');
        $p->oral_rate = Input::get('oral_rate');
        $p->skill_rate = Input::get('skill_rate');
        $p->ada_rate = Input::get('ada_rate');

        //dd($p);

        if ($p->save()) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
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
