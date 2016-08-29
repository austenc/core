<?php namespace Hdmaster\Core\Models\Student;

use View;
use Config;
use Crypt;
use Input;
use Validator;
use Session;
use Lang;
use DB;
use Auth;
use Flash;
use Log;
use Formatter;
use Carbon\Carbon;
use \Person;
use \StatusTrait;
use \Instructor;
use \User;
use \Ada;
use \Role;
use \Exam;
use \Testevent;
use \Facility;
use \Training;
use \Testattempt;
use \Certification;
use \Skillattempt;
use \Skilltest;
use \Skillexam;
use \SkilltaskResponse;
use \Discipline;
use \StudentTraining;
use Illuminate\Support\Collection;
use \Sorter;

class Student extends \Eloquent
{
    use Person, StatusTrait;

    protected $morphClass = 'Student';
    protected $fillable = [
        'user_id',
        'first',
        'last',
        'middle',
        'email',
        'ssn',
        'ssn_hash',
        'birthdate',
        'phone',
        'alt_phone',
        'is_unlisted',
        'is_oral',
        'password',
        'password_confirmation',
        'address',
        'city',
        'state',
        'zip',
        'comments',
        'gender',
        'creator_id',
        'creator_type',
        'media'
    ];

    protected $rules = [
        'first'                 => 'required',
        'last'                  => 'required',
        'gender'                => 'required',
        'email'                 => 'required|email',
        'phone'                 => 'required',
        'address'               => 'required',
        'city'                  => 'required',
        'state'                 => 'required|size:2',
        'zip'                   => 'required|min:5',
        'birthdate'             => 'required|date',
        'password'              => 'required|min:8|confirmed',
        'password_confirmation' => 'required|min:8',
        'ssn'                   => 'required|alpha_dash|proper_ssn',
        'rev_ssn'               => 'required|reverse_match'
    ];

    public $errors;
    public $actionableType;
    //public $certifications;

    /**
     * Constructor
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        // Have to define these here since we can't initialize dynamic properties in class definition
        $this->rules['email']     = 'required|email|unique:users';
        $this->rules['username']  = 'unique:users';
    }

    /**
     * Polymorphic 'creator' relation
     * @return Relation
     */
    public function creator()
    {
        return $this->morphTo();
    }

    /**
     *  Status Trait Function
     */
    public function studentHolds()
    {
        return $this->getStudentHolds();
    }

    public function studentLocks()
    {
        return $this->getStudentLocks();
    }

    public function isLocked()
    {
        return in_array('locked', explode(',', $this->status));
    }


    // -----------------------------------------------------------------------------------------------
    // -------------------------------------- TESTATTEMPTS -------------------------------------------
    // -----------------------------------------------------------------------------------------------
    /**
     * Get a collection containing all student testattempts AND skillattempts
     *
     * @param  boolean    exclude archived attempts if true 
     * @return  Collection
     */
    public function testHistory($active = true)
    {
        // grab only active attempts, or include archived if param false
        $knowledge = $active === true ? $this->activeAttempts()->get() : $this->attempts()->get();
        $skill     = $active === true ? $this->activeSkillAttempts()->get() : $this->skillAttempts()->get();

        // grab any knowledge attempts
        $allAttempts = $knowledge;

        // make sure we have a collection from this point
        if (empty($allAttempts)) {
            $allAttempts = new Collection;
        }

        // Go through any skill attempts and push them onto the collection
        foreach ($skill as $activeSkill) {
            $allAttempts->push($activeSkill);
        }

        return $allAttempts;
    }

    /**
     * Get entire history of student test attempts active and archived
     * @return Relation
     */
    public function attempts()
    {
        return $this->hasMany(Testattempt::class);
    }

    /**
     * Gets entire active test attempt history of any status
     * @return Relation
     */
    public function activeAttempts()
    {
        return $this->attempts()->where('archived', '=', false);
    }

    /**
     * Get all active failed test attempts
     * @return Relation
     */
    public function failedAttempts()
    {
        return $this->attempts()->where('testattempts.status', '=', 'failed')->where('testattempts.archived', '=', false);
    }

    /**
     * Get all active passed test attempts
     * @return Relation
     */
    public function passedAttempts()
    {
        return $this->attempts()->where('testattempts.status', '=', 'passed')->where('testattempts.archived', '=', false);
    }


    /**
     * Get all active failed exams
     */
    public function failedExams()
    {
        return $this->belongsToMany(Exam::class, 'testattempts')
                    ->where('testattempts.status', '=', 'failed')
                    ->where('testattempts.archived', '=', false);
    }

    /**
     * Get all active passed knowledge exams
     */
    public function passedExams()
    {
        return $this->belongsToMany(Exam::class, 'testattempts')
                    ->where('testattempts.status', '=', 'passed')
                    ->where('testattempts.archived', '=', false);
    }

    /**
     * Get all active scheduled test attempts, status assigned/pending/started
     * @return Relation
     */
    public function scheduledAttempts()
    {
        return $this->hasMany(Testattempt::class)
                    ->where('testattempts.archived', '=', false)
                    ->whereIn('testattempts.status', ['assigned', 'pending', 'started']);
    }

    /**
     * Get all active scheduled exams
     */
    public function scheduledExams()
    {
        return $this->belongsToMany(Exam::class, 'testattempts')
                    ->withPivot('testevent_id', 'facility_id', 'student_training_id', 'testform_id', 'status')
                    ->whereIn('testattempts.status', ['assigned', 'pending', 'started'])
                    ->where('testattempts.archived', '=', false);
    }



    // -----------------------------------------------------------------------------------------------
    // -------------------------------------- SKILLATTEMPTS ------------------------------------------
    // -----------------------------------------------------------------------------------------------

    /**
     * Get entire history of student skill attempts active and archived
     * @return Relation
     */
    public function skillAttempts()
    {
        return $this->hasMany(Skillattempt::class);
    }

    /**
     * Gets entire active skill attempt history of any status
     * @return Relation
     */
    public function activeSkillAttempts()
    {
        return $this->skillAttempts()->where('skillattempts.archived', '=', false);
    }

    /**
     * Get all active passed skill attempts
     * @return Relation
     */
    public function passedSkillAttempts()
    {
        return $this->skillAttempts()->where('skillattempts.status', '=', 'passed')->where('skillattempts.archived', '=', false);
    }

    /**
     * Get all active passed skill exams
     */
    public function passedSkills()
    {
        return $this->belongsToMany(Skillexam::class, 'skillattempts')
                    ->where('skillattempts.status', '=', 'passed')
                    ->where('skillattempts.archived', '=', false);
    }

    /**
     * Get all active failed skill attempts
     * @return Relation
     */
    public function failedSkillAttempts()
    {
        return $this->hasMany(Skillattempt::class)
                    ->where('skillattempts.status', '=', 'failed')
                    ->where('skillattempts.archived', '=', false);
    }

    /**
     * Get all active failed skill exams
     */
    public function failedSkills()
    {
        return $this->belongsToMany(Skillexam::class, 'skillattempts')
                    ->where('skillattempts.status', '=', 'failed')
                    ->where('skillattempts.archived', '=', false);
    }

    /**
     * Get all active scheduled skill attempts
     * @return [type] [description]
     */
    public function scheduledSkillAttempts()
    {
        return $this->hasMany(Skillattempt::class)
                    ->where('skillattempts.archived', '=', false)
                    ->whereIn('skillattempts.status', ['assigned', 'pending', 'started']);
    }

    public function scheduledSkills()
    {
        return $this->belongsToMany(Skillexam::class, 'skillattempts')
                    ->withPivot('testevent_id', 'facility_id', 'student_training_id', 'skilltest_id', 'status')
                    ->whereIn('skillattempts.status', ['assigned', 'pending', 'started'])
                    ->where('skillattempts.archived', '=', false);
    }

    /**
     * Get entire history of skill task responses active and archived
     * @return Relation
     */
    public function skillResponses()
    {
        return $this->hasMany(SkilltaskResponse::class);
    }

    /**
     * Get all active passed skill task responses
     * @return Relation
     */
    public function passedSkillResponses()
    {
        return $this->skillResponses()->where('skilltask_responses.status', '=', 'failed')->where('skilltask_responses.archived', '=', false);
    }

    /**
     * Get all active failed skill task responses
     * @return Relation
     */
    public function failedSkillResponses()
    {
        return $this->skillResponses()->where('skilltask_responses.status', '=', 'failed')->where('skilltask_responses.archived', '=', false);
    }



    // -----------------------------------------------------------------------------------------------
    // ---------------------------------------- FACILITY ---------------------------------------------
    // -----------------------------------------------------------------------------------------------

    /**
     * Get all facilities this student could get into if a testevent is closed 
     * Only count training record relation if its PASSED and ACTIVE
     */
    public function eligibleClosedSites()
    {
        return $this->belongsToMany(Facility::class, 'student_training', 'student_id', 'facility_id')
                    ->where('student_training.status', '=', 'passed')
                    ->withPivot('discipline_id')
                    ->whereNull('archived_at');
    }



    // -----------------------------------------------------------------------------------------------
    // --------------------------------------- INSTRUCTORS -------------------------------------------
    // -----------------------------------------------------------------------------------------------

    /**
     * Get current instructor according to the student_instructor table
     */
    public function currentInstructor()
    {
        return $this->instructors()->where('instructor_student.active', '=', true)->first();
    }

    /**
     * Gets all the student's previous instructors according to the student_instructor table
     */
    public function instructors()
    {
        return $this->belongsToMany(Instructor::class)->withPivot('active')->withTimestamps();
    }



    // -----------------------------------------------------------------------------------------------
    // ------------------------------------- CERTIFICATIONS ------------------------------------------
    // -----------------------------------------------------------------------------------------------

