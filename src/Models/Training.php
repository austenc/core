<?php namespace Hdmaster\Core\Models\Training;

use Validator;
use Input;
use \Instructor;
use \Student;
use \Attainable;
use \Discipline;

class Training extends \Eloquent
{
    use Attainable;

    protected $fillable = ['discipline_id', 'name', 'abbrev', 'valid_for', 'price', 'classroom_hours', 'distance_hours', 'lab_hours', 'traineeship_hours', 'comments'];
    public static $rules = [
        'discipline_id'     => 'required|not_in:0',
        'name'              => 'required',
        'abbrev'            => 'required',
        'valid_for'         => 'required|integer',
        'classroom_hours'   => 'numeric',
        'distance_hours'    => 'numeric',
        'lab_hours'         => 'numeric',
        'traineeship_hours' => 'numeric',
        'req_training_id'   => 'array'
    ];
    public $errors;


    // relations
    public function instructors()
    {
        return $this->belongsToMany(Instructor::class, 'instructor_training');
    }
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_training')->withTimestamps();
    }
    public function required_trainings()
    {
        return $this->belongsToMany(Training::class, 'training_requirements', 'training_id', 'req_training_id');
    }
    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }


    // pivot accessor to check if a single training by student is expired or not
    public function getIsExpiredAttribute()
    {
        if (empty($this->pivot->expires)) {
            return false;
        }

        return strtotime($this->pivot->expires) < time();
    }

    public function getIsArchivedAttribute()
    {
        return ! empty($this->pivot->archived_at);
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

    /**
     * Determine expiration date for a new training given
     * Completed is passed training
     */
    public function getTrainingExpiration($completed)
    {
        if (empty($completed)) {
            return null;
        }

        $completed   = new \DateTime($completed);
        $completed   = $completed->modify("+ ".$this->valid_for." months");

        return $completed->format('Y-m-d');
    }

    public function updateWithInput()
    {
        $abbrev = Input::get('abbrev');
        $name   = Input::get('name');

        // default abbrev
        if (empty($abbrev)) {
            $abbrev = '';
            $words = preg_split("/\s+/", $name);

            foreach ($words as $w) {
                $abbrev .= $w[0];
            }
        }

        $this->name            = $name;
        $this->abbrev          = strtoupper($abbrev);
        $this->price           = Input::get('price');
        $this->valid_for       = Input::get('valid_for');
        $this->classroom_hours = Input::get('classroom_hours') ? Input::get('classroom_hours') : null;
        $this->distance_hours  = Input::get('distance_hours') ? Input::get('distance_hours') : null;
        $this->lab_hours       = Input::get('lab_hours') ? Input::get('lab_hours') : null;
        $this->clinical_hours  = Input::get('clinical_hours') ? Input::get('clinical_hours') : null;
        $this->comments        = Input::get('comments') ? Input::get('comments') : null;
        
        // require trainings
        $this->required_trainings()->sync([]);
        $trainings = Input::get('req_training_id') ? array_filter(Input::get('req_training_id')) : [];
        if (! empty($trainings)) {
            $this->required_trainings()->sync(array_keys($trainings));
        }

        return $this->save();
    }

    public function addWithInput()
    {
        $training = new Training;

        $abbrev = Input::get('abbrev');
        $name   = Input::get('name');

        // default abbrev
        if (empty($abbrev)) {
            $abbrev = '';
            $words = preg_split("/\s+/", $name);

            foreach ($words as $w) {
                $abbrev .= $w[0];
            }
        }

        $training->discipline_id   = Input::get('discipline_id');
        $training->name            = $name;
        $training->abbrev          = strtoupper($abbrev);
        $training->price           = Input::get('price');
        $training->valid_for       = Input::get('valid_for');
        $training->classroom_hours = Input::get('classroom_hours') ? Input::get('classroom_hours') : null;
        $training->distance_hours  = Input::get('distance_hours') ? Input::get('distance_hours') : null;
        $training->lab_hours       = Input::get('lab_hours') ? Input::get('lab_hours') : null;
        $training->clinical_hours  = Input::get('clinical_hours') ? Input::get('clinical_hours') : null;
        $training->save();
        
        return $training->id;
    }
}
