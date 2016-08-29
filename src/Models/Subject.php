<?php namespace Hdmaster\Core\Models\Subject;

use Illuminate\Database\Eloquent\SoftDeletes;
use Input;
use Validator;
use Config;
use \Testitem;
use \Exam;

class Subject extends \Eloquent
{
    use SoftDeletes;
    use \ClientOnlyTrait;

    protected $table = 'subjects';
    protected $fillable = ['name', 'exam_id', 'old_number', 'report_as', 'client'];

    public static $rules = [
        'exam_id'    => 'required|integer|not_in:0',
        'name'       => 'required',
        'report_as'  => 'integer',
        'old_number' => 'integer'
    ];
    
    public $errors;

    /**
     * A subject is associated with many test items
     */
    public function testitems()
    {
        return $this->belongsToMany(Testitem::class, 'exam_testitem', 'subject_id', 'testitem_id')
            ->where('exam_testitem.client', '=', Config::get('core.client.abbrev'));
    }

    /**
     * 'Active' status testitems associated with this subject
     */
    public function activeTestitems()
    {
        return $this->belongsToMany(Testitem::class, 'exam_testitem', 'subject_id', 'testitem_id')
            ->where('testitems.status', 'active')
            ->where('exam_testitem.client', '=', Config::get('core.client.abbrev'));
    }

    /**
     * Subject belongs to an exam
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Subject may report under a different Subject
     */
    public function reportAs()
    {
        return $this->hasOne(Subject::class, 'id', 'report_as');
    }

    /**
     * Gets a map of all exams and their subjects from current state
     */
    public function clientSubjectMap()
    {
        $map = array();

        // get all exams with subjects
        $exams = Exam::with('subjects')->get();

        if ($exams) {
            foreach ($exams as $exam) {
                $oldNumbers = $exam->subjects->lists('old_number', 'id')->all();

                foreach ($oldNumbers as $subjectId => $oldNum) {
                    $map[$exam->id][$subjectId] = $oldNum;
                }
            }
        }

        return $map;
    }

    /**
     * Create new Subject
     */
    public function addWithInput()
    {
        $subject = Subject::create([
            'exam_id'    => Input::get('exam_id'),
            'name'       => Input::get('name'),
            'old_number' => Input::get('old_number'),
            'client'     => Config::get('core.client.abbrev')
        ]);

        if (is_null($subject)) {
            return false;
        }

        return $subject->id;
    }

    /**
     * Update existing Subject
     */
    public function updateWithInput()
    {
        $this->name       = Input::get('name');
        $this->old_number = Input::get('old_number') ? Input::get('old_number') : null;
        $this->report_as  = Input::get('report_as') == 0 ? null : Input::get('report_as');

        return $this->save();
    }
    
    public function validate()
    {
        $rules = static::$rules;

        $messages = [
            'exam_id.required'   => 'Invalid Exam selected.',
            'exam_id.not_in'     => 'Invalid Exam selected.',
            'exam_id.integer'    => 'Invalid Exam selected.',
            'old_number.integer' => 'Old Subject # must be an integer.'
        ];

        $v = Validator::make(Input::all(), $rules, $messages);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }

    /**
     * Gets a collection of all testitems for this subject + any testitems that
     * belong to subjects which 'report_as' this one
     */
    public function getItemPoolAttribute()
    {
        // grab this subject's items first
        $baseItems = $this->testitems;

        // grab any subjects where report_as = this.id
        $reportingAs = Subject::with('activeTestitems')
            ->where('exam_id', $this->exam_id)
            ->where('report_as', $this->id)->get();

        foreach ($reportingAs as $s) {
            $baseItems = $baseItems->merge($s->testitems);
        }

        return $baseItems;
    }
}
