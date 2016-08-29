<?php namespace Hdmaster\Core\Models\Testform;

use Input;
use Validator;
use Config;
use \Testitem;
use \Testplan;
use \Sorter;

class Testform extends \Eloquent
{

    use \ClientOnlyTrait;

    protected $guarded = ['id'];

    // Validation rules
    public static $rules = [
        'name' => 'required'
    ];

    public $errors;

    /**
     * A testform has many testitems
     */
    public function testitems()
    {
        return $this->belongsToMany(Testitem::class)->withPivot('ordinal')->orderBy('testform_testitem.ordinal');
    }

    /**
     * A testform belongs to a testplan
     */
    public function testplan()
    {
        return $this->hasOne(Testplan::class, 'id', 'testplan_id');
    }

    // makes oral / spanish fields return Y / N instead of 0/1
    public function getOralAttribute($value)
    {
        return $value == 1 ? 'Y' : 'N';
    }

    public function getSpanishAttribute($value)
    {
        return $value == 1 ? 'Y' : 'N';
    }

    /**
     * [handleSearch description]
     * @return [type] [description]
     */
    public function handleSearch()
    {
        // search options
        $status = Input::get('status');
        $search = Input::get('search');
        $type   = Input::get('search_type');

        // get all testforms along with associated testplan it was generated from
        $base = Testform::with('testplan');

        // add search params
        switch ($type) {
            case 'Name':
                $base->where('name', 'LIKE', '%'.$search.'%');
                break;
            default:
                break;
        }

        $baseAll    = clone $base;
        $baseActive = clone $base;
        $baseDraft  = clone $base;
        $baseArch   = clone $base;

        // counts for sidebar by status
        $r['count']['all']      = $baseAll->count();
        $r['count']['active']   = $baseActive->where('status', 'active')->count();
        $r['count']['draft']    = $baseDraft->where('status', 'draft')->count();
        $r['count']['archived'] = $baseArch->where('status', 'archived')->count();

        // add a status flag if it's set so we can see all of one type
        if ($status && in_array($status, ['active', 'draft', 'archived'])) {
            $base = $base->where('status', $status);
        }

        // order by		
        $base = $base->orderBy(Input::get('sort', 'status'), Sorter::order());

        // set the result
        $r['forms'] = $base->paginate(Config::get('paginate.default'));

        return $r;
    }

    // Validation
    public function validate()
    {
        $rules = static::$rules;

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    public function getStatusClassAttribute()
    {
        if ($this->status == 'draft') {
            return 'warning';
        } elseif ($this->status == 'active') {
            return 'success';
        } elseif ($this->status == 'archived') {
            return 'muted';
        } else {
            return '';
        }
    }
}
