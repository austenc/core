<?php namespace Hdmaster\Core\Models\Certification;

use Input;
use Validator;
use \Training;
use \Exam;
use \Student;
use \Skillexam;
use \Facility;
use \Attainable;
use \Discipline;

class Certification extends \Eloquent
{
    use Attainable;

    protected $fillable = ['discipline_id', 'name', 'abbrev', 'comments'];
    public static $rules = [
        'discipline_id' => 'required:not_in:0',
        'name'          => 'required',
        'abbrev'        => 'required'
    ];
    public $errors;


    // relations
    public function required_exams()
    {
        return $this->belongsToMany(Exam::class, 'certification_exams', 'certification_id', 'exam_id');
    }
    public function required_skills()
    {
        return $this->belongsToMany(Skillexam::class, 'certification_skillexams', 'certification_id', 'skillexam_id');
    }
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_certification')
                    ->withPivot('certified_at', 'expires_at');
    }
    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function getRequiredTrainingsAttribute()
    {
        $reqTrainingIds = [];

        foreach ($this->required_exams()->get() as $exam) {
            $reqTrainingIds = array_merge($reqTrainingIds, $exam->required_trainings()->lists('id')->all());
        }

        foreach ($this->required_skills()->get() as $skill) {
            $reqTrainingIds = array_merge($reqTrainingIds, $skill->required_trainings()->lists('id')->all());
        }

        return Training::whereIn('id', array_unique($reqTrainingIds))->get();
    }

    public function addWithInput()
    {
        $c = Certification::create([
            'discipline_id' => Input::get('discipline_id'),
            'name'          => Input::get('name'),
            'abbrev'        => Input::get('abbrev'),
            'comments'        => Input::get('comments')
        ]);

        return $c->id;
    }
    public function updateWithInput()
    {
        $this->name     = Input::get('name');
        $this->abbrev   = Input::get('abbrev');
        $this->comments = Input::get('comments');

        // requirements
        $exams     = array_filter(Input::get('req_exam_id'));
        $skills    = array_filter(Input::get('req_skill_id'));

        $this->required_exams()->sync([]);
        if (! empty($exams)) {
            $this->required_exams()->sync(array_keys($exams));
        }
            
        $this->required_skills()->sync([]);
        if (! empty($skills)) {
            $this->required_skills()->sync(array_keys($skills));
        }

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
