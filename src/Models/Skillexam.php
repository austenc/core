<?php namespace Hdmaster\Core\Models\Skillexam;

use Validator;
use Input;
use \Training;
use \Exam;
use \Skillattempt;
use \Skilltest;
use \Skilltask;
use \Testevent;
use \Discipline;
use \Attainable;

class Skillexam extends \Eloquent
{
    use Attainable;

    protected $fillable = ['discipline_id', 'price', 'name', 'abbrev', 'slug', 'max_attempts', 'comments'];
    public $errors;
    public static $rules = [
        'discipline_id'   => 'required|not_in:0',
        'name'            => 'required',
        'abbrev'          => 'required|alpha|min:3',
        'req_training_id' => 'array',
        'req_exam_id'     => 'array',
        'max_attempts'    => 'required|integer|not_in:0'
    ];

    // relations
    public function required_exams()
    {
        return $this->belongsToMany(Exam::class, 'skillexam_exam_requirements')
            ->where('status', 'prereq')
            ->withPivot('status');
    }
    public function required_trainings()
    {
        return $this->belongsToMany(Training::class, 'skillexam_training_requirements');
    }
    public function corequired_exams()
    {
        return $this->belongsToMany(Exam::class, 'skillexam_exam_requirements')
            ->where('status', 'coreq')
            ->withPivot('status');
    }
    public function tests()
    {
        return $this->hasMany(Skilltest::class);
    }
    public function active_tests()
    {
        return $this->hasMany(Skilltest::class)->where('status', 'active');
    }
    public function tasks()
    {
        return $this->belongsToMany(Skilltask::class, 'skillexam_tasks');
    }
    public function attempts()
    {
        return $this->hasMany(Skillattempt::class);
    }
    public function events()
    {
        return $this->belongsToMany(Testevent::class, 'testevent_skillexam')->withPivot('open_seats', 'reserved_seats');
    }
    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }


    // accessors
    public function getPrettyNameAttribute()
    {
        return $this->name.'<br><small>Skill</small>';
    }


    public function addWithInput()
    {
        $name   = Input::get('name');
        $abbrev = Input::get('abbrev');

        // default abbrev
        if (empty($abbrev)) {
            $abbrev = '';
            $words = preg_split("/\s+/", $name);

            foreach ($words as $w) {
                $abbrev .= $w[0];
            }
        }

        $skillexam                = new Skillexam;
        $skillexam->discipline_id = Input::get('discipline_id');
        $skillexam->name          = $name;
        $skillexam->abbrev        = $abbrev;
        $skillexam->price         = Input::get('price');
        $skillexam->max_attempts  = Input::get('max_attempts');
        $skillexam->comments      = Input::get('comments');
        $skillexam->save();

        return $skillexam->id;
    }

    public function updateWithInput()
    {
        $this->name         = Input::get('name');
        $this->price        = Input::get('price');
        $this->abbrev       = Input::get('abbrev');
        $this->max_attempts = Input::get('max_attempts');
        $this->comments     = Input::get('comments');
        $this->save();

        // requirements
        $trainings = Input::get('req_training') ? array_filter(Input::get('req_training')) : [];
        $exams     = Input::get('req_exam') ? array_filter(Input::get('req_exam')) : [];

        // required trainings
        $this->required_trainings()->sync([]);
        if (! empty($trainings)) {
            $this->required_trainings()->sync(array_keys($trainings));
        }

        // get all previous corequirements (before updating)
        // handle all missing exams (previously were corequirements, but now are not, remove inverse)
        $prevCoreqExamIds = $this->corequired_exams()->lists('id')->all();
        foreach ($prevCoreqExamIds as $examId) {
            if (empty($exams) || ! in_array($examId, array_keys($exams))) {
                Exam::find($examId)->corequired_skills()->detach($this->id);
            }
        }

        // exams
        $this->required_exams()->sync([]);
        if (! empty($exams)) {
            foreach ($exams as $examId => $reqType) {
                $reqType = ($reqType == 2) ? 'coreq' : 'prereq';

                $this->required_exams()->attach($examId, ['status' => $reqType]);

                // if corequired skillexam
                // setup inverse relation
                if ($reqType == 'coreq') {
                    // get exam
                    $currExam = Exam::with([
                        'required_skills',
                        'corequired_skills'
                    ])->find($examId);

                    // inverse already exists
                    if ($currExam->corequired_skills->contains($this->id)) {
                        continue;
                    }

                    // inverse exists as a prerequirement, set as corequirement
                    elseif ($currExam->required_skills->contains($this->id)) {
                        $currExam->required_skills()->detach($this->id);
                        $currExam->corequired_skills()->attach($this->id, ['status' => $reqType]);
                    }

                    // wasnt previously a corequirement OR prerequirement (add as new coreq)
                    else {
                        $currExam->corequired_skills()->attach($this->id, ['status' => $reqType]);
                    }
                }
            }
        }

        return true;
    }

    public function validate()
    {
        $rules = static::$rules;

        $messages = [
            'discipline_id.required' => 'Invalid Discipline selected.',
            'discipline_id.not_in'   => 'Invalid Discipline selected.',
            'max_attempts.required'  => 'Max Attempts must be 1 or more',
            'max_attempts.not_in'    => 'Max Attempts must be 1 or more',
            'max_attempts.integer'   => 'Max Attempts must be an Integer'
        ];

        $v = Validator::make(Input::all(), $rules, $messages);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }
}