    /**
     * History of student certifications including expired and active
     */
    public function allCertifications()
    {
        return $this->belongsToMany(Certification::class, 'student_certification')
                    ->withPivot('certified_at', 'expires_at');
    }

    /**
     * Active certifications a student holds
     */
    public function certifications()
    {
        return $this->allCertifications()
                    ->whereNested(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', date("Y-m-d"));
                    });
    }

    // -----------------------------------------------------------------------------------------------
    // --------------------------------------  TRAININGS ---------------------------------------------
    // -----------------------------------------------------------------------------------------------

    public function allStudentTrainings()
    {
        return $this->hasMany(StudentTraining::class)->orderBy('started', 'DESC');
    }

    /**
     * Get all active student training records (student_training table)
     * Includes passed/failed/pending, all trainings as long as not archived or expired
     */
    public function allActiveStudentTrainings()
    {
        return $this->hasMany(StudentTraining::class)
                    ->orderBy('ended', 'DESC')
                    ->whereNull('archived_at')
                    ->whereNested(function ($q) {
                        $q->whereNull('expires')
                          ->orWhere('expires', '>', date("Y-m-d"));
                    });
    }

    /**
     * Get all passed student trainings (StudentTraining model)
     * Archived and Non-Archived both
     */
    public function allPassedStudentTrainings()
    {
        return $this->hasMany(StudentTraining::class)
                    ->orderBy('ended', 'DESC')
                    ->where('status', 'passed');
    }

    /**
     * Get all passed student trainings that are also ACTIVE
     * Direct access to StudentTraining record
     * Useful so you don't need to go through pivot for passedTrainings() relation
     */
    public function passedStudentTrainings()
    {
        return $this->allActiveStudentTrainings()->where('student_training.status', 'passed');
    }

    /**
     * Gets all passed trainings that are non-archived
     */
    public function passedTrainings()
    {
        return $this->trainings()->where('student_training.status', 'passed');
    }

    /**
     * PASSED or ATTENDING status non-archived training
     * "Unable to retake these training types"
     */
    public function currentTrainings()
    {
        return $this->trainings()->whereIn('student_training.status', ['attending', 'passed']);
    }

    /**
     * Get all Student Trainings of any status (passed/failed/pending) that are ACTIVE (i.e. not expired/archived)
     */
    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'student_training')
                    ->withPivot(
                        'id',
                        'facility_id',
                        'instructor_id',
                        'status',
                        'reason',
                        'classroom_hours',
                        'distance_hours',
                        'lab_hours',
                        'traineeship_hours',
                        'clinical_hours',
                        'started',
                        'ended',
                        'expires',
                        'archived_at',
                        'creator_id',
                        'creator_type')
                    ->withTimestamps()
                    ->whereNull('student_training.archived_at')
                    ->whereNested(function ($q) {
                        $q->whereNull('student_training.expires')
                          ->orWhere('student_training.expires', '>', date("Y-m-d"));
                    });
    }

    /**
     * Entire history of student training included expired/archived trainings
     */
    public function allTrainings()
    {
        return $this->belongsToMany(Training::class, 'student_training')
                    ->withPivot(
                        'id',
                        'discipline_id',
                        'facility_id',
                        'instructor_id',
                        'status',
                        'classroom_hours',
                        'distance_hours',
                        'lab_hours',
                        'traineeship_hours',
                        'clinical_hours',
                        'started',
                        'ended',
                        'expires',
                        'archived_at',
                        'creator_id',
                        'creator_type')
                    ->withTimestamps()
                    ->orderBy('started', 'desc');
    }

    /**
     * Gets all trainings a student has ever passed (Includes all expired/archived)
     */
    public function allPassedTrainings()
    {
        return $this->allTrainings()->where('student_training.status', 'passed');
    }
    


    // -----------------------------------------------------------------------------------------------
    // ------------------------------------- TESTEVENTS ----------------------------------------------
    // -----------------------------------------------------------------------------------------------

    /**
     * History of Knowledge testevents this student was ever associated with
     * Includes archived and active
     */
    public function events()
    {
        return $this->belongsToMany(Testevent::class, 'testattempts')
                    ->withPivot(
                        'id',
                        'student_id',
                        'testevent_id',
                        'exam_id',
                        'seat_type')
                    ->withTimestamps();
    }

    /**
     * History of Skill testevents this student was ever associated with
     * Includes archived and active
     */
    public function skillevents()
    {
        return $this->belongsToMany(Testevent::class, 'skillattempts')
                    ->withPivot(
                        'id',
                        'student_id',
                        'testevent_id',
                        'skilltest_id')
                    ->withTimestamps();
    }
    


    /**
     * Gets the most recent StudentTraining record for this student
     * Ordered by student_training.ended to ensure most recent of passed trainings comes thru
     */
    public function getMostRecentPassedStudentTrainingAttribute()
    {
        return $this->passedStudentTrainings()->first();
    }

    /**
     * Get date of first training set to expire 
     * Out of ALL trainings, no particular training subset/discipline
     */
    public function getFirstTrainingExpirationAttribute()
    {
        return $this->passedStudentTrainings()->get()->sortBy('expires')->first()->expires;
    }

    /**
     * Out of a collection of training ids (i.e. trainings required for an exam) 
     * find the first training that will expire and return expiration date
     */
    public function getFirstTrainingExpirationForTrainingIds($trainingIds)
    {
        if ($this->passedStudentTrainings->isEmpty() || empty($trainingIds)) {
            return false;
        }

        $keyPassedTrainings = $this->passedStudentTrainings->filter(function ($training) use ($trainingIds) {
            if (in_array($training->training_id, $trainingIds)) {
                return $training;
            }
        });

        // only interested in trainings within $trainingIds
        if ($keyPassedTrainings->isEmpty()) {
            return false;
        }

        return $keyPassedTrainings->sortBy('expires')->first()->expires;
    }

    /**
     * Get all student training dates required for each requested exam
     */
    public function getTrainingExpirationsForExams($examIds=[], $skillIds=[])
    {
        $exps     = [];
        $allExams = new Collection;

        if (! empty($examIds)) {
            $exams    = Exam::with('required_trainings')->whereIn('id', $examIds)->get();
            $allExams = $allExams->merge($exams);
        }
        if (! empty($skillIds)) {
            $skills = Skillexam::with('required_trainings')->whereIn('id', $skillIds)->get();
            foreach ($skills as $s) {
                $allExams->push($s);
            }
        }
        
        // get all passed training for student
        $trainings = $this->passedTrainings()->get();

        // all exams (skill and knowledge)
        foreach ($allExams as $exam) {
            foreach ($exam->required_trainings as $reqTr) {
                $tr = $trainings->find($reqTr->id);

                // knowledge
                if (isset($reqTr->pivot->exam_id)) {
                    if (! isset($exps['exam'][$exam->id]) || (strtotime($tr->pivot->expires) < strtotime($exps['exam'][$exam->id]))) {
                        $exps['exam'][$exam->id] = $tr->pivot->expires;
                    }
                }

                // skills
                else {
                    if (! isset($exps['skill'][$exam->id]) || (strtotime($tr->pivot->expires) < strtotime($exps['skill'][$exam->id]))) {
                        $exps['skill'][$exam->id] = $tr->pivot->expires;
                    }
                }
            }
        }

        return $exps;
    }

    /**
     * Handles advanced searching (session-based) 
     * and returns the resulting results or null
     */
    public function handleSearch()
    {
        $loggedUser = Auth::user();

        // sidebar filter (active|inactive|all)
        if (Input::get('s')) {
            Session::put('students.search.filter', Input::get('s'));
        } else {
            // no filter set
            // default showing all: created by or trained here
            if ($loggedUser->isRole('Instructor') || $loggedUser->isRole('Facility')) {
                Session::forget('students.search.filter');
            } elseif ($loggedUser->ability(['Admin', 'Staff', 'Agency'], []) && ! Session::has('students.search.filter')) {
                // default active region
                Session::put('students.search.filter', 'active');
            }
        }

        // get current search filters
        $search_types   = Session::get('students.search.types');
        $search_queries = Session::get('students.search.queries');
        $filter         = Session::get('students.search.filter');


        // base query
        $q = DB::table('students')
            ->select(
                'students.*',
                'users.username AS username',
                'users.email AS email'
            );


        // ----------------------------------------------------------------------------------------------------
        // ---------------------------------------- JOIN TABLES -----------------------------------------------
        // ----------------------------------------------------------------------------------------------------
        $q->join('users', 'students.user_id', '=', 'users.id');

        // Poweruser searching
        if ($loggedUser->ability(['Admin', 'Staff', 'Agency'], [])) {
            $q->leftJoin('student_training', 'students.id', '=', 'student_training.student_id');
        } elseif ($loggedUser->isRole('Facility')) {
            // Facility searching
            $currDisciplineId = Session::get('discipline.id');

            // get all affiliated training site ids
            $facility = Facility::with('affiliated')->find($loggedUser->userable_id);
            $validFacilityIds = array_merge(array($loggedUser->userable_id), $facility->affiliated->lists('id')->all());
            
            $q->join('student_training', function ($join) use ($validFacilityIds) {
                $join->on('student_training.student_id', '=', 'students.id')->whereNull('student_training.archived_at');
            })
            ->whereIn('student_training.facility_id', $validFacilityIds)
            ->where('student_training.discipline_id', $currDisciplineId);
        } elseif ($loggedUser->isRole('Instructor')) {
            // Instructor searching
            $currTrProgramId = Session::get('discipline.program.id');
            $currDisciplineId = Session::get('discipline.id');

            $q->join('instructor_student', function ($join) use ($loggedUser) {
                // limit search to only their active students
                $join->on('instructor_student.student_id', '=', 'students.id')
                     ->where('instructor_student.instructor_id', '=', $loggedUser->userable_id)
                     ->where('instructor_student.active', '=', true);
            });

            $q->join('student_training', function ($join) use ($currTrProgramId, $currDisciplineId) {
                $join->on('student_training.student_id', '=', 'students.id')
                     ->where('student_training.discipline_id', '=', $currDisciplineId)
                     ->where('student_training.facility_id', '=', $currTrProgramId)
                     ->whereNull('student_training.archived_at');
            });
        } else {
            // Other searching
            // make sure expired trainings arent used
            $q->leftJoin('student_training', function ($join) {
                $join->on('student_training.student_id', '=', 'students.id')
                     ->where('student_training.expires', '>=', date('Y-m-d'))
                     ->whereNull('student_training.archived_at');
            });
        }

        // ADA and Training tables
        $q->leftJoin('student_ada', 'students.id', '=', 'student_ada.student_id');
        $q->leftJoin('trainings', 'student_training.training_id', '=', 'trainings.id');
        

        // ----------------------------------------------------------------------------------------------------
        // ----------------------------------- INCLUDE SEARCH PARAMS ------------------------------------------
        // ----------------------------------------------------------------------------------------------------
        if ($search_types !== null) {
            foreach ($search_types as $k => $type) {
                $search = $search_queries[$k];

                switch ($type) {
                    case 'Name':
                        // Last, First
                        if (strpos($search, ',') !== false) {
                            list($last, $first) = array_map('trim', explode(',', $search, 2));

                            $q->where(function ($query) use ($first, $last) {
                                $query->where('students.first', 'like', $first.'%')
                                      ->where('students.last', 'like', $last.'%');
                            });
                        } elseif (strpos($search, ' ') !== false) {
                            // First Last
                            list($first, $last) = array_map('trim', explode(' ', $search, 2));

                            $q->where(function ($query) use ($first, $last) {
                                $query->where('students.first', 'like', $first.'%')
                                      ->where('students.last', 'like', $last.'%');
                            });
                        } else {
                            // Last or First
                            list($first, $last) = array_map('trim', array($search, $search));

                            // OR WHERE
                            $q->where(function ($query) use ($first, $last) {
                                $query->where('students.first', 'like', $first.'%')
                                      ->orWhere('students.last', 'like', $last.'%');
                            });
                        }

                        
                    break;
                    case 'Email':
                        $q->where('users.email', 'like', '%'.$search.'%');
                    break;
                    case 'SSN':
                        $stripSSN = str_replace(['-', '_', ''], '', $search);
                        $q->where('students.ssn_hash', '=', saltedHash($stripSSN));
                    break;
                    case 'City':
                        $q->where('students.city', 'like', $search.'%');
                    break;
                    case 'TestID':
                        $stripTestID = str_replace(['-', '_', ''], '', $search);
                        $convertedSSN = $this->DecryptTestID($stripTestID);
                        $q->where('students.ssn_hash', '=', saltedHash($convertedSSN));
                    break;

                    case 'Trained At (name)':
                        $q->join('facilities', 'student_training.facility_id', '=', 'facilities.id');
                        $q->where('facilities.name', 'like', $search.'%');
                    break;
                    case 'Trained At (license)':
                        $q->join('facility_discipline', 'student_training.facility_id', '=', 'facility_discipline.facility_id');
                        $q->where('facility_discipline.tm_license', '=', $search);
                    break;

                    case 'Trained By (name)':
                        $q->join('instructors', 'student_training.instructor_id', '=', 'instructors.id');

                        // Last, First
                        if (strpos($search, ',') !== false) {
                            list($last, $first) = array_map('trim', explode(',', $search, 2));

                            $q->where(function ($query) use ($first, $last) {
                                $query->where('instructors.first', 'like', $first.'%')
                                      ->where('instructors.last', 'like', $last.'%');
                            });
                        } elseif (strpos($search, ' ') !== false) {
                            // First Last
                            list($first, $last) = array_map('trim', explode(' ', $search, 2));

                            $q->where(function ($query) use ($first, $last) {
                                $query->where('instructors.first', 'like', $first.'%')
                                      ->where('instructors.last', 'like', $last.'%');
                            });
                        } else {
                            // Last or First
                            list($first, $last) = array_map('trim', array($search, $search));

                            // OR WHERE
                            $q->where(function ($query) use ($first, $last) {
                                $query->where('instructors.first', 'like', $first.'%')
                                      ->orWhere('instructors.last', 'like', $last.'%');
                            });
                        }
                    break;
                    case 'Trained By (license)':
                        $q->join('facility_person', function ($join) {
                            $join->on('student_training.instructor_id', '=', 'facility_person.person_id')
                                 ->where('facility_person.person_type', '=', 'Instructor');
                        });
                        $q->where('facility_person.tm_license', '=', $search);
                    break;

                    case 'Training Type':
                        $q->where('trainings.name', 'like', '%'.$search.'%');
                    break;
                    case 'Training Status':
                        $q->where('student_training.status', '=', $search);
                    break;
                    case 'Training Begin':
                        $q->where('student_training.started', '=', date('Y-m-d', strtotime($search)));
                    break;
                    case 'Training End':
                        $q->where('student_training.ended', '=', date('Y-m-d', strtotime($search)));
                    break;
                    case 'Training Expires':
                        $q->where('student_training.expires', '=', date('Y-m-d', strtotime($search)));
                    break;
                    case 'Created On':
                        $q->where('students.created_at', '>=', date('Y-m-d H:i:s', strtotime($search)));
                    break;
                    case 'Updated On':
                        $q->where('students.updated_at', '>=', date('Y-m-d', strtotime($search)));
                    break;
                    case 'ADA Status':
                        $q->where('student_ada.status', '=', $search);
                    break;


                    default:
                        // do nothing for now, just show all records
                }
            }
        }



        // ----------------------------------------------------------------------------------------------------
        // ------------------------------------- SET SIDEBAR COUNTS -------------------------------------------
        // ----------------------------------------------------------------------------------------------------
        if ($loggedUser->ability(['Admin', 'Staff', 'Agency'], [])) {
            // count results (active/archived/all)

            $qArch   = clone $q;
            $qAll    = clone $q;
            $qActive = clone $q;

            $resArch   = $qArch->where('students.status', 'LIKE', '%archive%')->count(DB::raw('DISTINCT students.id'));
            $resActive = $qActive->where('students.status', 'LIKE', '%active%')->count(DB::raw('DISTINCT students.id'));
            $resAll    = $qAll->count(DB::raw('DISTINCT students.id'));

            $r['count']['inactive'] = $resArch;
            $r['count']['active']   = $resActive;
            $r['count']['all']      = $resAll;

            // apply region filter to main query
            if ($filter) {
                if ($filter == 'inactive') {
                    $q->where('students.status', 'LIKE', '%archive%');
                } elseif ($filter == 'active') {
                    $q->where('students.status', 'LIKE', '%active%');
                }
            } else {
                // no filter, default show only active region students
                $q->where('students.active', 'LIKE', '%active%');
            }
        } elseif ($loggedUser->isRole('Instructor') || $loggedUser->isRole('Facility')) {
            // count results (all/active/completed) 

            // only show active students
            $q->where('students.status', 'LIKE', '%active%');

            // facility user
            /*if($loggedUser->isRole('Facility'))
            {
                // only students with a training here?
            }*/

            $qPassed = clone $q; // has a passed training w/ instructor or at this facility
            $qAttend = clone $q; // has an attending training w/ instructor or at this facility
            $qAll    = clone $q; // doesnt have to have a training yet (may have just been created)

            $resPassed = $qPassed->where('student_training.status', '=', 'passed')->count(DB::raw('DISTINCT students.id'));
            $resAttend = $qAttend->where('student_training.status', '=', 'attending')->count(DB::raw('DISTINCT students.id'));
            $resAll    = $qAll->count(DB::raw('DISTINCT students.id'));

            $r['count']['passed']    = $resPassed;
            $r['count']['attending'] = $resAttend;
            $r['count']['all']       = $resAll;

            // filter (passed/attending/failed)
            if ($filter) {
                $q->where('student_training.status', $filter);
            }
        }



        // Group it properly and handle sorting params
        $r['students'] = $q->groupBy('students.id')
                           ->orderBy(Input::get('sort', 'last'), Sorter::order())
                           ->paginate(Config::get('core.pagination.default'));
        
        return $r;
    }

    /**
     * Active ADAs
     */
    public function adas()
    {
        return $this->allAdas()->whereNull('student_ada.deleted_at');
    }

    /**
     * All ADAs including soft deleted
     */
    public function allAdas()
    {
        return $this->belongsToMany(Ada::class, 'student_ada')->withPivot('status', 'notes', 'deleted_at')->withTimestamps();
    }

    public function acceptedAdas()
    {
        return $this->adas()->where('status', 'accepted');
    }

    public function pendingAdas()
    {
        return $this->adas()->where('status', 'pending');
    }

    // scopes
    public function scopeFailedTasks($query)
    {
        return $query->where('skilltask_responses.status', '=', 'failed');
    }

    /**
     * Tries splitting names by space or comma and returns any with names like them
     */
    public function scopeNameLike($query, $string)
    {
        // replace commas with spaces
        $string = str_replace(',', ' ', $string);
        $parts  = explode(' ', $string);
        $last   = trim(array_pop($parts));
        $first  = implode(' ', $parts);
        
        // make like query, maybe several?
        return $query->where(function ($query) use ($first, $last) {
            $query->where('first', 'LIKE', '%'.$first.'%');
            $query->where('last', 'LIKE', '%'.$last.'%');
        })
        ->orWhere(function ($query) use ($first, $last) {
            $query->where('first', 'LIKE', '%'.$last.'%');
            $query->where('last', 'LIKE', '%'.$first.'%');
        });
    }

    /**
     * Get all rescheduled attempts for this student
     */
    public function getRescheduled()
    {
        $r = [];

        $re['knowledge'] = \DB::table('testattempts')->where('status', 'rescheduled')->where('student_id', $this->id)->get();
        $re['skill']     = \DB::table('skillattempts')->where('status', 'rescheduled')->where('student_id', $this->id)->get();

        foreach ($re as $type => $info) {
            foreach ($info as $data) {
                $currEvent = Testevent::find($data->testevent_id);

                if ($currEvent) {
                    $data->event_date = $currEvent->test_date;
                }

                $r[] = $data;
            }
        }

        return $r;
    }



    /**
     * Gets all test attempts that are started OR pending
     * @return mixed
     */
    public function actionableTests()
    {
        // any 'started' tests?
        $started = $this->attempts()->with('exam', 'testform')->where('testattempts.status', '=', 'started')->get();
        if (! $started->isEmpty()) {
            $in_progress = new Collection;

            // make sure only tests that have time remaining are used
            foreach ($started as $test) {
                if ($test->timeRemaining > 0) {
                    $in_progress->push($test);
                    $this->actionableType = 'started';
                    return $in_progress;
                }
            }

            // we have some started tests, just return them. (more important than pending)
        }

        $pending = $this->attempts()->with('exam')->where('testattempts.status', '=', 'pending')->get();
        if (! $pending->isEmpty()) {
            $this->actionableType = 'pending';
            return $pending;
        }

        return null;
    }

    /**
     * Trainings that this Student would be eligible for
     * Optional discipline param, when used only considers trainings within that discipline
     */
    public function availableTrainings($disciplineId='')
    {
        $available = new Collection;

        // get all active PASSED or ATTENDING training this Student has
        // (student wont be eligible to take the training of already has training with passed/attending status on file)
        $ineligibleTrainings = $this->currentTrainings()->get();

        // get trainings under discipline
        if (! empty($disciplineId)) {
            $discipline = Discipline::with('training.required_trainings')->findOrFail($disciplineId);
            $trainings = $discipline->training;
        }
        // get all trainings
        else {
            $trainings = Training::with('required_trainings')->get();
        }

        if (! empty($trainings)) {
            foreach ($trainings as $tr) {
                // filter to only get active passed/attending training for this current training type
                $currActiveTrainings = $ineligibleTrainings->filter(function ($activeTraining) use ($tr) {
                    return $activeTraining->pivot->training_id == $tr->id;
                });

                // student has passed/attending on file
                // this training is NOT eligible
                if (! $currActiveTrainings->isEmpty()) {
                    continue;
                }

                // determine if this student has passed all requirements for this training
                $remTrainingReqs = array_diff($tr->required_trainings->lists('id')->all(), $this->passedTrainings->lists('id')->all());

                // all requirements satisfied?
                // training is eligible for student!
                if (empty($remTrainingReqs)) {
                    $available->push($tr);
                }
            }
        }

        return $available;
    }

    /**
     * Updates any certifications a student has
     *   - called after fired event (ie finished exam)
     */
    public function refreshCertifications()
    {
        $certs = Certification::with([
            'required_exams',
            'required_skills'
        ])->get();

        // passed exams/skills
        $passedExamIds      = $this->passedExams()->get()->lists('id')->all();
        $passedSkillIds     = $this->passedSkills()->get()->lists('id')->all();
         
        // get students current certifications
        $currCertifications = $this->certifications()->lists('id')->all();

        // certification exams
        foreach ($certs as $cert) {
            // does student already have this cert?
            if (in_array($cert->id, $currCertifications)) {
                continue;
            }

            // check exam certifications
            $reqCertExams = $cert->required_exams->lists('id')->all();
            if (! empty($reqCertExams)) {
                $remExamReqs = array_diff($reqCertExams, $passedExamIds);

                // missing exams..
                if (! empty($remExamReqs)) {
                    continue;
                }
            }

            // check skill certifications
            $reqCertSkills = $cert->required_skills->lists('id')->all();
            if (! empty($reqCertSkills)) {
                $remSkillReqs = array_diff($reqCertSkills, $passedSkillIds);

                // missing skills..
                if (! empty($remSkillReqs)) {
                    continue;
                }
            }

            // if made it this far, student has new certification that either does not yet exist or is expired
            $this->certifications()->attach($cert->id, [
                'certified_at'    => date('Y-m-d H:i:s'),
                'expires_at'    => date('Y-m-t H:i:s', strtotime('+2 years'))
            ]);
        }
    }


    /**
     * Returns a collection containing all exams that are missing any sort of requirement
     */
    public function ineligibleExams()
    {
        $ineligible = [];

        // all knowledge exams
        $exams = Exam::with([
            'required_exams',
            'required_trainings',
            'required_skills',
            'corequired_skills',
            'corequired_skills.required_trainings',
            'corequired_skills.required_exams'
        ])->get();


        // load relations where necessary
        if ($this->passedTrainings === null) {
            $this->load('passedTrainings');
        }
        if ($this->passedExams === null) {
            $this->load('passedExams');
        }
        if ($this->passedSkills === null) {
            $this->load('passedSkills');
        }
        if ($this->scheduledExams === null) {
            $this->load('scheduledExams');
        }

        // get any attempts being scored (aka results unreleased to student)
        $beingScored = new Collection;

        if ($this->attempts) {
            $beingScored = $this->attempts->filter(function ($attempt) {
                return ! in_array($attempt->exam_id, $this->scheduledExams->lists('id')->all()) && $attempt->seeResults === false;
            });
        }

        if (! empty($exams)) {
            foreach ($exams as $ex) {
                $errors = [];

                // has student already passed this exam?
                if (in_array($ex->id, $this->passedExams->lists('id')->all())) {
                    $errors['status'] = 'Previously Passed';
                }
                // is student already scheduled for this exam?
                elseif (in_array($ex->id, $this->scheduledExams->lists('id')->all())) {
                    $errors['status'] = 'Scheduled';
                }
                // is exam being scored?
                elseif (in_array($ex->id, $beingScored->lists('exam_id')->all())) {
                    $errors['status'] = 'Being Scored';
                }

                // check if student has completed all corequired exams 
                // either passed the corequired exam already, or has passed all prereqs
                $this->checkSkillCorequirements($ex->corequired_skills, $this->passedExams, $this->passedTrainings, $this->passedSkills, $errors);

                // checks if student has all training requirements for this knowledge exam
                $this->checkTrainingRequirements($ex->required_trainings, $this->passedTrainings, $errors);

                // checks if student has all knowledge requirements for this knowledge exam
                $this->checkKnowledgeRequirements($ex->required_exams, $this->passedExams, $errors);

                // checks if student has all skillexam requirements for this knowledge exam
                $this->checkSkillRequirements($ex->required_skills, $this->passedSkills, $errors);


                // add exam as ineligible if at least 1 prereq is missing
                if (! empty($errors)) {
                    if (! isset($errors['status'])) {
                        $errors['status'] = 'Not Eligible';
                    }

                    $ex->errors          = $errors;
                    $ineligible[$ex->id] = $ex;
                }
            }
        }

        return new Collection($ineligible);
    }

    /**
     * Returns a collection containing all skillexams missing any requirement and why the skillexam isnt available
     */
    public function ineligibleSkills()
    {
        $ineligible = [];

        // get all skillexams
        $skillexams = Skillexam::with([
            'required_trainings',
            'required_exams',
            'corequired_exams',
            'corequired_exams.required_exams',
            'corequired_exams.required_trainings',
            'corequired_exams.required_skills'
        ])->get();

        // load relations where necessary
        if ($this->passedExams === null) {
            $this->load('passedExams');
        }
        if ($this->passedTrainings === null) {
            $this->load('passedTrainings');
        }
        if ($this->passedSkills === null) {
            $this->load('passedSkills');
        }
        if ($this->scheduledSkills === null) {
            $this->load('scheduledSkills');
        }

        // get any attempts being scored (aka results unreleased to student)
        $beingScored = new Collection;
        if ($this->skillAttempts) {
            $beingScored = $this->skillAttempts->filter(function ($attempt) {
                return ! in_array($attempt->skillexam_id, $this->scheduledSkills->lists('id')->all()) && $attempt->seeResults === false;
            });
        }

        if (! empty($skillexams)) {
            foreach ($skillexams as $se) {
                $errors = [];

                // has student already passed this exam?
                if (in_array($se->id, $this->passedSkills->lists('id')->all())) {
                    $errors['status'] = 'Previously Passed';
                }
                // is student already scheduled for this exam?
                elseif (in_array($se->id, $this->scheduledSkills->lists('id')->all())) {
                    $errors['status'] = 'Scheduled';
                }
                // is skillexam being scored?
                elseif (in_array($se->id, $beingScored->lists('skillexam_id')->all())) {
                    $errors['status'] = 'Being Scored';
                }

                // check if student has completed all corequired exams 
                // either passed the corequired exam already, or has passed all prereqs
                $this->checkKnowledgeCorequirements($se->corequired_exams, $this->passedExams, $this->passedTrainings, $this->passedSkills, $errors);

                // checks if student has all training requirements for this skillexam
                $this->checkTrainingRequirements($se->required_trainings, $this->passedTrainings, $errors);

                // checks if student has all knowledge requirements for this skillexam
                $this->checkKnowledgeRequirements($se->required_exams, $this->passedExams, $errors);

                // only add the skilltest if at least 1 prereq is missing (and they arent already scheduled for the skilltest)
                if (! empty($errors)) {
                    if (! isset($errors['status'])) {
                        $errors['status'] = 'Not Eligible';
                    }

                    $se->errors          = $errors;
                    $ineligible[$se->id] = $se;
                }
            }
        }

        return new Collection($ineligible);
    }


    /**
     * Checks if a student has all training requirements completed
     * @param   $requiredTrainings - Collection of required trainings
     * @param 	$passedTrainings - Collection of passed trainings this student has
     * @param   $errors - pass by reference, hold info related to what trainings are missing
     */
    public function checkTrainingRequirements($requiredTrainings, $passedTrainings, &$errors)
    {
        if (! $requiredTrainings->isEmpty()) {
            $remTrainingsReqs = $requiredTrainings->diff($passedTrainings);

            // if there are training remaining that havent been passed yet
            if (! $remTrainingsReqs->isEmpty()) {
                foreach ($remTrainingsReqs as $remTraining) {
                    $errors['missing']['Training'][$remTraining->id]['name']   = $remTraining->name;
                    $errors['missing']['Training'][$remTraining->id]['reason'] = 'Required';
                }
            }
        }
    }

    /**
     * Checks if a student has all knowledge exam requirements completed
     * @param   $requiredKnowledge - Collection of required knowledge exams
     * @param 	$passedKnowledge - Collection of passed knowledge exams this student has
     * @param   $errors - pass by reference, hold info related to what knowledge exams are missing
     */
    public function checkKnowledgeRequirements($requiredKnowledge, $passedKnowledge, &$errors)
    {
        if (! $requiredKnowledge->isEmpty()) {
            $remKnowledgeReqs = $requiredKnowledge->diff($passedKnowledge);

            if (! $remKnowledgeReqs->isEmpty()) {
                foreach ($remKnowledgeReqs as $remKnowledge) {
                    $missingReqs['missing']['Knowledge'][$remKnowledge->id]['name']   = $remKnowledge->name;
                    $missingReqs['missing']['Knowledge'][$remKnowledge->id]['reason'] = 'Required';
                }
            }
        }
    }

    /**
     * Checks if a student has either passed, or has all prereqs for every corequired exam completed
     */
    public function checkKnowledgeCorequirements($requiredKnowledge, $passedKnowledge, $passedTraining, $passedSkills, &$errors)
    {
        if (! $requiredKnowledge->isEmpty()) {
            $remKnowledgeReqs = $requiredKnowledge->diff($passedKnowledge);

            if (! $remKnowledgeReqs->isEmpty()) {
                foreach ($remKnowledgeReqs as $remKnowledge) {
                    // check if student has all prerequirements for the corequired exam!
                    // if any requirements are missing, return
                    // we only want to know if YES or NO, ready to take corequired

                    // check trainings
                    if (! $remKnowledge->required_trainings->isEmpty()) {
                        $remReqTraining = $remKnowledge->required_trainings->diff($passedTraining);
                        if (! $remReqTraining->isEmpty()) {
                            $errors['missing']['Knowledge'][$remKnowledge->id]['name']   = $remKnowledge->name;
                            $errors['missing']['Knowledge'][$remKnowledge->id]['reason'] = 'Corequired';
                            return;
                        }
                    }

                    // check exams
                    if (! $remKnowledge->required_exams->isEmpty()) {
                        $remReqKnowledge = $remKnowledge->required_exams->diff($passedKnowledge);
                        if (! $remReqKnowledge->isEmpty()) {
                            $errors['missing']['Knowledge'][$remKnowledge->id]['name']   = $remKnowledge->name;
                            $errors['missing']['Knowledge'][$remKnowledge->id]['reason'] = 'Corequired';
                            return;
                        }
                    }

                    // check skills
                    if (! $remKnowledge->required_skills->isEmpty()) {
                        $remReqSkill = $remKnowledge->required_skills->diff($passedSkills);
                        if (! $remReqSkill->isEmpty()) {
                            $errors['missing']['Knowledge'][$remKnowledge->id]['name']   = $remKnowledge->name;
                            $errors['missing']['Knowledge'][$remKnowledge->id]['reason'] = 'Corequired';
                            return;
                        }
                    }
                } // end FOREACH remaining knowledge corequired (not passed)
            }
        }
    }

    /**
     * Checks if a student has all skillexam requirements completed
     * @param   $requiredSkills - Collection of required skillexams
     * @param 	$passedSkills - Collection of passed skills attempts this student has
     * @param   $errors - pass by reference, hold info related to what skills are missing
     */
    public function checkSkillRequirements($requiredSkills, $passedSkills, &$errors)
    {
        if (! $requiredSkills->isEmpty()) {
            $remSkillReqs = $requiredSkills->diff($passedSkills);

            // if there are training remaining that havent been passed yet
            if (! $remSkillReqs->isEmpty()) {
                foreach ($remSkillReqs as $remSkill) {
                    $errors['missing']['Skill'][$remSkill->id]['name']   = $remSkill->name;
                    $errors['missing']['Skill'][$remSkill->id]['reason'] = 'Required';
                }
            }
        }
    }

    /**
     * Checks if a student has passed or has all prereqs for every corequired skillexam completed
     */
    public function checkSkillCorequirements($requiredSkill, $passedKnowledge, $passedTraining, $passedSkills, &$errors)
    {
        if (! $requiredSkill->isEmpty()) {
            $remSkillReqs = $requiredSkill->diff($passedSkills);

            if (! $remSkillReqs->isEmpty()) {
                foreach ($remSkillReqs as $remSkill) {
                    // check trainings
                    if (! $remSkill->required_trainings->isEmpty()) {
                        $remReqTraining = $remSkill->required_trainings->diff($passedTraining);
                        if (! $remReqTraining->isEmpty()) {
                            $errors['missing']['Skill'][$remSkill->id]['name']   = $remSkill->name;
                            $errors['missing']['Skill'][$remSkill->id]['reason'] = 'Corequired';
                            return;
                        }
                    }

                    // check exams
                    if (! $remSkill->required_exams->isEmpty()) {
                        $remReqKnowledge = $remSkill->required_exams->diff($passedKnowledge);
                        if (! $remReqKnowledge->isEmpty()) {
                            $errors['missing']['Skill'][$remSkill->id]['name']   = $remSkill->name;
                            $errors['missing']['Skill'][$remSkill->id]['reason'] = 'Corequired';
                            return;
                        }
                    }

                    // check skills..
                    // no need, system was not designed to have skill require skill
                }
            }
        }
    }

    /**
     * Before creating a new student, check if doesnt already exist
     * This function checks another student with SSN doesnt already exist
     */
    public function existingStudent()
    {
        $ssn = Input::get('ssn');

        $existingStudent = Student::where('ssn_hash', saltedHash($ssn))->first();

        if ($existingStudent) {
            return $existingStudent->id;
        }

        return false;
    }

    /**
     * All disciplines this student is eligible for
     * In workbench its just default all
     * In Oregon, this will be an easy to override function
     *    where the student needs CNA certification (discipline) before beginning CMA
     */
    public function availableDisciplines()
    {
        return Discipline::all();
    }

    /**
     * Find all events containing certain skill and/or knowledge exams within a discipline and has at least $requested seats remaining
     */
    public function findEvent($discipline, $knowTestIds=[], $skillTestIds=[], $requestedSeats=1, $trainingExps=[])
    {
        // get expiration dates for each training
        // they cant schedule into an event past when their training expires
        // get first training set to expire
        $exps            = empty($trainingExps) ? $this->getTrainingExpirationsForExams($knowTestIds, $skillTestIds) : [];
        $trainingExpires = empty($exps) ? '' : min($exps);

        $eligibleEvts    = [];

        // get all events (nothing outside of students testing expiration date)
        $events = Testevent::with([
            'facility',
            'facility.affiliated' => function ($query) use ($discipline) {
                $query->where('facility_affiliated.discipline_id', $discipline->id);
            },
            'exams.active_testforms',
            'skills.active_tests',
            'testattempts',
            'skillattempts'
        ])
        ->where('discipline_id', $discipline->id)
        ->where('test_date', '>=', date('Y-m-d'))
        ->where('locked', '=', 0);

        // test date must be before training expires
        if (! empty($trainingExpires)) {
            $events->where('test_date', '<', $trainingExpires);
        }

        // oral student?
        // only restrict to paper events only if the student is requesting a knowledge event
        if ($this->is_oral && ! empty($knowTestIds)) {
            $events->where('is_paper', true);
        }

        // order by test date
        $events = $events->orderBy('test_date', 'ASC')->get();

        // get all sites the student would be eligible for if event is closed
        //  (programs student has: passed non-archived training within same discipline)
        $closedSiteIds = $this->eligibleClosedSites->filter(function ($site) use ($discipline) {
            return $discipline->id == $site->pivot->discipline_id;
        })->lists('id')->all();

        if (! empty($events)) {
            foreach ($events as $event) {
                $remSkillIds = [];
                $remKnowIds  = [];

                // closed event? (only students trained here or at affiliated sites are eligible)
                if ($event->is_regional == 0) {
                    // all facility ids that we can allow this student into if closed event
                    //   (current site + affiliated programs)
                    $affiliatedIds = array_merge([$event->facility_id], $event->facility->affiliated->lists('id')->all());

                    // intersect to see if student has active passed training record at this (or any affiliated) test site
                    $matchedTrainedAtIds = array_intersect($closedSiteIds, $affiliatedIds);

                    // is student eligible to get into this closed event?
                    if (empty($matchedTrainedAtIds)) {
                        continue;
                    }
                }

                // knowledge test requested?
                if (! empty($knowTestIds)) {
                    $remKnowIds = array_diff($knowTestIds, $event->exams->lists('id')->all());
                }

                // skill test requested?
                if (! empty($skillTestIds)) {
                    $remSkillIds = array_diff($skillTestIds, $event->skills->lists('id')->all());
                }
                
                // does this event contain all requested exams?
                if (empty($remSkillIds) && empty($remKnowIds)) {
                    $allSkillsOK = true;
                    $allKnowOK   = true;

                    // check available skill seats
                    foreach ($event->skills as $sk) {
                        // skill test we are interested in?
                        if (in_array($sk->id, $skillTestIds)) {
                            // get all event students scheduled for this event skillexam only
                            $currAttempts = $event->skillattempts->filter(function ($item) use ($sk) {
                                return $item->skillexam_id == $sk->id;
                            });

                            // is there enough remaining seats?
                            $totalSeats = $sk->pivot->open_seats;
                            $takenSeats = $currAttempts->count();
                            $remSeats = (int)$totalSeats - (int)$takenSeats;

                            // no remaining seats..
                            if ($remSeats < $requestedSeats) {
                                $allSkillsOK = false;
                            }

                            // no active testforms..
                            if ($sk->active_tests->isEmpty()) {
                                $allSkillsOK = false;
                            }
                        }
                    }

                    // check available knowledge seats
                    foreach ($event->exams as $kn) {
                        // knowledge test we are interested in?
                        if (in_array($kn->id, $knowTestIds)) {
                            // get all event students scheduled for this event exam only
                            $currAttempts = $event->testattempts->filter(function ($item) use ($kn) {
                                return $item->exam_id == $kn->id;
                            });

                            // is there enough remaining seats?
                            $totalSeats = $kn->pivot->open_seats;
                            $takenSeats = $currAttempts->count();
                            $remSeats   = (int)$totalSeats - (int)$takenSeats;
                            
                            // no remaining seats..
                            if ($remSeats < $requestedSeats) {
                                $allKnowOK = false;
                            }

                            // no active testforms..
                            if ($kn->active_testforms->isEmpty()) {
                                $allKnowOK = false;
                            }
                        }
                    }

                    // this event contains all requested exams and has seats available!
                    // eligible event!
                    if ($allSkillsOK && $allKnowOK) {
                        $eligibleEvts[] = $event;
                    }
                }
            }
        }

        // Check for conflict of interest on eligibleEvts[]
        // Observer cannot also have been an instructor of the student
        $allinstructors = Instructor::all()->lists('user_id', 'id')->all();
        $eligible = [];
        foreach ($eligibleEvts as $evt) {
            $observer = \Observer::find($evt->observer_id);
            
            foreach ($this->trainings as $training) {
                if ($allinstructors[$training->pivot->instructor_id] == $observer->user_id) {
                    continue 2;
                }
            }

            $eligible[] = $evt;
        }

        if (empty($eligible)) {
            $eligible = $eligibleEvts;
        }

        return new Collection($eligible);
    }


    /**
     * Gets all available status' for a training in current state
     * ie. Passed might be named Complete in certain states
     */
    public function getTrainingStatusAttribute()
    {
        $status = ['attending', 'passed', 'failed'];
        $opts   = [];

        foreach ($status as $s) {
            $opts[$s] = Lang::get('core::training.status_'.$s);
        }

        return $opts;
    }

    /**
     * Gets all available failed training reasons in assoc array
     */
    public function getTrainingFailedReasonsAttribute()
    {
        $reasons = Lang::get('core::training.reasons');
        $opts = [];

        foreach ($reasons as $i => $r) {
            $opts[$r] = $r;
        }

        return $opts;
    }

    /**
     * Gets a collection of all Exams (knowledge) this student is eligible to take
     * Uses ineligibleExams() to determine which are eligible
     */
    public function getEligibleExams()
    {
        $ineligible = $this->ineligibleExams();

        if (! $ineligible->isEmpty()) {
            return Exam::whereNotIn('id', $ineligible->lists('id')->all())->get();
        }

        return Exam::all();
    }

    /**
     * Gets a collection of all Skillexams this student is eligible to take
     * Uses ineligibleSkills() to determine which are eligible
     */
    public function getEligibleSkills()
    {
        $ineligible = $this->ineligibleSkills();

        if (! $ineligible->isEmpty()) {
            return Skillexam::whereNotIn('id', $ineligible->lists('id')->all())->get();
        }

        return Skillexam::all();
    }


    /**
     * Finds all knowledge testform ids this student could take
     */
    public function getTestformPool($examId)
    {
        $exam = Exam::with('active_testforms')->find($examId);

        // if no active testforms for the exam were found, error
        if ($exam->active_testforms->isEmpty()) {
            return [];
        }

        $testformPool = $exam->active_testforms;

        // oral schedule student?
        if ($this->is_oral) {
            // filter testforms down to only oral testforms
            $testformPool = $testformPool->filter(function ($form) {
                return $form->getOriginal('oral');
            });
        }

        // still remaining available testforms?
        if ($testformPool->isEmpty()) {
            return [];
        }

        // get all testforms (active in this state)
        $testformIds = $testformPool->lists('id')->all();
        // randomize the order
        shuffle($testformIds);

        // look up failed attempts (if any exist)
        $failedTestformIds = $this->failedAttempts()->get()->lists('testform_id')->all();

        // if no failed history, all skilltests are available
        if (empty($failedTestformIds)) {
            return $testformIds;
        }

        // remove all previously failed testform ids
        $avTestformIds = array_diff($testformIds, $failedTestformIds);

        return $avTestformIds;
    }

    /**
     * Finds all skilltest ids (for a skillexam) this student could take
     */
    public function getSkilltestPool($skillexamId)
    {
        // get active skilltests for exam
        $skillexam = Skillexam::with('active_tests.tasks')->find($skillexamId);

        // if no active skilltests for the exam were found, error
        if ($skillexam->active_tests->isEmpty()) {
            return false;
        }

        // pool of eligible skilltests
        $pool = $skillexam->active_tests;

        // failed history? 
        // remove non-eligible skilltests
        if (! $this->failedSkillAttempts->isEmpty()) {
            // get all failed tasks
            $failedTaskIds = $this->failedSkillResponses->lists('skilltask_id')->all();
            $failedTaskIds = ! empty($failedTaskIds) ? array_unique($failedTaskIds) : [];

            // get failed skilltest ids
            $failedSkilltestIds = $this->failedSkillAttempts->lists('skilltest_id')->all();

            // remove all previous failed test ids from pool of available test ids
            $pool = $pool->filter(function ($skilltest) use ($failedSkilltestIds) {
                return ! in_array($skilltest->id, $failedSkilltestIds);
            });

            // remove eligible skilltests that dont contain at least ONE of the previously failed task ids
            $pool = $pool->filter(function ($skilltest) use ($failedTaskIds) {
                return array_intersect($failedTaskIds, $skilltest->tasks->lists('id')->all());
            });
        }

        return $pool->lists('id')->all();
    }


    /**
     * Validation for a student
     * @param  array
     * @return boolean
     */
    public function validate($ignoreUserId=null)
    {
        $rules = $this->rules;

        // set custom error messages
        $messages = ['proper_ssn'        => 'Invalid SSN.',
                     'reverse_match'    => 'Reverse SSN is incorrect.',
                     'ssn.required'        => 'The SSN field is required.',
                     'rev_ssn.required' => 'The Reverse SSN field is required.'];

        // updating student
        if (is_numeric($ignoreUserId)) {
            $student = Student::where('user_id', $ignoreUserId)->first();

            // ensure unique username/email
            $rules['username'] = 'sometimes|min:4|unique:users,username,'.$ignoreUserId;
            $rules['email']    = 'email|unique:users,email,'.$ignoreUserId;

            // ssn rules for updating a student
            unset($rules['rev_ssn']);
            $rules['ssn'] = 'sometimes|alpha_dash|proper_ssn|unique_ssn:'.$student->id;

            // pwd not required when updating
            $rules['password']              = 'min:8|confirmed';
            $rules['password_confirmation'] = 'min:8';
        }
        // creating student
        // validate initial training too
        else {
            $initTraining = new StudentTraining;

            // set initial training rules
            $rules['discipline_id']     = 'required|not_in:0';
            $rules['facility_id']       = 'required|not_in:0';
            $rules['training_id']       = 'required|not_in:0';
            $rules['instructor_id']     = 'required|not_in:0';
            $rules['started']           = 'required|date';
            $rules['ended']             = 'required_if:status,passed|start_before_end';
            $rules['classroom_hours']   = 'numeric';
            $rules['distance_hours']    = 'numeric';
            $rules['lab_hours']         = 'numeric';
            $rules['traineeship_hours'] = 'numeric';
            $rules['clinical_hours']    = 'numeric';

            // conditionally append necessary hours requirements 
            // depends on initial training status
            $rules = $initTraining->appendHoursRules($rules);

            // set initial training custom messages
            $messages['discipline_id.required'] = 'Initial Training Discipline must be set.';
            $messages['discipline_id.not_in']   = 'Initial Training Discipline must be set.';
            $messages['facility_id.required']   = 'Initial Training Program must be set.';
            $messages['facility_id.not_in']     = 'Initial Training Program must be set.';
            $messages['training_id.required']   = 'Initial Training Type must be set.';
            $messages['training_id.not_in']     = 'Initial Training Type must be set.';
            $messages['instructor_id.required'] = 'Initial Training '.Lang::choice('core::terms.instructor', 1).' must be set.';
            $messages['instructor_id.not_in']   = 'Initial Training '.Lang::choice('core::terms.instructor', 1).' must be set.';
            $messages['started.required']       = 'Initial Training Start Date must be set.';
            $messages['started.date']           = 'Initial Training Start Date is invalid.';
            $messages['ended.required_if']      = 'Initial Training End Date must be set on Passed Training.';
            $messages['ended.start_before_end'] = 'Initial Training End Date must come after Start Date.';

            $messages['classroom_hours.numeric']   = 'Initial Training Classroom Hours must be numeric.';
            $messages['distance_hours.numeric']    = 'Initial Training Distance Hours must be numeric.';
            $messages['lab_hours.numeric']         = 'Initial Training Lab Hours must be numeric.';
            $messages['traineeship_hours.numeric'] = 'Initial Training Traineeship Hours must be numeric.';
            $messages['clinical_hours.numeric']    = 'Initial Training Clinical Hours must be numeric.';
        }

        $v = Validator::make(Input::get(), $rules, $messages);
        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }

    public function validateChangePassword()
    {
        // only need change pwd rules
        $rules = array_intersect_key($this->rules, array_flip(['password', 'password_confirmation']));

        $v = Validator::make(Input::get(), $rules);
        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }

    /**
     * Determines whether or not a student exists with this ssn, returns the record if so
     * @param  string $ssn 
     * @return mixed      
     */
    public function findBySocial($ssn)
    {
        return Student::where('ssn_hash', '=', saltedHash($ssn))->first();
    }

    /**
     * Generate fake student
     */
    public function populate()
    {
        $faker = \Faker\Factory::create();
        
        $user    = new User;
        $gender  = rand(0, 1) ? 'Male' : 'Female';
        $pwd     = 'testing123';
        $email   = $user->getFakeEmail('student');
        $ssn     = $this->getFakeSSN();
        $strip   = [' ', ')', '(', 'x', '-', '+', '.'];
        $phone   = Formatter::format_phone(substr(str_replace($strip, '', $faker->phoneNumber), 0, 10));
        $zip     = substr(str_replace($strip, '', $faker->postcode), 0, 5);

        $info = [
            'ssn'       => $ssn,
            'first'     => $faker->firstName($gender),
            'last'      => $faker->lastName,
            'birthdate' => date('m/d/Y', strtotime($faker->date('Y-m-d', '-18 years'))),
            'address'   => $faker->streetAddress,
            'city'      => $faker->city,
            'state'     => Config::get('core.client.abbrev'),
            'zip'       => $zip,
            'gender'    => $gender,
            'email'     => $email,
            'password'  => $pwd,
            'phone'     => $phone,
            'unlisted'  => $faker->boolean(),
            'oral'      => $faker->boolean(20) ? 1 : 0
        ];

        // all disciplines with trainings/programs
        $disciplines = Discipline::with('trainingPrograms', 'training')->get()->shuffle();
        
        foreach ($disciplines as $discipline) {
            // get all trainings the student would be eligible for
            // (under this discipline)
            $trainings = $this->availableTrainings($discipline->id);

            if ($trainings->isEmpty() || $discipline->trainingPrograms->isEmpty()) {
                continue;
            }

            $info['discipline_id'] = $discipline->id;

            // add available training programs under discipline
            $facility                     = $discipline->trainingPrograms->random(1);
            $info['facility_id']          = $facility->id;
            $info['available_facilities'] = $discipline->trainingPrograms->lists('id', 'name')->all();

            // add available trainings under discipline
            $training                    = $trainings->random(1);
            $info['training_id']         = $training->id;
            $info['available_trainings'] = $trainings->lists('id', 'name')->all();

            // get facility so we can find available instructors for discipline/facility/training combo
            $facility = Facility::with([
                'activeInstructors' => function ($query) use ($discipline) {
                    $query->wherePivot('discipline_id', $discipline->id)->orderBy('last', 'ASC');
                },
                'activeInstructors.teaching_trainings' => function ($query) use ($training) {
                    $query->wherePivot('training_id', $training->id);
                }
            ])->find($facility->id);

            // if there were any instructors matching our params (discipline/facility/training)
            if (! $facility->activeInstructors->isEmpty()) {
                // add available instructors
                $instructor                    = $facility->activeInstructors->random(1);
                $info['instructor_id']         = $instructor->id;
                $info['available_instructors'] = $facility->activeInstructors->lists('id', 'full_name')->all();

                break;
            }
        }

        // set an initial training start date (today)
        $info['training_started'] = date('m/d/Y');
    
        return $info;
    }

    /**
     * Deactivates any existing instructors and sets a new one active. Expects a model object to be initialized
     * @param string $value [description]
     */
    public function setCurrentInstructor($instructorId)
    {
        DB::table('instructor_student')->where('student_id', '=', $this->id)
            ->update(['active' => false]);

        $this->instructors()->attach($instructorId, ['active' => true]);
    }

    /**
     * Checks if a Student is using a fake SSN
     * If first digit of SSN is F, this is fake
     */
    public function hasFakeSSN()
    {
        return substr($this->really_plain_ssn, 0, 3) == '999';
    }

    /**
     * Generates a unique fake SSN
     * Uses first character as F to denote fake ssn
     */
    public function getFakeSSN()
    {
        $foundSSN = '';

        while (! $foundSSN) {
            // generate 5 fake ssns
            $ssns = $this->generateFakeSSN(5);

            // get any matching (already used) ssns in database
            $students = Student::whereIn('ssn', $ssns)->get();

            // diff to get available (not used) ssns
            $available = array_diff($ssns, $students->lists('ssn_hash')->all());

            if (! empty($available)) {
                // non-hashed ssn is key
                $foundSSN = (string) array_rand($available);
            }
        }

        return $foundSSN;
    }

    /**
     * Generates X amount of fake unique socials using $base as the prefix
     * (always returns 9 digits)
     */
    protected function generateFakeSSN($numSSN=1, $prefix='999')
    {
        $ssns = [];

        // cant create ssn larger than 9 digits
        if (strlen($prefix) < 9) {
            for ($i = 0; $i < $numSSN; $i++) {
                $suffixLength = 9 - strlen($prefix);
                $suffixMax    = str_pad("9", $suffixLength, "9", STR_PAD_LEFT);
                $suffix       = rand(0, $suffixMax);
                
                // prefix + suffix padded
                $fakeSSN = $prefix.str_pad($suffix, $suffixLength, 0, STR_PAD_LEFT);

                // add hashed as value
                $ssns[$fakeSSN] = saltedHash($fakeSSN);
            }
        }

        return $ssns;
    }

    /**
     * Adds a new student from the add form
     */
    public function addWithInput()
    {
        $first      = Input::get('first');
        $last       = Input::get('last');
        $ssn        = str_replace(['-', '_', ' '], '', Input::get('ssn'));    // strip - _ and space
        $email      = Input::get('email');
        $loggedUser = Auth::user();
        $comments   = Input::get('comments') ? Input::get('comments') : null;
        $altPhone   = Input::get('alt_phone') ? Input::get('alt_phone') : null;
        $middle     = Input::get('middle') ? Input::get('middle') : null;

        $demographics = [
            'first'       => $first,
            'middle'      => $middle,
            'last'        => $last,
            'birthdate'   => Input::get('birthdate'),
            'address'     => Input::get('address'),
            'city'        => Input::get('city'),
            'state'       => Input::get('state'),
            'zip'         => Input::get('zip'),
            'comments'    => $comments,
            'phone'       => Input::get('phone'),
            'alt_phone'   => $altPhone,
            'gender'      => Input::get('gender'),
            'is_unlisted' => Input::get('is_unlisted', false),
            'is_oral'     => Input::get('is_oral', false)
        ];

        // Create a new user
        $user                        = new User;
        $username                    = $user->unique_username($last, $first);
        $user->email                 = $email;
        $user->username              = $username;
        $userPwd                     = Input::get('password');
        $user->password              = $userPwd;
        $user->password_confirmation = $userPwd;
        $user->confirmed             = 1;
        $user->save();

        // make sure user created above
        if ($user->id !== null) {
            $role = Role::where('name', '=', 'Student')->first();
            $user->attachRole($role);

            $otherInfo = [
                'user_id'      => $user->id,
                'creator_id'   => $loggedUser->userable_id,        // relation for student's creator
                'creator_type' => $loggedUser->userable_type,
                'ssn'          => Crypt::encrypt($ssn),
                'ssn_hash'     => saltedHash($ssn)
            ];

            // All of this new student's info
            $newStudent = array_merge($demographics, $otherInfo);

            // Create a new student
            $saved = Student::create($newStudent);

            if ($saved !== null) {
                // polymorphic user relation for auth
                $user->userable_id   = $saved->id;
                $user->userable_type = $saved->getMorphClass();
                $user->save();

                // add initial training
                $initialTraining = new StudentTraining();
                $initialTraining->addWithInput($saved->id);

                // Send email notification if email defined
                $this->notifyAccountCreated($saved, $userPwd);

                // Log this created student
                $u = Auth::user();
                $name = is_null($u) ? '' : ' by ' . $u->userable->fullName;
                \Log::info(Lang::choice('core::terms.student', 1) . ' #' . $saved->id . ' - ' . $saved->fullName . ' created' . $name . '.');
            }

            return $saved->id;
        }
        
        return false;
    }

    /**
     * Unschedules (remove & reschedule) a student from an Event Knowledge Exam
     */
    public function unscheduleKnowledge($eventId, $examId)
    {
        $exam = Exam::with('corequired_skills')->find($examId);

        // find the scheduled testattempt record
        $attempt = $this->attempts()
                        ->where('testattempts.status', 'assigned')
                        ->where('testevent_id', $eventId)
                        ->where('exam_id', $examId)
                        ->first();
        if (is_null($attempt)) {
            return false;
        }

        // reschedule main requested knowledge exam
        $this->reschedule($attempt, true);
        
        // get all student attempts in this event
        $schedKnowledgeAttempts = Testattempt::where('student_id', $this->id)->where('testevent_id', $eventId)->where('testattempts.status', 'assigned')->get();
        $schedSkillAttempts     = Skillattempt::where('student_id', $this->id)->where('testevent_id', $eventId)->where('skillattempts.status', 'assigned')->get();

        // flash warning to user to check if student should be manually rescheduled out of any other exams?
        $showWarning = false;

        // check for any corequired skill exams that will need to be rescheduled as well
        if (! $schedSkillAttempts->isEmpty()) {
            foreach ($schedSkillAttempts as $att) {
                // corequirement of the originally unscheduled exam?
                if (in_array($att->skillexam_id, $exam->corequired_skills->lists('id')->all())) {
                    $this->reschedule($att, true);
                } else {
                    $showWarning = true;
                }
            }
        }

        // if student is scheduled for other skillexams that are not a corequirement
        // or one of the other scheduled knowledge exams wasnt a corequirement
        if (! $schedKnowledgeAttempts->isEmpty() || $showWarning) {
            Flash::warning('Student has other tests scheduled in Event #' . $attempt->testevent_id . ' that are not co-required. These attempts have not been re-scheduled. Please re-schedule this test if necessary.');
        }

        return true;
    }

    /**
     * Unschedules (remove & reschedule) a student from an Event Skill Exam
     */
    public function unscheduleSkill($eventId, $skillexamId)
    {
        $skill = Skillexam::with('corequired_exams')->find($skillexamId);

        // find the scheduled skillattempt record
        $attempt = $this->skillAttempts()
                        ->where('skillattempts.status', 'assigned')
                        ->where('testevent_id', $eventId)
                        ->where('skillexam_id', $skillexamId)
                        ->first();
        if (is_null($attempt)) {
            return false;
        }

        // reschedule main requested skillexam
        $this->reschedule($attempt, true);

        // get all student attempts in this event
        $schedKnowledgeAttempts = Testattempt::where('student_id', $this->id)->where('testevent_id', $eventId)->where('testattempts.status', 'assigned')->get();
        $schedSkillAttempts     = Skillattempt::where('student_id', $this->id)->where('testevent_id', $eventId)->where('skillattempts.status', 'assigned')->get();

        // flash warning to user to check if student should be manually rescheduled out of any other exams?
        $showWarning = false;

        // check for any corequired knowledge exams that will need to be rescheduled as well
        if (! $schedKnowledgeAttempts->isEmpty()) {
            foreach ($schedKnowledgeAttempts as $att) {
                // corequirement of the originally unscheduled exam?
                if (in_array($att->exam_id, $skill->corequired_exams->lists('id')->all())) {
                    $this->reschedule($att, true);
                } else {
                    $showWarning = true;
                }
            }
        }

        // if student is scheduled for other skillexams that are not a corequirement
        // or one of the other scheduled knowledge exams wasnt a corequirement
        if (! $schedSkillAttempts->isEmpty() || $showWarning) {
            Flash::warning('Student has other tests scheduled in Event #' . $attempt->testevent_id . ' that are not co-required. These attempts have not been re-scheduled. Please re-schedule this test if necessary.');
        }

        return true;
    }

    /**
     * Sets an attempt to reschedule status and optional notify student 
     */
    public function reschedule($attempt, $notify = false)
    {
        $attempt->status = 'rescheduled';
        $attempt->save();

        // knowledge or skill exam?
        $exam = isset($attempt->exam_id) ? Exam::find($attempt->exam_id) : Skillexam::find($attempt->skillexam_id);

        if ($notify === true) {
            $event = Testevent::with('observer')->find($attempt->testevent_id);
            $reflect = new \ReflectionClass($attempt);

            // Send a notification to the student
            if ($reflect->getShortName() == 'Testattempt') {
                $this->user->notify()
                    ->withType('info')
                    ->withSubject('Removed from Test Event')
                    ->withBody(View::make('core::students.notifications.unscheduled_knowledge')->with([
                            'exam'        => Exam::find($attempt->exam_id),
                            'event'        => $event
                           ]))
                    ->deliver();
            } elseif ($reflect->getShortName() == 'Skillattempt') {
                $this->user->notify()
                    ->withType('info')
                    ->withSubject('Removed from Test Event')
                    ->withBody(View::make('core::students.notifications.unscheduled_skill')->with([
                            'skill' => Skillexam::find($attempt->skillexam_id),
                            'event' => $event
                           ]))
                    ->deliver();
            }

            // Email the test observer
            $event->notifyObserverOfReschedule($attempt->student);
        }

        Flash::success('Re-scheduled '.Lang::choice('core::terms.student', 1).' '.$this->fullname.' from Exam '.$exam->name.'.');
    }

    /**
     * Attempts to update with info from the update form
     */
    public function updateWithInput()
    {
        // Update user attached to student
        $user           = User::find($this->user_id);
        $user->username = Input::get('username');
        $user->email    = Input::get('email');
        $user->save();

        // perform actual password reset
        $user->resetPassword();

        $info = [
            'first'       => Input::get('first'),
            'middle'      => Input::get('middle'),
            'last'        => Input::get('last'),
            'birthdate'   => Input::get('birthdate') ? date('Y-m-d', strtotime(Input::get('birthdate'))) : null,
            'phone'       => Input::get('phone'),
            'is_unlisted' => Input::get('is_unlisted'),
            'is_oral'     => Input::get('is_oral', false),
            'alt_phone'   => Input::get('alt_phone'),
            'address'     => Input::get('address'),
            'city'        => Input::get('city'),
            'state'       => Input::get('state'),
            'zip'         => Input::get('zip'),
            'comments'    => Input::get('comments'),
            'gender'      => Input::get('gender')
        ];

        // ssn included in update?
        if (Input::get('ssn')) {
            $ssn = str_replace(['-', '_', ' '], '', Input::get('ssn'));    // strip - _ and space

            $info['ssn']      = Crypt::encrypt($ssn);
            $info['ssn_hash'] = saltedHash($ssn);
        }

        // Attaching any files?
        if (Input::hasFile('media')) {
            $info['media'] = Input::file('media');
        }

        $this->fill($info);

        // Update student
        $saved = $this->save();
        if ($saved) {
            // Log this created student
            $u = Auth::user();
            $name = is_null($u) ? '' : ' by ' . $u->userable->fullName;
            \Log::info(Lang::choice('core::terms.student', 1) . ' #' . $this->id . ' - ' . $this->fullName . ' updated' . $name . '.');
        }

        return $saved;
    }

    /**
     * Person updating their own record with a form
     */
    public function updateSelf()
    {
        // Update user attached to student
        $user           = User::find($this->user_id);
        $user->email    = Input::get('email');
        $user->username = Input::get('username');
        $user->save();

        // perform actual password reset
        $user->resetPassword();

        // Update student
        return $this->update([
            'birthdate'   => Input::get('birthdate'),
            'phone'       => Input::get('phone'),
            'alt_phone'   => Input::get('alt_phone'),
            'is_unlisted' => Input::get('is_unlisted'),
            'address'     => Input::get('address'),
            'city'        => Input::get('city'),
            'state'       => Input::get('state'),
            'zip'         => Input::get('zip'),
            'gender'      => Input::get('gender')
        ]);
    }

    /**
     * Gets a student's phone number (if any)
     */
    public function getMainPhoneAttribute()
    {
        $phone = $this->phone ? $this->phone : null;

        // didn't find a home phone, use alternate
        if (! $phone) {
            $phone = $this->alt_phone;
        }

        return $phone;
    }

    /**
     * Return unencrypted dash separated ssn
     */
    public function getPlainSsnAttribute()
    {
        $ssn = Crypt::decrypt($this->ssn);
        return substr($ssn, 0, 3).'-'.substr($ssn, 3, 2).'-'.substr($ssn, 5, 4);
    }

    public function getReallyPlainSsnAttribute()
    {
        return Crypt::decrypt($this->ssn);
    }

    public function getActivatedAtAttribute($value)
    {
        return isset($value) ? date('m/d/Y H:i A', strtotime($value)) : '';
    }

    public function getDeactivatedAtAttribute($value)
    {
        return isset($value) ? date('m/d/Y H:i A', strtotime($value)) : '';
    }

    /**
     * Convert 10 digit TestID to SSN
     * OLD REGISTRY FUNCTION, NEEDED TO STUDENT IMPORT
     */
    public static function DecryptTestID($id)
    {
        $retval = 0;
        $base   = 1;
        $trace  = "";
        $error  = false;

        for ($i=0; $i<strlen($id); $i++) {
            $trace  .= ord($id[$i])-48;
            $retval += $base*(ord($id[$i])-48);
            $base   *= 9;
        }

        if ($error) {
            $retval=-1;
        }
        
        // ensure always length 9, pad with zeroes if necessary
        $pRetval=str_pad($retval, 9, "0", STR_PAD_LEFT);
 
        return $pRetval;
    }

    /**
     * Convert SSN to 10 digit TestID
     */
    public static function EncryptTestID($id, $withticks)
    {
        $retval = "";
        $base   = 1;
        $trace  = $id.".";

        do {
            $retval .= chr(48 + ($id % 9));

            if ($withticks && (strlen($retval)==4 || strlen($retval)==8)) {
                $retval.="-";
            }
    
            $id     = floor($id/9);
            $trace .= $id.".";
            $base  *= 9;
        } while ($id>0);

        while (strlen($retval)<10) {
            $retval .= "0";
        }
  
        return $retval;
    }
}
