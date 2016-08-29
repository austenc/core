<?php namespace Hdmaster\Core\Models\Testevent;

use Auth;
use Lang;
use Input;
use Event;
use Config;
use View;
use Flash;
use \Sorter;
use DateTime;
use Validator;
use Session;
use Mail;
use Paginator;
use \Student;
use \Discipline;
use \Instructor;
use \Facility;
use \Testattempt;
use \Skillattempt;
use \Exam;
use \Proctor;
use \Observer;
use \Skilltest;
use \Skillexam;
use \User;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Testevent extends \Eloquent
{
    use SoftDeletes;

    protected $dates    = ['deleted_at'];
    protected $with     = ['facility'];
    protected $fillable = [
        'discipline_id',
        'facility_id',
        'observer_id',
        'proctor_id',
        'proctor_type',
        'actor_id',
        'actor_type',
        'test_date',
        'start_time',
        'locked',
        'is_paper',
        'is_regional',
        'start_code',
        'ended',
        'comments',
        'is_mentor'
    ];
    public $errors;

    public static $rules = [
        'facility_id'    => 'required|array_has_one',
        'start_time'    => 'array_has_one',
        'test_date'        => 'array_has_one',
        'discipline_id' => 'required|numeric|not_in:0'
    ];
    public static $fill_seats_rules = [
        'student_id'    => 'required'
    ];
    protected $update_seats_rules = [
        'seats'            => 'integer'
    ];
    protected $schedule_rules = [
        'exam_id'        => 'event_exam_has_seats|event_exam_has_testforms'
    ];


    public function facility()
    {
        return $this->belongsTo(Facility::class)->withTrashed();
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function allTestattempts()
    {
        return $this->hasMany(Testattempt::class);
    }

    public function testattempts()
    {
        return $this->allTestattempts()->where('testattempts.status', '!=', 'rescheduled');
    }

    public function allSkillattempts()
    {
        return $this->hasMany(Skillattempt::class);
    }

    public function skillattempts()
    {
        return $this->allSkillattempts()->where('skillattempts.status', '!=', 'rescheduled');
    }

    public function observer()
    {
        return $this->belongsTo(Observer::class)->withTrashed();
    }

    /**
     * A test event can have a proctor (which may also be an observer filling in)
     */
    public function proctor()
    {
        return $this->morphTo();
    }

    /**
     * A test event can have an actor (which may also be an observer filling in)
     */
    public function actor()
    {
        return $this->morphTo();
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'testevent_exam')
                    ->withPivot('open_seats', 'reserved_seats', 'is_paper');
    }

    public function skills()
    {
        return $this->belongsToMany(Skillexam::class, 'testevent_skillexam')
                    ->withPivot('open_seats', 'reserved_seats');
    }

    /**
     * Get all students currently scheduled for a knowledge test in this Event
     */
    public function knowledgeStudents()
    {
        return $this->belongsToMany(Student::class, 'testattempts')
                    ->withPivot('id',
                                'printed_by',
                                'testevent_id',
                                'facility_id',
                                'student_training_id',
                                'student_id',
                                'exam_id',
                                'testform_id',
                                'status',
                                'is_oral',
                                'archived',
                                'seat_type',
                                'payment_status')
                    ->withTimestamps()
                    ->where('testattempts.status', '!=', 'rescheduled');
    }

    /**
     * Get all students currently scheduled for a skill test in this event
     */
    public function skillStudents()
    {
        return $this->belongsToMany(Student::class, 'skillattempts')
                    ->withPivot('id',
                                'printed_by',
                                'skillexam_id',
                                'skilltest_id',
                                'student_id',
                                'testevent_id',
                                'facility_id',
                                'student_training_id',
                                'funding_source',
                                'attendance',
                                'archived',
                                'start_time',
                                'end_time',
                                'status',
                                'payment_status')
                    ->withTimestamps()
                    ->where('skillattempts.status', '!=', 'rescheduled');
    }

    public function getHasPendingADAStudentAttribute()
    {
        $evtStudents = $this->students;

        if (! $evtStudents->isEmpty()) {
            foreach ($evtStudents as $s) {
                if (! $s->pendingAdas->isEmpty()) {
                    return $s;
                }
            }
        }

        return false;
    }

    /**
     * Checks if any scheduled Event Skills contain NULL testforms
     */
    public function getHasNullTestformAttribute()
    {
        $c = 0;

        if (! $this->knowledgeStudents->isEmpty()) {
            foreach ($this->knowledgeStudents as $s) {
                if (! $s->pivot->testform_id) {
                    $c++;
                }
            }
        }

        return $c > 0 ?: false;
    }

    /**
     * Checks if any scheduled Event Skills contain NULL skilltests
     */
    public function getHasNullSkilltestAttribute()
    {
        $c = 0;

        if (! $this->skillStudents->isEmpty()) {
            foreach ($this->skillStudents as $s) {
                if (! $s->pivot->skilltest_id) {
                    $c++;
                }
            }
        }

        return $c > 0 ?: false;
    }

    /**
     * Determines if this event can be locked
     */
    public function getLockableAttribute()
    {
        // cant lock an already locked event
        if ($this->locked) {
            return false;
        }

        // user must have permission
        if (! Auth::user()->can('events.lock')) {
            return false;
        }

        // Is this an observer? If so, they can lock online events within a certain time period
        if (Auth::user()->isRole('Observer')) {
            $buffer     = '-' . Config::get('core.observerLockPeriod') . ' days';
            $today      = date('Y-m-d');
            $daysBefore = date('Y-m-d', strtotime($this->getOriginal('test_date') . ' ' . $buffer));

            // If it's not within the buffer period, or this event is paper, they can't lock it
            if ($today < $daysBefore || $this->is_paper) {
                return false;
            }
        }
        
        // each scheduled student must have an assigned test
        if ($this->hasNullTestform || $this->hasNullSkilltest) {
            return false;
        }

        // each scheduled student must have a unique assigned test (i.e. no duplicate tests)
        // allow admin user to proceed with a duplicate test event
        if (($this->hasDuplicateTestforms || $this->hasDuplicateSkilltests) && ! Auth::user()->isRole('Admin')) {
            return false;
        }

        // test should not proceed if a scheduled student has a pending ADA
        if ($this->hasPendingADAStudent) {
            return false;
        }

        return true;
    }

    /**
     * Returns # of oral students in event
     * (Currently oral only available for paper event so only need to search knowledge students 5/10)
     */
    public function getHasOralStudentsAttribute()
    {
        $students = $this->knowledgeStudents;
        $count    = 0;

        if (! $students->isEmpty()) {
            foreach ($students as $s) {
                if ($s->pivot->is_oral) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Goes thru each event exam and looks if it contains duplicate assigned testforms
     */
    public function getHasDuplicateTestformsAttribute()
    {
        foreach ($this->exams as $exam) {
            // get all scheduled attempts for this exam
            $examAttempts = $this->testattempts->filter(function ($att) use ($exam) {
                return $att->exam_id == $exam->id;
            });

            $attemptIds = ! $examAttempts->isEmpty() ? array_filter($examAttempts->lists('testform_id')->all()) : [];

            if (array_unique($attemptIds) != $attemptIds) {
                return $exam->id;
            }
        }

        return false;
    }

    /**
     * Goes thru each event exam and looks if it contains duplicate assigned testforms
     */
    public function getHasDuplicateSkilltestsAttribute()
    {
        foreach ($this->skills as $skill) {
            // get all scheduled attempts for this skillexam
            $skillAttempts = $this->skillattempts->filter(function ($att) use ($skill) {
                return $att->skillexam_id == $skill->id;
            });

            $skillAttemptIds = ! $skillAttempts->isEmpty() ? array_filter($skillAttempts->lists('skilltest_id')->all()) : [];
    
            if (array_unique($skillAttemptIds) != $skillAttemptIds) {
                return $skill->id;
            }
        }

        return false;
    }


    /**
     * All knowledge and skill students in this event
     */
    public function getStudentsAttribute($value)
    {
        // get all skill and knowledge students
        $students  = new Collection;
        $exclude   = []; // skillattempt_ids to exclude 
        $knowledge = $this->knowledgeStudents()->get();
        $skill     = $this->skillStudents()->get();

        // Loop through knowledge attempts
        foreach ($knowledge as $student) {
            $s            = $student;
            $s->knowledge = $student->pivot;
            $s->skill     = null; // default

            // does this student have a skill test as well?
            $hasSkill = $skill->filter(function ($skillStudent) use ($student) {
                return $skillStudent->id == $student->id;
            })->first();

            // The student has a skill			
            if ($hasSkill) {
                $s->skill  = $hasSkill->pivot;
                $exclude[] = $s->skill->id;
            }

            // now add it to our collection
            $students->push($s);
        }

        // Loop through skill attempts
        foreach ($skill as $student) {
            // if this person didn't have a knowledge too
            if (! in_array($student->pivot->id, $exclude)) {
                $s            = $student;
                $s->knowledge = null;
                $s->skill     = $student->pivot;
                $students->push($s);
            }
        }

        return $students;
    }

    /**
     * Events that are happening today
     */
    public function scopeToday($query)
    {
        return $query->where('test_date', '>=', new DateTime('today'))
        ->where('test_date', '<', new DateTime('tomorrow'))
        ->orderBy('start_time');
    }

    /**
     * Events that are upcoming
     */
    public function scopeUpcoming($query)
    {
        return $query->where('test_date', '>', new DateTime('today'))
        ->orderBy('test_date');
    }

    // Assigned knowledge tests
    public function assigned_knowledge()
    {
        return $this->knowledgeStudents()->wherePivot('status', 'assigned');
    }

    // Assigned skill tests
    public function assigned_skills()
    {
        return $this->skillStudents()->wherePivot('status', 'assigned');
    }

    /**
     * Gets all events depending on which type of person is logged in
     * @return mixed
     */
    public function getAll($pastOnly = false)
    {
        $user = Auth::user();

        $search     = Input::get('search', null);
        $searchType = Input::get('type', null);

        $base = $this->with([
            'facility',
            'facility.disciplines',
            'exams',
            'skills',
            'discipline',
            'testattempts',
            'skillattempts'
        ]);
        
        if ($user->ability(['Admin', 'Staff', 'Agency'], [])) {
            // show all events
            $events = $base->with('assigned_knowledge', 'assigned_skills');
        } elseif ($user->isRole('Proctor')) {
            $proctor = Proctor::where('user_id', $user->id)->first();
            $events  = $base->where('proctor_id', $proctor->id)->where('proctor_type', '=', 'Proctor');
        } elseif ($user->isRole('Observer')) {
            $observer = Observer::where('user_id', $user->id)->first();
            $events   = $base->where('observer_id', $observer->id);
        } elseif ($user->isRole('Facility')) {
            $facility = Facility::where('user_id', $user->id)->first();
            $events   = $base->where('facility_id', $facility->id)->where('discipline_id', Session::get('discipline.id'));
        } else {
            // should never get here
            return [];
        }

        // searching?
        if ($searchType && ! empty($search)) {
            switch ($searchType) {
                case 'Test Date':
                    // does it have a hyphen?
                    $searchDate = date('Y-m-d', strtotime($search));
                    $events->where('test_date', '=', $searchDate);
                break;
                case 'Test Site License':
                    $fac = Facility::where('license', 'LIKE', $search.'%')->first();
                    
                    if ($fac) {
                        $events->where('facility_id', '=', $fac->id);
                    } else {
                        $events->where('facility_id', '=', -1);
                    }
                break;
                case 'Test Site Name':
                    $fac = Facility::where('name', 'LIKE', $search.'%')->first();

                    if ($fac) {
                        $events->where('facility_id', '=', $fac->id);
                    } else {
                        $events->where('facility_id', '=', -1);
                    }
                break;
                case 'Event #':
                    $events->where('id', '=', $search);
                break;
                case 'Exam Name':
                    $events->whereHas('exams', function ($q) use ($search) {
                        $q->where('exams.name', 'LIKE', $search.'%');    // knowledge exam name
                    });
                break;
                case 'Skill Name':
                    $events->whereHas('skills', function ($q) use ($search) {
                        $q->where('skillexams.name', 'LIKE', $search.'%');    // skill exam name
                    });
                break;
                default:
            }
        }

        // past events?
        if ($pastOnly !== false) {
            $events->where('test_date', '<', date('Y-m-d'));
            $order = Input::get('order') ? Sorter::order() : 'desc';
        } else {
            // default to showing present / future events only
            $events->where('test_date', '>=', date('Y-m-d'));
            $order = Sorter::order();
        }

        return $events
            ->orderBy(Input::get('sort', 'test_date'), $order)
            ->paginate(Config::get('paginate.default'));
    }


    /**
     * Get all students eligible for a Knowledge Test
     */
    public function eligibleStudents($examId)
    {
        $exam = Exam::with([
            'required_exams',
            'required_trainings',
            'required_skills',
            'corequired_skills.required_exams',
            'corequired_skills.required_exams'
        ])->findOrFail($examId);

        // final collection containing all eligible students
        $ready = new Collection;

        // Facility user!
        if (Auth::user()->isRole('Facility')) {
            // only students trained at this facility + affiliated sites
            $currFacility = Facility::with([
                'students' => function ($query) {
                    $query->orderBy('students.last');
                },
                'students.passedTrainings' => function ($query) {
                    $query->where('student_training.discipline_id', $this->discipline_id);
                },
                'students.passedExams',
                'students.passedSkills',
                'students.scheduledExams',
                'students.eligibleClosedSites' => function ($query) {
                    $query->where('student_training.discipline_id', $this->discipline_id);
                },
                'affiliated' => function ($query) {
                    $query->where('facility_affiliated.discipline_id', $this->discipline_id);
                },
                'affiliated.students' => function ($query) {
                    $query->where('student_training.discipline_id', $this->discipline_id);
                }
            ])->find(Auth::user()->userable_id);

            $students = $currFacility->students->unique()->paginate(Config::get('core.pagination.default'));

            foreach ($currFacility->affiliated as $aff) {
                $affStudents = $aff->students->unique();

                foreach ($affStudents as $as) {
                    $students->push($as);
                }
            }
        } else {
            // get all students
            $students = Student::with([
                'passedTrainings' => function ($query) {
                    $query->where('student_training.discipline_id', $this->discipline_id);
                },
                'passedExams',
                'passedSkills',
                'scheduledExams',
                'eligibleClosedSites' => function ($query) {
                    $query->where('student_training.discipline_id', $this->discipline_id);
                }
            ])->orderBy('students.last')->get();
        }


        // current affiliate programs for discipline
        $affiliateDisciplineIds = $this->facility->affiliated->filter(function ($aff) {
            return $aff->pivot->discipline_id == $this->discipline_id;
        });

        // facilities accepted for a closed event
        // (this test site + any affiliated training programs per discipline)
        $affiliatedIds = array_merge([$this->facility_id], $affiliateDisciplineIds->lists('id')->all());

        // hold corequired skillexams NOT in this event (not offered or full)
        $missingCoSkillexams = new Collection;
        // hold corequired skillexams IN this event
        $foundCoSkillexams = new Collection;

        if (! $exam->corequired_skills->isEmpty()) {
            foreach ($exam->corequired_skills as $coSkill) {
                // corequired not offered!
                if (! in_array($coSkill->id, $this->skills->lists('id')->all())) {
                    $missingCoSkillexams->push($coSkill);
                    continue;
                }

                // get # seats in event skill test
                $coreqTotalSeats = (int) $this->skills->find($coSkill->id)->pivot->open_seats;
                // get # scheduled in event skill test
                $coreqScheduled = $this->skillStudents->filter(function ($st) use ($coSkill) {
                    return $st->pivot->skillexam_id == $coSkill->id;
                })->count();

                // corequired offered but full!
                if (($coreqTotalSeats - $coreqScheduled) < 1) {
                    $missingCoSkillexams->push($coSkill);
                    continue;
                }

                $foundCoSkillexams->push($coSkill);
            }
        }


        // loop thru all students
        foreach ($students as $st) {
            // student already scheduled for this exam or active passed exam
            if (in_array($examId, $st->scheduledExams->lists('id')->all()) || in_array($examId, $st->passedExams->lists('id')->all())) {
                continue;
            }

            // if oral student and event IS NOT paper then the student isnt available
            // 5/6/16 = oral students are only available for paper events
            if ($st->is_oral && ! $this->is_paper) {
                continue;
            }

            // required trainings
            if (! $exam->required_trainings->isEmpty()) {
                $remTrainingReq = array_diff($exam->required_trainings->lists('id')->all(), $st->passedTrainings->lists('id')->all());

                // student has remaining trainings left to pass
                if (! empty($remTrainingReq)) {
                    continue;
                }

                // get the most recent passed date
                // (will students training expire before the event?)
                $trainingExpires = $st->getFirstTrainingExpirationForTrainingIds($exam->required_trainings->lists('id')->all());
                
                if (! $trainingExpires || (strtotime($this->test_date) > strtotime($trainingExpires))) {
                    continue;
                }
            }

            // required knowledge exams
            if (! $exam->required_exams->isEmpty()) {
                $remExamReqs = array_diff($exam->required_exams->lists('id')->all(), $st->passedExams->lists('id')->all());

                if (! empty($remExamReqs)) {
                    continue;
                }
            }

            // required skill exams
            if (! $exam->required_skills->isEmpty()) {
                $remSkillReqs = array_diff($exam->required_skills->lists('id')->all(), $st->passedSkills->lists('id')->all());

                if (! empty($remSkillReqs)) {
                    continue;
                }
            }

            // closed event?
            if (! $this->is_regional) {
                // can student get into this test site?
                $eligibleSiteIds = array_intersect($st->eligibleClosedSites->lists('id')->all(), $affiliatedIds);

                // no eligible sites? 
                // student never trained at any affiliated site? 
                // goto next student
                if (empty($eligibleSiteIds)) {
                    continue;
                }
            }

            // corequired exams missing from this event?
            if (! $missingCoSkillexams->isEmpty()) {
                // student must have previously passed every missing corequired exam
                // (coreqs here will always be skill)
                $remMissingCoreqs = array_diff($missingCoSkillexams->lists('id')->all(), $st->passedSkills->lists('id')->all());

                // not eligible if student has not passed all missing corequirements!
                if (! empty($remMissingCoreqs)) {
                    continue;
                }
            }
            
            // corequired exams in this event?
            // student must have satisfied all corequired skill prereqs to be eligible
            if (! $foundCoSkillexams->isEmpty()) {
                // get all requirements for current corequired offered exam
                foreach ($foundCoSkillexams as $coreq) {
                    // Are there any training or exam requirements for this co-required exam?
                    $coExams         = $coreq->required_exams;
                    $coTrainings     = $coreq->required_trainings;
                    $passedExams     = $st->passedExams;
                    $passedTrainings = $st->passedTrainings;

                    // if there are no corequired exams, there are no requirements to meet
                    if (empty($coExams)) {
                        $offeredremExamReqs = null;
                    } else {
                        // we have a corequired exam, if person has no passed exams, they still need all the co-exams, 
                        // otherwise, we do a 'diff' to see which ones they have passed
                        $offeredremExamReqs = empty($passedExams) ? $coExams->lists('id')->all() : array_diff($coExams->lists('id')->all(), $passedExams->lists('id')->all());
                    }

                    // if there are no corequired trainings, there are no requirements to meet
                    if (empty($coTrainings)) {
                        $offeredremTrainingReqs = null;
                    } else {
                        // we have a corequired training, if person has no passed trainings, they still need all the co-trainings, 
                        // otherwise, we do a 'diff' to see which ones they have passed
                        $offeredremTrainingReqs = empty($passedTrainings) ? $coTrainings->lists('id')->all() : array_diff($coTrainings->lists('id')->all(), $passedTrainings->lists('id')->all());
                    }

                    // if any of the prereqs for this offered corequired knowledge exam werent met the student is not eligible
                    // skip to next student
                    if (! empty($offeredremTrainingReqs) || ! empty($offeredremExamReqs)) {
                        continue 2;
                    }
                }
            }

            // student must be eligible!
            $ready->push($st);
        }

        // add searching and order (name/etc)
        $students = $this->handleFillSeatsSearch($ready, $exam);

        // filter out oral students from web events
        // (oral is only paper)
        if (! $this->is_paper) {
            $students = $students->filter(function ($s) {
                return ! $s->is_oral;
            });
        }

        return $students;
    }

    /**
     * Get all student eligible for a Skill Test
     */
    public function eligibleSkillStudents($skillId)
    {
        // get skill in question (along with all requirements)
        $skill = Skillexam::with([
            'required_trainings',
            'required_exams',
            'corequired_exams.required_exams',
            'corequired_exams.required_trainings',
            'corequired_exams.required_skills'
        ])->find($skillId);

        // final collection containing all eligible students
        $ready = new Collection;

        // Facility user!
        if (Auth::user()->isRole('Facility')) {
            // only students trained at this facility + affiliated sites
            $currFacility = Facility::with([
                'students' => function ($query) {
                    $query->orderBy('students.last');
                },
                'students.passedTrainings' => function ($query) {
                    $query->where('student_training.discipline_id', $this->discipline_id);
                },
                'students.passedExams',
                'students.passedSkills',
                'students.scheduledSkills',
                'students.eligibleClosedSites' => function ($query) {
                    $query->where('student_training.discipline_id', $this->discipline_id);
                },
                'affiliated' => function ($query) {
                    $query->where('facility_affiliated.discipline_id', $this->discipline_id);
                },
                'affiliated.students' => function ($query) {
                    $query->where('student_training.discipline_id', $this->discipline_id);
                }
            ])->find(Auth::user()->userable_id);

            $students = $currFacility->students->unique();

            foreach ($currFacility->affiliated as $aff) {
                $affStudents = $aff->students->unique();

                foreach ($affStudents as $as) {
                    $students->push($as);
                }
            }
        } else {
            // get all students
            $students = Student::with([
                'passedTrainings' => function ($query) {
                    $query->where('student_training.discipline_id', $this->discipline_id);
                },
                'passedExams',
                'passedSkills',
                'scheduledSkills',
                'eligibleClosedSites' => function ($query) {
                    $query->where('student_training.discipline_id', $this->discipline_id);
                }
            ])->orderBy('students.last')->get();
        }

        // current affiliate programs for discipline
        $affiliateDisciplineIds = $this->facility->affiliated->filter(function ($aff) {
            return $aff->pivot->discipline_id == $this->discipline_id;
        });

        // facilities that can be accepted for closed event
        // (this test site + any affiliated training programs)
        $affiliatedIds = array_merge([$this->facility_id], $affiliateDisciplineIds->lists('id')->all());

        // hold corequired skillexams NOT in this event (not offered or full)
        $missingCoExams = new Collection;
        // hold corequired skillexams IN this event
        $foundCoExams = new Collection;

        if (! $skill->corequired_exams->isEmpty()) {
            foreach ($skill->corequired_exams as $coExam) {
                // corequired not offered!
                if (! in_array($coExam->id, $this->exams->lists('id')->all())) {
                    $missingCoExams->push($coExam);
                    continue;
                }

                // get # seats in event skill test
                $coreqTotalSeats = (int) $this->exams->find($coExam->id)->pivot->open_seats;
                // get # scheduled in event skill test
                $coreqScheduled = $this->knowledgeStudents->filter(function ($st) use ($coExam) {
                    return $st->pivot->exam_id == $coExam->id;
                })->count();

                // corequired offered but full!
                if (($coreqTotalSeats - $coreqScheduled) < 1) {
                    $missingCoExams->push($coExam);
                    continue;
                }

                $foundCoExams->push($coExam);
            }
        }
        

        // loop thru all students
        foreach ($students as $st) {
            // student is already scheduled or passed this exam?
            if (in_array($skillId, $st->scheduledSkills->lists('id')->all()) || in_array($skillId, $st->passedSkills->lists('id')->all())) {
                continue;
            }

            // required trainings
            if (! $skill->required_trainings->isEmpty()) {
                $remTrainingReqs = array_diff($skill->required_trainings->lists('id')->all(), $st->passedTrainings->lists('id')->all());
                
                if (! empty($remTrainingReqs)) {
                    continue;
                }

                // get the most recent passed date
                // (will students training expire before the event?)
                $trainingExpires = $st->getFirstTrainingExpirationForTrainingIds($skill->required_trainings->lists('id')->all());

                if (! $trainingExpires || (strtotime($this->test_date) > strtotime($trainingExpires))) {
                    continue;
                }
            }

            // required exams
            if (! $skill->required_exams->isEmpty()) {
                $remExamReqs = array_diff($skill->required_exams->lists('id')->all(), $st->passedExams->lists('id')->all());

                if (! empty($remExamReqs)) {
                    continue;
                }
            }

            // is event closed?
            if (! $this->is_regional) {
                // can student get into this test site?
                $eligibleSiteIds = array_intersect($st->eligibleClosedSites->lists('id')->all(), $affiliatedIds);

                // none eligible? student never trained at any affiliated site? next continue..
                if (empty($eligibleSiteIds)) {
                    continue;
                }
            }

            // corequired exams missing from this event?
            if (! $missingCoExams->isEmpty()) {
                // student must have previously passed every missing co-required exam to be eligible
                // (coreqs here will always be knowledge)
                $remMissingCoreqs = array_diff($missingCoExams->lists('id')->all(), $st->passedExams->lists('id')->all());

                // not eligible if student has not passed all missing corequirements!
                if (! empty($remMissingCoreqs)) {
                    continue;
                }
            }

            // corequired exams in this event?
            if (! $foundCoExams->isEmpty()) {
                // student must have satisfied all corequired exam prereqs to be eligible
                // get all requirements for current corequired offered exam
                foreach ($foundCoExams as $coreq) {
                    // Are there any training or exam requirements for this co-required exam?
                    $coExams         = $coreq->required_exams;
                    $coSkills        = $coreq->required_skills;
                    $coTrainings     = $coreq->required_trainings;
                    $passedExams     = $st->passedExams;
                    $passedSkills    = $st->passedSkills;
                    $passedTrainings = $st->passedTrainings;

                    // if there are no corequired exams, there are no requirements to meet
                    if (empty($coExams)) {
                        $offeredremExamReqs = null;
                    } else {
                        // we have a corequired exam, if person has no passed exams, they still need all the co-exams, 
                        // otherwise, we do a 'diff' to see which ones they have passed
                        $offeredremExamReqs = empty($passedExams) ? $coExams->lists('id')->all() : array_diff($coExams->lists('id')->all(), $passedExams->lists('id')->all());
                    }

                    // if there are no corequired skill exams, there are no requirements to meet
                    if (empty($coSkills)) {
                        $offeredRemSkillReqs = null;
                    } else {
                        // we have a corequired exam, if person has no passed exams, they still need all the co-exams, 
                        // otherwise, we do a 'diff' to see which ones they have passed
                        $offeredRemSkillReqs = empty($passedSkills) ? $coSkills->lists('id')->all() : array_diff($coSkills->lists('id')->all(), $passedSkills->lists('id')->all());
                    }
                    
                    // if there are no corequired trainings, there are no requirements to meet
                    if (empty($coTrainings)) {
                        $offeredremTrainingReqs = null;
                    } else {
                        // we have a corequired training, if person has no passed trainings, they still need all the co-trainings, 
                        // otherwise, we do a 'diff' to see which ones they have passed
                        $offeredremTrainingReqs = empty($passedTrainings) ? $coTrainings->lists('id')->all() : array_diff($coTrainings->lists('id')->all(), $passedTrainings->lists('id')->all());
                    }

                    // if any of the prereqs for this offered corequired knowledge exam werent met, the student is not eligible, NEXT!
                    if (! empty($offeredRemSkillReqs) || ! empty($offeredremTrainingReqs) || ! empty($offeredremExamReqs)) {
                        continue 2;
                    }
                }
            }

            // student must be eligible!
            $ready->push($st);
        }
        
        // add searching and order (name/etc)
        $students = $this->handleFillSeatsSearch($ready, $skill);

        return $students;
    }

    /**
     * Handles searching by name in various ways
     * $exam - either Exam or Skillexam (both have required_trainings())
     */
    protected function handleFillSeatsSearch($students, $exam)
    {
        // was there a search done?
        $search = Input::get('search');

        if (! empty($search)) {
            // is there a comma?
            if (strpos($search, ',') !== false) {
                list($last, $first) = explode(',', $search, 2);
            }
            // First Last
            elseif (strpos($search, ' ') !== false) {
                list($first, $last) = explode(' ', $search, 2);
            }
            // Last, First
            else {
                list($first, $last) = array($search, $search);
            }

            // filter array
            // only allow matching names
            $students = $students->filter(function ($st) use ($first, $last) {
                return strpos($st->first, $first) !== false || strpos($st->last, $last) !== false;
            });
        }

        // sort by important expiration
        if (! Input::get('sort') || Input::get('sort') == 'expires') {
            $order = Sorter::order();

            $students = $students->sort(function ($a, $b) use ($exam,$order) {
                $aExp = $a->getFirstTrainingExpirationForTrainingIds($exam->required_trainings->lists('id')->all());
                $bExp = $b->getFirstTrainingExpirationForTrainingIds($exam->required_trainings->lists('id')->all());
                
                if ($order == 'asc') {
                    return strtotime($bExp) - strtotime($aExp);
                } else {
                    return strtotime($aExp) - strtotime($bExp);
                }
            });
        }
        // sort by db field
        else {
            if (Sorter::order() == 'asc') {
                $students = $students->sortBy(Input::get('sort'));
            } else {
                $students = $students->sortByDesc(Input::get('sort'));
            }
        }

        return $students;
    }

    /**
     * Create schedule records for each student
     * Check OSBN getApprovedOral again
     */
    public function fillSeats($studentIds, $examIds, $skillIds)
    {
        $students = Student::with(['passedExams', 'passedSkills'])->whereIn('id', $studentIds)->get();

        foreach ($students as $student) {
            // schedule into knowledge exam(s)?
            if (! empty($examIds)) {
                foreach ($examIds as $examId) {
                    // has student already passed this exam?
                    if (! in_array($examId, $student->passedExams->lists('id')->all())) {
                        $this->scheduleKnowledgeStudent($student->id, $examId, $student->is_oral);
                    }
                }
            }

            // schedule into skill exam(s)?
            if (! empty($skillIds)) {
                foreach ($skillIds as $skillId) {
                    // has student already passed this skillexam?
                    if (! in_array($skillId, $student->passedSkills->lists('id')->all())) {
                        $this->scheduleSkillStudent($student->id, $skillId);
                    }
                }
            }
        }

        return;
    }


    /**
     * Send a 'scheduled for test' email notification
     */
    public function sendScheduleEmail($type, $student, $exam)
    {
        $type  = $type == 'skill' ? 'skill' : 'knowledge';
        $class = $type == 'knowledge' ? 'Testattempt' : 'Skillattempt';

        // Do the two classes exist in php?
        if (! class_exists($class)) {
            return false;
        }

        $attempt = $class::where('student_id', $student->id)->where('testevent_id', $this->id)->first();

        if (empty($attempt)) {
            return false;
        }


        // Send email notification
        Mail::send('core::emails.scheduled',
            [
                'type'  => $type,
                'exam'  => $exam,
                'event' => $this,
                'route' => route('testing.confirm', [$type, $attempt->id])
            ],
            function ($message) use ($student) {
                $message->to($student->user->email, $student->fullName)->subject('Scheduled Test Confirmation');
        });
    }

    /**
     * Checks if this new student we are about to schedule has a conflict of interest with the current observer
     */
    private function hasConflictOfInterest($student)
    {
        $currDiscipline = $this->discipline;
        $currObserver   = $this->observer;

        // Check if Observer is double-agent (also works as Instructor)
        $instructor = Instructor::with([
            'activeFacilities' => function ($q) use ($currDiscipline) {
                $q->where('facility_person.discipline_id', $currDiscipline->id);
            }
        ])->where('user_id', $currObserver->user_id)->first();

        // Instructor wasn't found?
        //  (Observer is NOT also an Instructor or Instructor has no active programs)
        if (is_null($instructor) || $instructor->activeFacilities->isEmpty()) {
            return false;
        }

        // check each student training for a conflict
        foreach ($student->currentTrainings as $tr) {
            // student has training from program where observer is working as an instructor!
            //  conflict!
            if (in_array($tr->pivot->facility_id, $instructor->activeFacilities->lists('id')->all())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Schedule a student into a Knowledge Test in THIS event
     *   - optional notification to Student
     */
    public function scheduleKnowledgeStudent($studentId, $examId, $isOral = null)
    {
        $exam = Exam::with('required_trainings')->find($examId);

        // ensure requested exam exists in event
        $eventExamIds = $this->exams->lists('id')->all();
        if (empty($eventExamIds) || ! in_array($examId, $eventExamIds)) {
            Flash::danger('Knowledge Test <strong>'.$exam->name.'</strong> not found in Event.');
            return false;
        }

        // get student we are attempting to schedule
        $student = Student::with([
            'scheduledAttempts',
            'currentTrainings' => function ($q) use ($exam) {
                $q->where('student_training.discipline_id', $exam->discipline_id);
            }
        ])->find($studentId);

        if (is_null($student)) {
            Flash::danger('Unable to schedule '.Lang::choice('core::terms.student', 1).' with unknown ID#'.$studentId.'.');
            return false;
        }

        // oral status?
        // if passed in use that, otherwise retrieve from student record
        $isOral = is_null($isOral) ? $student->is_oral : $isOral;

        // check for conflict of interest
        //  (new function 7/15 - extended conflict checking to any PROGRAM the observer/instructor works at)
        if ($this->hasConflictOfInterest($student)) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> has a Conflict of Interest with Event Observer.');
            return false;
        }

        // get seat counts
        // (total listed & currently scheduled)
        $eventExam  = $this->exams->keyBy('id')->get($examId);
        $totalSeats = $eventExam->pivot->open_seats;
        $scheduledAttempts = $this->testattempts()->get()->filter(function ($attempt) use ($examId) {
            return $attempt->exam_id == $examId;
        });

        // prevent overscheduling
        // (remaining event seats)
        if ($scheduledAttempts->count() >= (int) $totalSeats) {
            Flash::danger('Knowledge Test <strong>'.$exam->name.'</strong> has no remaining Event seats available.');
            return false;
        }

        // prevent oral student from scheduling into web event
        // paper oral only 5/9
        if ($isOral && ! $this->is_paper) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> has requested Oral which is only available for Paper Events.');
            return false;
        }

        // prevent double schedule
        // (already scheduled for this exam)
        if (in_array($examId, $student->scheduledAttempts->lists('exam_id')->all())) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> is already scheduled for Knowledge Test <strong>'.$exam->name.'</strong>.');
            return false;
        }

        // no suitable testform found
        // (removes previously failed active testforms as available)
        // (if oral student, filtered to only oral testforms)
        $testformPool = $student->getTestformPool($examId);

        // Student requested Oral and no Oral testforms available?
        //  (dont allow NULL testform scheduling for orals)
        if (empty($testformPool) && $isOral) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> requested Oral but no Oral Testforms available for Knowledge Test <strong>'.$exam->name.'</strong>.');
            return false;
        }

        // available testforms?
        if (! empty($testformPool)) {
            // remove all previously taken testform ids from available testforms
            $testformPool = array_diff($testformPool, $scheduledAttempts->lists('testform_id')->all());
            // randomize + rekey array
            shuffle($testformPool);
        }

        // assign testform if possible otherwise assign NULL
        $testformId = empty($testformPool) ? null : $testformPool[array_rand($testformPool)];

        // get student training record for student
        $newestTrId         = null;
        $newestTrDate       = null;
        foreach ($student->passedTrainings as $passedTr) {
            // if this passed training is one of the required trainings for taking this exam
            // is the ended date the newest we've come across?
            if (in_array($passedTr->id, $exam->required_trainings->lists('id')->all()) && (strtotime($passedTr->pivot->ended) > strtotime($newestTrDate))) {
                $newestTrId   = $passedTr->pivot->id;
                $newestTrDate = $passedTr->pivot->ended;
            }
        }

        $testattemptInfo = [
            'facility_id'         => $this->facility_id,
            'student_training_id' => $newestTrId,
            'exam_id'             => $examId,
            'testform_id'         => $testformId,
            'status'              => 'assigned',
            'seat_type'           => 'open',
            'is_oral'             => (boolean) $isOral
        ];


        // add student to event exam
        $this->knowledgeStudents()->attach($studentId, $testattemptInfo);

        // notify Student?
        if (Input::get('notify.student') && Input::get('notify.student') === '1') {
            $student->user->notify()
                ->withType('info')
                ->withSubject('Scheduled Event')
                ->withBody(
                    View::make('core::students.notifications.scheduled_knowledge')->with([
                        'exam'        => $exam,
                        'event'        => $this
                    ]))
                ->deliver();
        }

        \Log::info('Student ' . $student->fullName . ' scheduled into ' . $exam->name . ' Knowledge Test', [
            'scheduledBy' => Auth::user()->userable->fullName
        ]);

        // Send email notification 
        $this->sendScheduleEmail('knowledge', $student, $exam);

        Flash::success(Lang::choice('core::terms.student', 1).' <strong>'.$student->fullName.'</strong> scheduled into Knowledge Test <strong>'.$exam->name.'</strong>');
        return true;
    }

    /**
     * Schedule a student into a Skill Exam in THIS event
     *   - optional notification to Student
     */
    public function scheduleSkillStudent($studentId, $skillId)
    {
        $skill   = Skillexam::with('required_trainings')->find($skillId);

        // ensure requested skillexam exists in event
        $eventSkillIds = $this->skills->lists('id')->all();
        if (empty($eventSkillIds) || ! in_array($skillId, $eventSkillIds)) {
            Flash::danger('Skill Test <strong>'.$skill->name.'</strong> not found in Event.');
            return false;
        }

        // get student we are attempting to schedule
        $student = Student::with([
            'scheduledSkills',
            'currentTrainings' => function ($q) use ($skill) {
                $q->where('student_training.discipline_id', $skill->discipline_id);
            }
        ])->find($studentId);

        if (is_null($student)) {
            Flash::danger('Unable to schedule '.Lang::choice('core::terms.student', 1).' with unknown ID#'.$studentId.'.');
            return false;
        }

        // check for conflict of interest
        //  (new function 7/15 - extended conflict checking to any PROGRAM the observer/instructor works at)
        if ($this->hasConflictOfInterest($student)) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> has a Conflict of Interest with Event Observer.');
            return false;
        }

        // get seat counts
        // (total listed & currently scheduled)
        $totalSeats = $this->skills->keyBy('id')->get($skillId)->pivot->open_seats;
        $scheduledAttempts = $this->skillattempts()->get()->filter(function ($attempt) use ($skillId) {
            return $attempt->skillexam_id == $skillId;
        });

        // prevent overscheduling
        // (remaining event seats)
        if ($scheduledAttempts->count() >= (int) $totalSeats) {
            Flash::danger('Skill Test <strong>'.$skill->name.'</strong> has no remaining Event seats available.');
            return false;
        }

        // prevent double schedule
        // (already scheduled for this exam)
        if (in_array($skillId, $student->scheduledSkillAttempts->lists('skillexam_id')->all())) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> is already scheduled for Skill Test <strong>'.$skill->name.'</strong>.');
            return false;
        }

        // get eligible skilltests
        //  (removes previously failed active skilltests as available)
        $skilltestPool = $student->getSkilltestPool($skillId);
        
        if (! empty($skilltestPool)) {
            // remove all previously taken skilltest ids from available testforms
            $skilltestPool = array_diff($skilltestPool, $scheduledAttempts->lists('skilltest_id')->all());
            // randomize
            shuffle($skilltestPool);
        }

        // assign testform if possible otherwise assign NULL
        $skilltestId = empty($skilltestPool) ? null : $skilltestPool[array_rand($skilltestPool)];

        // get student training record for student
        $newestTrId         = null;
        $newestTrDate       = null;
        foreach ($student->passedTrainings as $ptr) {
            // if this passed training is one of the required trainings for taking this exam
            // is the ended date the newest we've come across?
            if (in_array($ptr->id, $skill->required_trainings->lists('id')->all()) && (strtotime($ptr->pivot->ended) > strtotime($newestTrDate))) {
                $newestTrId   = $ptr->pivot->id;
                $newestTrDate = $ptr->pivot->ended;
            }
        }

        // add student to event exam
        $this->skillStudents()->attach($studentId, [
            'facility_id'         => $this->facility_id,
            'student_training_id' => $newestTrId,
            'skillexam_id'        => $skillId,
            'skilltest_id'        => $skilltestId,
            'testevent_id'        => $this->id,
            'status'              => 'assigned'
        ]);

        // notify Student?
        if (Input::get('notify.student') && Input::get('notify.student') === '1') {
            $student->user->notify()
                ->withType('info')
                ->withSubject('Scheduled Event')
                ->withBody(
                    View::make('core::students.notifications.scheduled_skill')->with([
                        'event'    => $this
                    ]))
                ->deliver();
        }

        // Log this scheduled student and by whom
        \Log::info('Student ' . $student->fullName . ' scheduled into ' . $skill->name . ' Skill Test', [
            'scheduledBy' => Auth::user()->userable->fullName
        ]);

        // Send email notification 
        $this->sendScheduleEmail('skill', $student, $skill);

        // Flash a message and return
        Flash::success(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> scheduled into Skill Test <strong>'.$skill->name.'</strong>.');
        return true;
    }

    /**
     * Add a new Testevent
     */
    public function addWithInput()
    {
        // datetime
        $testDates    = Input::get('test_date');
        $startTimes   = Input::get('start_time');
        // discipline
        $disciplineId = Input::get('discipline_id');
        // test site
        $facilityId   = Input::get('facility_id');
        // test team
        // (proctor/actor uses user_id, to distinguish b/t observer filling in)
        // (all test team comes in as array, since we are using Form::checkbox it requires array)
        // (using array allows us to easily have user check/uncheck rather than radio, provides for a nicer UI)
        $observerId   = current(Input::get('observer_id'));
        $proctorId    = Input::get('proctor_id') ? current(Input::get('proctor_id')) : null;
        $actorId      = Input::get('actor_id') ? current(Input::get('actor_id')) : null;    // actually a user_id (in case observer filling in)
        $mentoring    = Input::get('is_mentor');
        // other
        $isRegional   = Input::get('is_regional');    // regional or closed
        $isPaper      = Input::get('is_paper');        // paper or web
        $comments     = Input::get('comments');

        // get observer and test site
        $observer     = Observer::with('user')->find($observerId);
        $facility     = Facility::with('user')->find($facilityId);

        // if multiple test dates
        // then multiple event ids will be returned
        $eventIds = [];

        if (! is_array($testDates)) {
            $testDates = array($testDates);
        }
        if (! is_array($startTimes)) {
            $startTimes = array($startTimes);
        }

        foreach ($testDates as $i => $testDate) {
            // If empty test date, ignore
            if (empty($testDate)) {
                continue;
            }

            // test event
            $evt = new \Testevent;
            $evt->discipline_id = $disciplineId;
            $evt->facility_id   = $facility->id;
            $evt->observer_id   = $observer->id;
            $evt->test_date     = date('Y-m-d', strtotime($testDate));
            $evt->start_time    = date('H:i:s', strtotime($startTimes[$i]));
            $evt->locked        = 0;
            $evt->is_regional   = $isRegional;
            $evt->is_paper      = $isPaper;
            $evt->comments      = $comments;

            // any exams in the event paper?
            $evtHasPaperExam = false;

            // Is the observer mentoring this event?
            if ($mentoring) {
                $evt->is_mentor = true;
            }

            // Is there a proctor?
            if (! empty($proctorId)) {
                $proctorType = Input::get('proctor_type.'.$proctorId);

                if (! empty($proctorType)) {
                    $evt->proctor_id   = User::find($proctorId)->userable_id;
                    $evt->proctor_type = $proctorType;
                }
            }
            // Is there an actor?
            if (! empty($actorId)) {
                $actorType = Input::get('actor_type.'.$actorId);

                if (! empty($actorType)) {
                    $evt->actor_id   = User::find($actorId)->userable_id;
                    $evt->actor_type = $actorType;
                }
            }

            // Save the event
            $evt->save();

            // knowledge/skill exam seats
            $knowledgeSeats = Input::get('exam_seats');
            $skillSeats     = Input::get('skill_seats');

            // add knowledge exams
            if ($knowledgeSeats) {
                foreach ($knowledgeSeats as $comboInfo => $numSeats) {
                    if (! empty($numSeats)) {
                        list($examId, $disciplineId) = explode('|', $comboInfo);

                        // $isPaper = $knowledgeTypes[$examId] == 'paper' ? 1 : 0;

                        // if($isPaper == 1)
                        // {
                        // 	$evtHasPaperExam = true;
                        // }

                        // attach pivot relationship
                        $evt->exams()->attach($examId, [
                            'open_seats'     => $numSeats,
                            'reserved_seats' => null
                            // 'is_paper'    => $isPaper
                        ]);
                    }
                }
            }

            // add skill exams
            if ($skillSeats) {
                foreach ($skillSeats as $comboInfo => $numSeats) {
                    if (! empty($numSeats)) {
                        list($skillId, $disciplineId) = explode('|', $comboInfo);

                        $evt->skills()->attach($skillId, [
                            'open_seats'     => $numSeats,
                            'reserved_seats' => null
                        ]);
                    }
                }
            }

            // update event record if exam contains paper
            // $evt->is_paper = $evtHasPaperExam;
            // Commented out above in case we need paper+web in same event

            $evt->save();

            // notify Observer
            $observer->user->notify()
                ->withType('info')
                ->withSubject('New Test Event')
                ->withBody(
                    View::make('core::observers.notifications.new_event')->with(['event' => $evt])
                )
                ->deliver();

            // Notify proctor
            // no need to send duplicate notification if OBSERVER == PROCTOR
            if ($evt->proctor_id && $evt->proctor_type && $observer->user->id != $proctorId) {
                User::find($proctorId)->notify()
                    ->withType('info')
                    ->withSubject('New Test Event')
                    ->withBody(
                        View::make('core::proctors.notifications.new_event')->with(['event' => $evt])
                    )
                    ->deliver();
            }

            // notify Facility (Test Site)
            $facility->user->notify()
                ->withType('info')
                ->withSubject('New Test Event')
                ->withBody(
                    View::make('core::facilities.notifications.new_event')->with(['event' => $evt])
                )
                ->deliver();

            $eventIds[] = $evt->id;
        }
    
        return $eventIds;
    }

    /**
     * Notify a test observer that a student has been rescheduled
     */
    public function notifyObserverOfReschedule($student)
    {
        // if this event isn't locked, don't do anything
        if ($this->locked === false) {
            return false;
        }

        $testDate = strtotime($this->getOriginal('test_date'));
        $today    = strtotime(date('Y-m-d'));
        $observer = $this->observer;

        // Do we have an observer and student? And is the event in the future?
        if ($observer && $student && $testDate >= $today) {
            // Send email notification
            Mail::send('core::emails.rescheduled',
                [
                    'event'    => $this,
                    'student'  => $student,
                    'facility' => $this->facility,
                ],
                function ($message) use ($observer) {
                    $message->to($observer->user->email, $observer->fullName)
                        ->subject(\Lang::choice('core::terms.student', 1) . ' Rescheduled');
                }
            );

            Flash::info(Lang::choice('core::terms.observer', 1) . $observer->fullName . ' sent email notification of reschedule.');
        }
    }

    /**
     * Triggered after a test_date has changed to notify: observer, scheduled students, and facility
     * Currently only sends inter-app message
     * Add email functionality here
     */
    protected function notifyChangedTestdate($info)
    {
        // old event date/time needed
        if (! array_key_exists('old_datetime', $info)) {
            return false;
        }

        $newDate = date('m/d/Y', strtotime($info['new_datetime']));
        $oldDate = date('m/d/Y', strtotime($info['old_datetime']));

        // notify facility
        $this->facility->user->notify()
            ->withType('info')
            ->withSubject('Test Event Changed')
            ->withBody(
                View::make('core::facilities.notifications.event_datetime_changed')->with($info)
            )
            ->deliver();

        // notify observer
        $this->observer->user->notify()
            ->withType('info')
            ->withSubject('Test Event Changed')
            ->withBody(
                View::make('core::observers.notifications.event_datetime_changed')->with($info)
            )
            ->deliver();

        // notify testing students
        foreach ($this->knowledgeStudents()->get() as $s) {
            $attemptId = $s->scheduledAttempts()->where('testevent_id', $this->id)->first()->id;
            $info['attempt_id'] = $attemptId;
            $info['user_id'] = $s->user_id;

            $s->user->notify()
                ->withType('info')
                ->withSubject('Test Event Changed')
                ->withBody(
                    View::make('core::students.notifications.event_knowledge_datetime_changed')->with($info)
                )
                ->deliver();
        }

        // notify skill students
        foreach ($this->skillStudents()->get() as $s) {
            $attemptId = $s->scheduledSkillAttempts()->where('testevent_id', $this->id)->first()->id;
            $info['attempt_id'] = $attemptId;
            $info['user_id'] = $s->user_id;

            $s->user->notify()
                ->withType('info')
                ->withSubject('Test Event Changed')
                ->withBody(
                    View::make('core::students.notifications.event_skill_datetime_changed')->with($info)
                )
                ->deliver();
        }

        // Gather other events on this day (the old event date) for Observer to remind them to potentially change other events too!
        //  (do they need to reschedule other events too?)
        $otherEvents = Testevent::where('observer_id', $this->observer->id)
                            ->where('id', '!=', $this->id)
                            ->where('test_date', date('Y-m-d', strtotime($oldDate)))
                            ->get();

        if (! $otherEvents->isEmpty() && Auth::user()->ability(['Staff', 'Admin'], [])) {
            $msg = Lang::choice('core::terms.observer', 1) . ' is scheduled for ' . $otherEvents->count() . ' other Events on ' . $oldDate . ' that may require attention!';

            $linkedEvents = [];
            foreach ($otherEvents as $e) {
                $linkedEvents[] = link_to_route('events.edit', '#' . $e->id, [$e->id]);
            }

            $msg .= '<br><br>'.implode(', ', $linkedEvents);

            Flash::warning($msg);
        }
    }

    public function updateWithInput()
    {
        // original datetime
        $origTestDate  = $this->test_date;
        $origStartTime = date('g:i A', strtotime($this->start_time));
        // new datetime
        $newTestDate  = Input::get('test_date')[0];
        $newStartTime = Input::get('start_time')[0];

        // if event datetime changed, notify		
        if ($origTestDate != $newTestDate || $origStartTime != $newStartTime) {
            $this->notifyChangedTestdate([
                'event'        => $this,
                'old_datetime' => $origTestDate.' '.$origStartTime,
                'new_datetime' => $newTestDate.' '.$newStartTime
            ]);
        }

        // update testevent
        $this->test_date   = date('Y-m-d', strtotime(Input::get('test_date')[0]));
        $this->start_time  = date('H:i:s', strtotime(Input::get('start_time')[0]));
        $this->is_regional = Input::get('is_regional');
        $this->is_paper    = Input::get('is_paper');
        $this->is_mentor   = Input::get('is_mentor', false);
        $this->comments    = Input::get('comments', null);

        // uploads?
        if (Input::hasFile("eventFiles")) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/events/" . $this->id . "/";
            
            foreach (Input::file("eventFiles") as $file) {
                $file->move($target_dir, $file->getClientOriginalName());
            }
        }

        return $this->save();
    }

    /**
     * Determines if there are any pending attempts for this event
     */
    public function all_released()
    {
        if ($this->assigned_knowledge === null) {
            $this->load('assigned_knowledge');
        }
        if ($this->assigned_skills === null) {
            $this->load('assigned_skills');
        }

        return $this->assigned_knowledge->count() == 0 && $this->assigned_skills->count() == 0;
    }

    /**
     * Release all pending test (skill and knowledge) attempts for an event
     */
    public function releaseTests($notify=[])
    {
        // if a start code doesnt already exist, assign one!
        if (is_null($this->start_code)) {
            $this->start_code = substr(sha1(uniqid(mt_rand(), true)), 0, 4);
            $this->save();
        }

        $kAssigned = $this->testattempts()->where('status', 'assigned')->get();
        $sAssigned = $this->skillattempts()->where('status', 'assigned')->get();

        // knowledge tests
        $c = 0;
        foreach ($kAssigned as $attempt) {
            $attempt->status = 'pending';
            $attempt->save();

            // notify student
            if (isset($notify["student"])) {
                $student = Student::find($attempt->student_id);
                $exam    = Exam::find($attempt->exam_id);

                $student->user->notify()
                    ->withType('warning')
                    ->withSubject('Begin Test')
                    ->withBody(
                        View::make('core::students.notifications.begin_test')->with([
                            'student'    => $student,
                            'exam'       => $exam,
                            'event'      => $this,
                            'attempt_id' => $attempt->id
                        ]))
                    ->deliver();
            }
        }

        // skill tests
        foreach ($sAssigned as $attempt) {
            $attempt->status = 'pending';
            $attempt->save();
        }
    }

    /**
     * Updates max seats for a Knowledge Test
     */
    public function updateKnowledgeSeats($examId, $newSeats)
    {
        $exam = Exam::find($examId);

        $pivotRow                    = $this->exams()->where('exam_id', '=', $examId)->first();
        $oldSeats                    = $pivotRow->pivot->open_seats;    // get original seats
        $pivotRow->pivot->open_seats = $newSeats; // set new seats
        $pivotRow->pivot->save();

        $evtData = ['event'        => $this,
                    'exam'        => $exam,
                    'old_seats' => $oldSeats,
                    'new_seats' => $newSeats];

        // notify Facility
        $this->facility->user->notify()
                ->withType('info')
                ->withSubject('Test Event Updated')
                ->withBody(
                    View::make('core::facilities.notifications.event_seats_changed')->with($evtData)
                )
                ->deliver();

        // notify Observer
        $this->observer->user->notify()
                ->withType('info')
                ->withSubject('Test Event Updated')
                ->withBody(
                    View::make('core::observers.notifications.event_seats_changed')->with($evtData)
                )
                ->deliver();

        // notify Proctor (if set)
        if (! is_null($this->proctor_id)) {
            $this->proctor->user->notify()
                    ->withType('info')
                    ->withSubject('Test Event Updated')
                    ->withBody(
                        View::make('core::proctors.notifications.event_seats_changed')->with($evtData)
                    )
                    ->deliver();
        }
    }

    /**
     * Updates max seats for a Skill Test
     */
    public function updateSkillSeats($skillId, $newSeats)
    {
        $skill = Skillexam::find($skillId);

        $pivotRow                    = $this->skills()->where('skillexam_id', '=', $skillId)->first();
        $oldSeats                    = $pivotRow->pivot->open_seats;    // get original seats
        $pivotRow->pivot->open_seats = $newSeats; // set new seats
        $pivotRow->pivot->save();

        $evtData = ['event'        => $this,
                    'skill'        => $skill,
                     'old_seats' => $oldSeats,
                    'new_seats' => $newSeats];

        // notify Facility
        $this->facility->user->notify()
                ->withType('info')
                ->withSubject('Test Event Updated')
                ->withBody(
                    View::make('core::facilities.notifications.event_skill_seats_changed')->with($evtData)
                )
                ->deliver();

        // notify Observer
        $this->observer->user->notify()
                ->withType('info')
                ->withSubject('Test Event Updated')
                ->withBody(
                    View::make('core::observers.notifications.event_skill_seats_changed')->with($evtData)
                )
                ->deliver();

        // notify Proctor (if set)
        if (! is_null($this->proctor_id)) {
            // notify Proctor
            $this->proctor->user->notify()
                    ->withType('info')
                    ->withSubject('Test Event Updated')
                    ->withBody(
                        View::make('core::proctors.notifications.event_skill_seats_changed')->with($evtData)
                    )
                    ->deliver();
        }
    }

    /**
     * Bootstrap-compatible status classes
     */
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

    /**
     * Readable test date
     */
    public function getTestDateAttribute($value)
    {
        return empty($value) ? '' : date('m/d/Y', strtotime($value));
    }

    /**
     * Readable start time
     */
    public function getStartTimeAttribute($value)
    {
        return empty($value) ? '' : date('g:i A T', strtotime($value));
    }

    /**
     * Date formatted like Sunday, May 5th, 2134
     */
    public function getPrettyDateAttribute()
    {
        $date = $this->test_date;
        return empty($date) ? '' : date('l, F j, Y', strtotime($date));
    }

    /**
     * The title displayed when an event appears in calendar view
     */
    public function getCalendarTitleAttribute()
    {
        return $this->facility->city . ' ' . $this->facility->name;
    }

    public function getCreatedAttribute()
    {
        return empty($this->created_at) ? '' : date('m/d/Y H:i A', strtotime($this->created_at));
    }

    public function getDeletedAttribute()
    {
        return empty($this->deleted_at) ? '' : date('m/d/Y H:i A', strtotime($this->deleted_at));
    }

    /**
     * Get the color this event should display as on the calendar
     */
    public function getCalendarColorAttribute()
    {
        $colors = Config::get('core.events.calendarColors');
        
        // Past Events
        if ($this->isPast) {
            return $colors['past'];
        }

        // Colors matching on discipline_id
        if (array_key_exists($this->discipline_id, $colors['disciplines'])) {
            return $colors['disciplines'][$this->discipline_id];
        }

        // default to the normal event color
        return '';
    }

    /**
     * Boolean, true if all knowledge exams in an event are full
     */
    public function getIsFullKnowledgeAttribute()
    {
        // Go through each exam, checking if the matching attempts are >= max
        foreach ($this->exams as $exam) {
            // filter to get only attempts for this exam
            $examAttempts = $this->testattempts->filter(function ($attempt) use ($exam) {
                return $attempt->exam_id == $exam->id;
            });

            // If there are open seats left, this event isn't full
            if ($examAttempts->count() < $exam->pivot->open_seats) {
                return false;
            }
        }

        // If we made it here, it's full!
        return true;
    }

    /**
     * Boolean, true if all the skill exams in an event are full
     */
    public function getIsFullSkillAttribute()
    {
        // Go through each skill, checking if the matching attempts are >= max
        foreach ($this->skills as $skillexam) {
            // filter to get only the attempts for this skill
            $skillAttempts = $this->skillattempts->filter(function ($attempt) use ($skillexam) {
                return $attempt->skillexam_id == $skillexam->id;
            });

            // if there are open seats left, event isn't full
            if ($skillAttempts->count() < $skillexam->pivot->open_seats) {
                return false;
            }
        }
        
        // If we made it here, all the seats must be filled
        return true;
    }

    /**
     * Boolean, true if all the exams in an event are full
     */
    public function getIsFullAttribute()
    {
        return $this->isFullSkill && $this->isFullKnowledge;
    }

    /**
     * Whether an event can be ended or not
     */
    public function getCanEndAttribute()
    {
        // Does this user have permission to end the event?
        if (! Auth::user()->can('events.end')) {
            return false;
        }

        // Is the event today (or in the past?)
        if (! ($this->test_date <= date('m/d/Y'))) {
            return false;
        }

        // Can't end paper events
        if ($this->is_paper) {
            return false;
        }

        // Has the event been started? And not ended already?
        if (empty($this->start_code) || ! empty($this->ended)) {
            return false;
        }

        return true;
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
     * Whether an event is 'ended' or not, assumes events in PAST are ENDED
     *
     * @return boolean
     */
    public function getIsEndedAttribute()
    {
        // If the event has the 'ended' timestamp, return true
        if ($this->ended) {
            return true;
        }

        // Otherwise, return whether it's in the past or not
        return $this->getIsPastAttribute();
    }

    /**
     * Saves all marked no-show students after an observer ends event
     */
    public function saveNoShows()
    {
        $knowledgeAttemptIds = Input::get('knowledge_att_id');
        $skillAttemptIds     = Input::get('skill_att_id');

        // knowledge noshows
        if ($knowledgeAttemptIds) {
            foreach ($knowledgeAttemptIds as $attemptId) {
                $attempt = $this->testattempts()->find($attemptId);
                
                $attempt->status = 'noshow';
                $attempt->save();
            }
        }

        // skill noshows
        if ($skillAttemptIds) {
            foreach ($skillAttemptIds as $attemptId) {
                $attempt = $this->skillattempts()->find($attemptId);
                
                $attempt->status = 'noshow';
                $attempt->save();
            }
        }

        return true;
    }

    /**
     * Modify event input before creating
     *   - before landing on select test team page
     *   - used to pull out empty tests (since now all tests show by default)
     */
    public function modifyEventInput($input)
    {
        $modified = $input;

        // remove all empty skill tests
        unset($modified['skill_seats']);
        unset($modified['skill_names']);

        if (array_key_exists('skill_seats', $input)) {
            foreach ($input['skill_seats'] as $comboInfo => $ss) {
                list($skillId, $disciplineId) = explode('|', $comboInfo);

                if (! empty($ss)) {
                    $modified['skill_seats'][$skillId] = $ss;
                    $modified['skill_names'][$skillId] = $input['skill_names'][$skillId];
                }
            }
        }

        // remove all empty knowledge tests
        unset($modified['exam_seats']);
        unset($modified['exam_names']);

        if (array_key_exists('exam_seats', $input)) {
            foreach ($input['exam_seats'] as $comboInfo => $ks) {
                list($examId, $disciplineId) = explode('|', $comboInfo);

                if (! empty($ks)) {
                    $modified['exam_seats'][$examId] = $ks;
                    $modified['exam_names'][$examId] = $input['exam_names'][$examId];
                }
            }
        }

        return $modified;
    }

    public function validate()
    {
        $rules = static::$rules;

        $rules['observer_id'] = 'required';
        $rules['facility_id'] = 'required|numeric|not_in:0';

        $messages = [
            'observer_id.required' => 'Observer is required.',
            'facility_id.required' => 'Invalid '.Lang::choice('core::terms.facility_testing', 1).' selection.',
            'facility_id.numeric'  => 'Invalid '.Lang::choice('core::terms.facility_testing', 1).' selection.',
            'facility_id.not_in'   => 'Invalid '.Lang::choice('core::terms.facility_testing', 1).' selection.'
        ];

        // get test dates
        $testDates = Input::get('test_date');
        if (! is_array($testDates)) {
            $testDates = array($testDates);
        }

        // check test dates
        foreach ($testDates as $i => $testDate) {
            if (! empty($testDate)) {
                $rules["test_date.{$i}"] = 'required|date';
            }
        }

        $validation = Validator::make($this->attributes, $rules, $messages);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();
        
        return false;
    }

    /**
     * Validate before updating an event
     */
    public function validateUpdate()
    {
        $rules = static::$rules;

        // facility id (testsite) is numeric now
        // (on events.create it starts as array)
        $rules['facility_id'] = 'required|numeric';
        $rules['observer_id'] = 'required|numeric|not_in:0';

        $messages = [
            'observer_id.required' => 'Observer is required.',
            'observer_id.numeric'  => 'Observer is required.',
            'observer_id.not_in'   => 'Observer is required.'
        ];

        // get test dates
        $testDates = Input::get('test_date');
        if (! is_array($testDates)) {
            $testDates = array($testDates);
        }

        // check test dates
        foreach ($testDates as $i => $testDate) {
            if (! empty($testDate)) {
                $rules["test_date.{$i}"] = 'required|date';
            }
        }

        $validation = Validator::make($this->attributes, $rules);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();
        
        return false;
    }

    /**
     * Before changing available skill seats in an event
     */
    public function validateSkillSeats($skillId, $new_seats)
    {
        $rules = $this->update_seats_rules;

        $skill = Skillexam::find($skillId);

        // get minimum number of seats (# people scheduled currently)
        $minSeats = $this->skillStudents()->where('skillexam_id', '=', $skillId)->count();
        $minSeats = $minSeats == 0 ? 1 : $minSeats;    // minimum of 1 seat always
        $rules['seats'] .= "|min:".$minSeats;

        // get maximum number of seats (# of unique testforms)
        $maxSeats = $skill->active_tests()->count();
        $rules['seats'] .= "|max:".$maxSeats;

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    public function validateKnowledgeSeats($examId, $newSeats)
    {
        $rules = $this->update_seats_rules;

        $exam = Exam::find($examId);

        // get minimum number of seats (# people scheduled currently)
        $minSeats = $this->knowledgeStudents()->where('exam_id', $examId)->count();
        $minSeats = ($minSeats == 0) ? 1 : $minSeats;    // minimum 1 seat always
        $rules['seats'] .= "|min:".$minSeats;

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

    /**
     * Validation for both SKILL and KNOWLEDGE fill seat pages
     *   - at least 1 student has been selected for scheduling
     *   - the exam is not already full
     */
    public function validateFillSeats()
    {
        $rules = static::$fill_seats_rules;

        $messages = [
            'student_id.required' => 'Select at least one '.Lang::choice('core::terms.student', 1).' to schedule.'
        ];

        // skill test?
        if (Input::get('skill_id')) {
            $rules['skill_id'] = "event_skill_has_seats:".Input::get('skill_id').",".Input::get('event_id');
            $messages['skill_id.event_skill_has_seats'] = 'Skill Exam is full.';
        }

        // knowledge test?
        if (Input::get('exam_id')) {
            $rules['exam_id'] = "event_exam_has_seats:".Input::get('exam_id').",".Input::get('event_id');
            $messages['exam_id.event_exam_has_seats'] = 'Knowledge Exam is full.';
        }

        $validation = Validator::make(Input::all(), $rules, $messages);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    /**
     * Validate a test event before selecting test team
     */
    public function validateBeforeTeam()
    {
        $rules = static::$rules;

        // facility_max_seats: checks each exam in the event doesnt go over test site hard limit (facilities.max_seats db field)
        // check_event_exams:  checks each exam has enough unique testforms to accompany # of seats requested
        $rules['facility_id'] = "required|not_in:0|facility_max_seats|check_event_exams";

        $messages = [
            'discipline_id.required'         => 'Invalid Discipline selection.',
            'discipline_id.not_in'           => 'Invalid Discipline selection.',
            'facility_id.required'           => Lang::choice('core::terms.facility_testing', 1).' is required.',
            'facility_id.not_in'             => 'Invalid '.Lang::choice('core::terms.facility_testing', 1).' selection.',
            'facility_id.facility_max_seats' => '',
            'facility_id.check_event_exams'  => '',
            'test_date.array_has_one'        => 'At least 1 one Test Date is required.'
        ];

        $validation = Validator::make(Input::all(), $rules, $messages);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        // flash errors where possible
        // easier to see errors when they are at the top
        if ($this->errors->has('test_date')) {
            Session::flash('danger', $this->errors->first('test_date'));
        }
        if ($this->errors->has('facility_id')) {
            Session::flash('danger', $this->errors->first('facility_id'));
        }
        if ($this->errors->has('discipline_id')) {
            Session::flash('danger', $this->errors->first('discipline_id'));
        }

        return false;
    }

    /**
     * Validate a request to schedule into knowledge exam
     * (from view students.find_knowledge_event)
     */
    public function validateSchedule($examId)
    {
        $exam = Exam::find($examId);

        $rules = $this->schedule_rules;
        $rules['exam_id'] = "event_exam_has_seats:".$examId.",".$this->id.
                            "|event_exam_has_testforms:".$examId.",".$this->id;

        $messages = [
            'event_exam_has_seats'        => 'Event :attribute <strong>'.$exam->name.'</strong> has no remaining seats.',
            'event_exam_has_testforms'    => 'Event :attribute <strong>'.$exam->name.'</strong> has no remaining testforms.'
        ];
        
        $validation = Validator::make(['exam_id' => $examId], $rules, $messages);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    /**
     * Validate a request to schedule into skill exam
     * (from view students.find_skill_event)
     */
    public function validateSkillSchedule($skillId)
    {
        $skill = Skillexam::find($skillId);

        $rules['skill_id'] = "event_skill_has_seats:".$skillId.",".$this->id;
        $messages = ['event_skill_has_seats' => 'Event :attribute <strong>'.$skill->name.'</strong> has no remaining seats.'];
        
        $validation = Validator::make(['skill_id' => $skillId], $rules, $messages);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    /**
     * End an event
     * @return 	boolean
     */
    public function end()
    {
        // eager load event and attempts
        $event = Testevent::with(
            'testattempts',
            'testattempts.student',
            'skillattempts',
            'skillattempts.student'
        )->find($this->id);

        // grab all written and skill attempts for this event
        $attempts        = $event->testattempts;
        $skills          = $event->skillattempts;
        $studentsBySkill = $skills->lists('student_id', 'id')->all();

        $dontTouch = ['passed', 'failed', 'rescheduled', 'started'];

        // Go through this event's knowledge attempts, score or mark as no-show
        foreach ($attempts as $a) {
            $opposingAttempt = in_array($a->student_id, $studentsBySkill);


            // if ONLY knowledge attempt, score this and fire the knowledge finished event
            if (! $opposingAttempt) {

                // Only if the attempt isn't in progress, rescheduled, or scored already
                // pending, noshow, assigned, unscored all will be scored
                if (! in_array($a->status, $dontTouch)) {
                    $a->score();
                }

                // fire testattempt finished event!
                Event::fire('student.finished_knowledge', [
                    'student' => $a->student,
                    'attempt' => $a
                ]);
            } else {
                // If this has NOT been rescheduled
                if ($a->status != 'rescheduled') {

                    // attempt status should be 'unscored' unless it is still 
                    // pending, in which case it should be 'noshow'
                    $a->status = $a->status == 'pending' ? 'noshow' : 'unscored';
                    $a->save();

                    $a->score(true);

                    // create a pending score for this attempt
                    $a->createPending();
                }
            }
        }

        // Go through this event's skill attempts, mark no-show, create pending
        foreach ($skills as $s) {

            // Mark no-shows if needed
            if (in_array($s->status, ['assigned', 'pending'])) {
                $s->status = 'noshow';
                $s->save();
            }

            // Now create a pending score, assuming this attempt wasn't rescheduled
            if ($s->status != 'rescheduled') {
                $s->score(true);
                $s->createPending();
            }
        }

        // mark this event as 'ended' now
        $this->ended = date('Y-m-d H:i:s');
        return $this->save();
    }
}
