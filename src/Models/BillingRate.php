<?php namespace Hdmaster\Core\Models\BillingRate;

use Log;
use Input;
use Validator;

class BillingRate extends \Eloquent
{

    protected $table = 'billing_rates';

    protected $fillable   = [
        'discipline_id',
        'svc_name',
        'test_type',
        'rate',
        'rate_ns'
    ];

    public $errors;

    protected static $rules = [
        'discipline_id' => 'required',
        'svc_name' => 'required',
        'test_type' => 'required',
        'rate' => 'required|numeric',
        'rate_ns' => 'required|numeric'
    ];

    public function addWithInput()
    {
        $b = new BillingRate;
        $b->discipline_id = Input::get('discipline_id');
        $b->svc_name = Input::get('svc_name');
        $b->test_type = Input::get('test_type');
        $b->rate = Input::get('rate');
        $b->rate_ns = Input::get('rate_ns');

        if ($b->save()) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    public function updateWithInput()
    {
        $b = BillingRate::find(Input::get('id'));
        $b->discipline_id = Input::get('discipline_id');
        $b->svc_name = Input::get('svc_name');
        $b->test_type = Input::get('test_type');
        $b->rate = Input::get('rate');
        $b->rate_ns = Input::get('rate_ns');
        
        if ($b->save()) {
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
