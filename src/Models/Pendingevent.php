<?php namespace Hdmaster\Core\Models\Pendingevent;

use Input;
use Validator;
use Flash;
use Lang;
use DateTime;
use \Facility;
use \Actor;
use \Proctor;
use \Observer;
use \Exam;
use \Skilltest;
use \Skillexam;
use \Discipline;

class Pendingevent extends \Eloquent
{
    
    protected $fillable = ['discipline_id', 'facility_id', 'proctor_id', 'actor_id', 'test_date', 'start_time',
                           'locked', 'is_paper', 'start_code', 'ended', 'comments'];
    
    public $errors;
    
    protected $update_seats_rules = [
        'seats'    => 'integer'
    ];

    /**
     * A test event belongs to a single facility
     * @return Relation
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * A test event belongs to a single test observer
     * @return Relation
     */
    public function observer()
    {
        return $this->belongsTo(Observer::class);
    }

    /**
     * A test event can have a proctor (which may also be an observer filling in)
     * @return Relation
     */
    public function proctor()
    {
        return $this->morphTo();
    }

    /**
     * A test event can have an actor (which may also be an observer filling in)
     * @return Relation
     */
    public function actor()
    {
        return $this->morphTo();
    }

    /**
     * A test event can feature multiple exams
     * @return Relation
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'pendingevent_exam')
                    ->withPivot('open_seats', 'reserved_seats', 'is_paper');
    }

    /**
     * Get all skill tests in an event
     * @return Relation
     */
    public function skills()
    {
        return $this->belongsToMany(Skillexam::class, 'pendingevent_skillexam')
                    ->withPivot('open_seats', 'reserved_seats');
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function getStatusClassAttribute($value)
    {
        $date = strtotime($this->test_date);
        if ($date === strtotime('today')) {
            return 'success';
        } elseif ($date === strtotime('tomorrow')) {
            return 'warning';
        } elseif ($date < strtotime('today')) {
            return 'muted';
        }

        return '';
    }

    public function getTestDateAttribute($value)
    {
        return empty($value) ? '' : date('m/d/Y', strtotime($value));
    }

    public function getStartTimeAttribute($value)
    {
        return empty($value) ? '' : date('g:i A T', strtotime($value));
    }

    public function getAll()
    {
        $search  = Input::get('search', null);

        $base = Pendingevent::with('skills', 'exams');
        if (! empty($search)) {
            $date = date('Y-m-d', strtotime($search));
            $base->where('test_date', $date);
        }

        return $base->get();
    }

    /**
     * Add a new Testevent
     */
    public function addWithInput()
    {
        $disciplineId = Input::get('discipline_id');
        $facilityId   = Input::get('facility_id');
        $testDates    = Input::get('test_date');
        $startTimes   = Input::get('start_time');
        
        $eventIds = array();

        // ensure datetime are arrays
        if (! is_array($testDates)) {
            $testDates = array($testDates);
        }
        if (! is_array($startTimes)) {
            $startTimes = array($startTimes);
        }

        foreach ($testDates as $i => $testDate) {
            $evt = new Pendingevent;

            $evt->discipline_id = $disciplineId;
            $evt->facility_id   = $facilityId;
            $evt->test_date     = $testDate == null ? null : date('Y-m-d', strtotime($testDate));
            $evt->start_time    = date('H:i:s', strtotime($startTimes[$i]));
            $evt->is_regional   = Input::get('is_regional', 0);
            $evt->is_paper      = Input::get('is_paper', 0);
            $evt->locked        = false;
            $evt->comments      = Input::get('comments');
            $evt->save();

            $this->updateSeats($evt);

            $eventIds[] = $evt->id;
        }

        return $eventIds;
    }

    /**
     * Update a pendingevent
     */
    public function updateWithInput()
    {
        \Eloquent::unguard();

        // new datetime
        $newDate = Input::get('test_date')[0];
        $newTime = Input::get('start_time')[0];
        // old testdate
        $oldDate = Input::get('pendingevent_testdate');

        // if test date is empty
        // remove any test team that was set
        // must have a test date to assign test team
        if (is_null($newDate) || $newDate != $oldDate) {
            $this->observer_id  = null;
            $this->proctor_id   = null;
            $this->proctor_type = null;
            $this->actor_id     = null;
            $this->actor_type   = null;
            $this->is_mentor    = false;
        }
        // update test team
        else {
            // observer set?
            if (Input::get('observer_id')) {
                list($observerId, $observerType) = explode('|', Input::get('observer_id'));

                $this->observer_id = $observerId;
            } else {
                $this->observer_id = null;
            }

            // proctor set?
            if (Input::get('proctor_id')) {
                list($proctorId, $proctorType) = explode('|', Input::get('proctor_id'));
                
                // current observer filling in 
                if ($proctorId == '-1' && isset($observerId)) {
                    $this->proctor_id   = $observerId;
                    $this->proctor_type = Observer::class;
                }
                // other observer filling in
                elseif ($proctorType == 'observer') {
                    $this->proctor_id   = $proctorId;
                    $this->proctor_type = Observer::class;
                }
                // proctor as proctor
                else {
                    $this->proctor_id   = $proctorId;
                    $this->proctor_type = Proctor::class;
                }
            } else {
                $this->proctor_id   = null;
                $this->proctor_type = null;
            }

            // actor set?
            if (Input::get('actor_id')) {
                list($actorId, $actorType) = explode('|', Input::get('actor_id'));

                // current observer filling in 
                if ($actorId == '-1' && isset($observerId)) {
                    $this->actor_id   = $observerId;
                    $this->actor_type = Observer::class;
                }
                // other observer filling in
                elseif ($actorType == 'observer') {
                    $this->actor_id   = $actorId;
                    $this->actor_type = Observer::class;
                }
                // actor as actor
                else {
                    $this->actor_id   = $actorId;
                    $this->actor_type = Actor::class;
                }
            } else {
                $this->actor_id   = null;
                $this->actor_type = null;
            }
        }

        // update datetime
        $this->test_date  = $newDate == null ? null : date('Y-m-d', strtotime($newDate));
        $this->start_time = $newTime == null ? null : date('H:i:s', strtotime($newTime));
        
        // update options
        $this->is_regional = Input::get('is_regional', 0);
        $this->is_paper    = Input::get('is_paper', 0);
        $this->is_mentor   = Input::get('is_mentor', 0);
        $this->comments    = Input::get('comments');

        // update seats
        $this->updateSeats($this->getModel());

        return $this->save();
    }

    /**
     * Update pendingevents knowledge and/or skill seats
     * Used on create and edit
     */
    private function updateSeats($evt)
    {
        $knowledgeSeats = Input::get('exam_seats');
        $skillSeats     = Input::get('skill_seats');

        // create event knowledge test records
        if ($knowledgeSeats) {
            foreach ($knowledgeSeats as $comboInfo => $numSeats) {
                // coming from events.create
                // exam seats are keyed examId+disciplineId
                // 
                // coming from events.edit
                // exam seats are keyed normally (examId only)
                if (strpos($comboInfo, '|') !== false) {
                    list($examId, $disciplineId) = explode('|', $comboInfo);
                } else {
                    $examId = $comboInfo;
                }

                // if this exam has seats listed
                if (! empty($numSeats)) {
                    $info = [
                        'open_seats'     => $numSeats,
                        'reserved_seats' => null
                    ];

                    if ($evt->exams()->find($examId) === null) {
                        $evt->exams()->attach($examId, $info);
                    } else {
                        $evt->exams()->updateExistingPivot($examId, $info);
                    }
                }
            }
        }

        // create event skill test records
        if ($skillSeats) {
            foreach ($skillSeats as $comboInfo => $numSeats) {
                if (strpos($comboInfo, '|') !== false) {
                    list($skillId, $disciplineId) = explode('|', $comboInfo);
                } else {
                    $skillId = $comboInfo;
                }

                // if this skillexam has seats listed
                if (! empty($numSeats)) {
                    $info = [
                        'open_seats'    => $numSeats,
                        'reserved_seats'=> null
                    ];

                    if ($evt->skills()->find($skillId) === null) {
                        $evt->skills()->attach($skillId, $info);
                    } else {
                        $evt->skills()->updateExistingPivot($skillId, $info);
                    }
                }
            }
        }
    }


    /**
     * Updates max seats for a Knowledge Test
     */
    public function updateKnowledgeSeats($exam_id, $new_seats)
    {
        $exam = Exam::find($exam_id);

        $pivot_row = $this->exams()->where('exam_id', '=', $exam_id)->first();
        $old_seats = $pivot_row->pivot->open_seats;    // get original seats
        $pivot_row->pivot->open_seats = $new_seats; // set new seats
        $pivot_row->pivot->save();
    }

    /**
     * Updates max seats for a Skill Test
     */
    public function updateSkillSeats($skill_id, $new_seats)
    {
        $skill = Skilltest::find($skill_id);

        $pivot_row = $this->skills()->where('skilltest_id', '=', $skill_id)->first();
        $old_seats = $pivot_row->pivot->open_seats;    // get original seats
        $pivot_row->pivot->open_seats = $new_seats; // set new seats
        $pivot_row->pivot->save();
    }

    /**
     * Whether an event's date has passed or not
     *
     * @return boolean 	- returns true if event is in past
     */
    public function getIsPastAttribute()
    {
        return strtotime($this->test_date) < strtotime(date('Y-m-d'));
    }

    /**
     * Before changing available skill seats in an event
     */
    public function validateSkillSeats($skill_id, $new_seats)
    {
        $rules = $this->update_seats_rules;

        $skill = Skilltest::find($skill_id);

        // get minimum number of seats (# people scheduled currently)
        $min_seats = $this->skillStudents()->where('skilltest_id', $skill_id)->count();
        $min_seat = $min_seats == 0 ? 1 : $min_seats;    // minimum of 1 seat always
        $rules['seats'] .= "|min:".$min_seats;

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    public function validateKnowledgeSeats($exam_id, $new_seats)
    {
        $rules = $this->update_seats_rules;

        $exam = Exam::find($exam_id);

        /** Commented out because pending events can't schedule anyone at this point **/
        // // get minimum number of seats (# people scheduled currently)
        // $min_seats = count($this->knowledgeStudents()->where('exam_id', '=', $exam_id)->get());
        // $min_seats = $min_seats == 0 ? 1 : $min_seats;	// minimum 1 seat always
        // $rules['seats'] .= "|min:".$min_seats;

        // get maximum number of seats (# of unique testforms)
        $maxSeats = $exam->active_testforms()->count();
        $rules['seats'] .= "|max:".$maxSeats;

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    public function validatePending()
    {
        // validate test team selection
        // if proctor_id or actor_id is -1, then observer must be non-zero 
        // (e.g there must be an observer selected if choosing observer filling in)
        if (Input::get('proctor_id') == '-1|observer' && Input::get('observer_id') == 0) {
            Flash::danger('Invalid '.Lang::choice('core::terms.proctor', 1).'. '.Lang::choice('core::terms.observer', 1).' must be selected.');
            return false;
        }
        if (Input::get('actor_id') == '-1|observer' && Input::get('observer_id') == 0) {
            Flash::danger('Invalid '.Lang::choice('core::terms.proctor', 1).'. '.Lang::choice('core::terms.observer', 1).' must be selected.');
            return false;
        }

        // validate test date
        // test date must be in the future
        if (Input::get('test_date.0')) {
            $testDate = Input::get('test_date.0');

            $date = new DateTime($testDate);
            $now  = new DateTime(date('Y-m-d'));

            if (! empty($testDate) && $date < $now) {
                Flash::danger('Invalid Test Date.');
                return false;
            }
        }
        
        // validate knowledge/skill exam seats
        if (Input::get('exam_seats')) {
            foreach (Input::get('exam_seats') as $examId => $seats) {
                $exam = Exam::with('active_testforms')->find($examId);
                $numForms = $exam->active_testforms->count();

                if ($numForms < intval($seats)) {
                    Flash::danger('Exam '.$exam->name.' is restricted to '.$numForms.' seats max due to available testforms.');
                    return false;
                }
            }
        }
        if (Input::get('skill_seats')) {
            foreach (Input::get('skill_seats') as $skillId => $seats) {
                $skill    = Skillexam::with('active_tests')->find($skillId);
                $numForms = $skill->active_tests->count();

                if ($numForms < intval($seats)) {
                    Flash::danger('Skill Exam '.$skill->name.' is restricted to '.$numForms.' seats max due to available testforms.');
                    return false;
                }
            }
        }

        return true;
    }

    // validate a pending event before fully publishing
    public function validatePublish()
    {
        $rules = [
            'discipline_id' => 'required|numeric',
            'observer_id'   => 'required|not_in:0',
            'facility_id'    => 'required|numeric|check_event_exams',
            'start_time'    => 'array_has_one',
            'test_date'        => 'array_has_one'
        ];

        // if any skill seats, an actor and proctor must be set
        // (could be set to 'Observer filling in')

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }
}
