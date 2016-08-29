<?php namespace Hdmaster\Core\Models\Discipline;

use Log;
use Input;
use Validator;
use \Certification;
use \Training;
use \Exam;
use \Skillexam;
use \Facility;
use \Instructor;
use \Observer;
use \Testevent;

class Discipline extends \Eloquent
{
    protected $fillable   = ['name', 'abbrev', 'description'];

    protected static $rules = [
        'abbrev'    => 'required',
        'name'        => 'required'
    ];

    public $errors;

    /**
     * Constructor
     */
    public function __construct($attributes = [])
    {
    }

    public function certification()
    {
        return $this->hasMany(Certification::class);
    }

    public function training()
    {
        return $this->hasMany(Training::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function skills()
    {
        return $this->hasMany(Skillexam::class);
    }


    /**
     * All Facilities including those that have been deactivated for the discipline
     */
    public function allFacilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_discipline')->withPivot('tm_license', 'old_license')->orderBy('name', 'ASC');
    }

    /**
     * Only active Facilities under the current discipline 
     */
    public function facilities()
    {
        return $this->allFacilities()->where('facility_discipline.active', true);
    }

    /**
     * Only active Test Sites under the current discipline
     */
    public function testSites()
    {
        return $this->facilities()->where('actions', 'LIKE', '%Testing%');
    }

    /**
     * Only active Training Programs under the current discipline
     */
    public function trainingPrograms()
    {
        return $this->facilities()->where('actions', 'LIKE', '%Training%');
    }

    /**
     * agency_only training programs 
     */
    public function agencyTrainingPrograms()
    {
        return $this->trainingPrograms()->where('agency_only', true);
    }

    /**
     * Trainers
     */
    public function instructors()
    {
        return $this->morphedByMany(Instructor::class, 'person', 'facility_person');
    }

    /**
     * Testing Team
     */
    public function observers()
    {
        return $this->morphedByMany(Observer::class, 'person', 'facility_person');
    }
    public function proctors()
    {
        return $this->morphedByMany(Observer::class, 'person', 'facility_person');
    }
    public function actors()
    {
        return $this->morphedByMany(Observer::class, 'person', 'facility_person');
    }

    public function events()
    {
        return $this->hasMany(Testevent::class)->orderBy('test_date', 'DESC');
    }

    public function getNameWithAbbrevAttribute()
    {
        return $this->name.'<br><small>'.$this->abbrev.'</small>';
    }

    public function addWithInput()
    {
        $d = new Discipline;

        $d->name        = Input::get('name');
        $d->abbrev      = strtoupper(Input::get('abbrev'));
        $d->description = Input::get('description');
        $d->save();

        return $d->id;
    }
    
    public function updateWithInput()
    {
        $this->abbrev      = strtoupper(Input::get('abbrev'));
        $this->name        = Input::get('name');
        $this->description = Input::get('description');

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

    public function validateEdit()
    {
        $r = [
            'abbrev' => 'required',
            'name'   => 'required|unique:disciplines,name,' . $this->id
        ];

        $v = Validator::make($this->attributes, $r);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();
        return false;
    }
}
