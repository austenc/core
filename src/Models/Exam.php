<?php namespace Hdmaster\Core\Models\Exam;

use Config;
use Input;
use Validator;
use \Subject;
use \Testplan;
use \Testform;
use \Testitem;
use \Testevent;
use \Training;
use \Skillexam;
use \Discipline;
use \Attainable;

class Exam extends \Eloquent
{
    use Attainable;

    protected $fillable = ['discipline_id', 'name', 'abbrev', 'has_paper', 'max_attempts', 'price', 'assisted_price', 'comments'];
    public static $rules = [
        'discipline_id'   => 'required|not_in:0',
        'name'            => 'required',
        'abbrev'          => 'required',
        'has_paper'       => 'required',
        'req_training_id' => 'array',
        'req_exam_id'     => 'array',
        'req_skill_id'    => 'array',
        'max_attempts'    => 'required|integer|not_in:0'
    ];
    public $errors;


    // relations
    public function events()
    {
        return $this->belongsToMany(Testevent::class, 'testevent_exam')->withPivot('open_seats', 'reserved_seats', 'is_paper');
    }
    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    // test content
    public function subjects()
    {
        return $this->hasMany(Subject::class)->where('subjects.client', '=', Config::get('core.client.abbrev'));
    }
    public function testplans()
    {
        return $this->hasMany(Testplan::class)->where('testplans.client', '=', Config::get('core.client.abbrev'));
    }
    public function testitems()
    {
        return $this->belongsToMany(Testitem::class, 'exam_testitem', 'exam_id', 'testitem_id')
                    ->where('exam_testitem.client', '=', Config::get('core.client.abbrev'));
    }

    // testforms
    public function testforms()
    {
        return $this->hasMany(Testform::class)->where('testforms.client', '=', Config::get('core.client.abbrev'));
    }
    public function active_testforms()
    {
        return $this->testforms()->where('status', '=', 'active');
    }
    public function oralTestforms()
    {
        return $this->active_testforms()->where('oral', '=', true);
    }

    // requirements
    public function required_exams()
    {
        return $this->belongsToMany(\Exam::class, 'exam_requirements', 'exam_id', 'req_exam_id');
    }
    public function required_trainings()
    {
        return $this->belongsToMany(Training::class, 'exam_training_requirements', 'exam_id', 'training_id');
    }
    public function required_skills()
    {
        return $this->belongsToMany(Skillexam::class, 'exam_skill_requirements')
            ->where('status', 'prereq')
            ->withPivot('status');
    }
    public function corequired_skills()
    {
        return $this->belongsToMany(Skillexam::class, 'exam_skill_requirements')
            ->where('status', 'coreq')
            ->withPivot('status');
    }


    // accessors
    public function getPrettyNameAttribute()
    {
        return $this->name.'<br><small>Knowledge</small>';
    }
    public function getNameWithAbbrevAttribute()
    {
        return $this->name.'<br><small>'.$this->abbrev.'</small>';
    }


    public function addWithInput()
    {
        $exam = new Exam;

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
        
        $exam->discipline_id = Input::get('discipline_id');
        $exam->name          = $name;
        $exam->abbrev        = strtoupper($abbrev);
        $exam->price         = Input::get('price');
        $exam->max_attempts  = Input::get('max_attempts');
        $exam->has_paper     = Input::get('has_paper');
        $exam->comments      = Input::get('comments');
        $exam->save();

        return $exam->id;
    }

    public function updateWithInput()
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

        $this->name         = $name;
        $this->abbrev       = $abbrev;
        $this->price        = Input::get('price');
        $this->max_attempts = Input::get('max_attempts');
        $this->has_paper    = Input::get('has_paper');
        $this->comments     = Input::get('comments');
        $this->save();

        // requirements
        $trainings = Input::get('req_training_id') ? array_filter(Input::get('req_training_id')) : [];
        $exams     = Input::get('req_exam_id') ? array_filter(Input::get('req_exam_id')) : [];
        $skills    = Input::get('req_skill_id') ? array_filter(Input::get('req_skill_id')) : [];

        // trainings
        $this->required_trainings()->sync([]);
        if (! empty($trainings)) {
            $this->required_trainings()->sync(array_keys($trainings));
        }
        // exams
        $this->required_exams()->sync([]);
        if (! empty($exams)) {
            $this->required_exams()->sync(array_keys($exams));
        }

        // skillexams
        // get all previous corequirements (before updating)
        // handle missing coreqs (previously were coreqs, but now are not, remove inverse)
        $prevCoreqSkillIds = $this->corequired_skills()->lists('id')->all();
        foreach ($prevCoreqSkillIds as $skillId) {
            if (empty($skills) || ! in_array($skillId, array_keys($skills))) {
                Skillexam::find($skillId)->corequired_exams()->detach($this->id);
            }
        }
        
        // skillexams
        $this->required_skills()->sync([]);
        if (! empty($skills)) {
            foreach ($skills as $skillId => $reqType) {
                $reqType = ($reqType == 2) ? 'coreq' : 'prereq';

                $this->required_skills()->attach($skillId, ['status' => $reqType]);

                // if corequired skillexam
                // setup inverse relation
                if ($reqType == 'coreq') {
                    // get skillexam
                    $currSkillexam = Skillexam::with([
                        'required_exams',
                        'corequired_exams'
                    ])->find($skillId);

                    // inverse already exists
                    if ($currSkillexam->corequired_exams->contains($this->id)) {
                        continue;
                    }

                    // inverse exists as a prerequirement, set as corequirement
                    elseif ($currSkillexam->required_exams->contains($this->id)) {
                        $currSkillexam->required_exams()->detach($this->id);
                        $currSkillexam->corequired_exams()->attach($this->id, ['status' => $reqType]);
                    }

                    // wasnt previously a corequirement OR prerequirement (add as new coreq)
                    else {
                        $currSkillexam->corequired_exams()->attach($this->id, ['status' => $reqType]);
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
