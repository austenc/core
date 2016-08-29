<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Lang;
use Input;
use Config;
use Session;
use Redirect;
use Response;
use Request;
use DB;
use URL;
use Formatter;
use Illuminate\Support\Collection as Collection;
use Hdmaster\Core\Notifications\Flash;
use \Student;
use \User;
use \Ada;
use \StudentTraining;
use \Training;
use \Exam;
use \Testattempt;
use \Testevent;
use \Instructor;
use \Facility;
use \Skilltest;
use \Skillexam;
use \Skillattempt;
use \Certification;
use \Discipline;
use \CertPdf;
use \Sorter;

class StudentsController extends BaseController
{

    protected $student;
    protected $user;
    protected $training;
    protected $student_training;

    public function __construct(Student $student, User $user, StudentTraining $student_training, Training $training)
    {
        $this->student          = $student;
        $this->user             = $user;
        $this->training         = $training;
        $this->student_training = $student_training;

        // Only allow instructors to edit their own students
        $this->beforeFilter('editOwnStudent', ['only' => 'edit']);
        $this->beforeFilter('check-archived', ['only' => 'edit']);
        $this->beforeFilter('check-active', ['only' => 'archived']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $loggedUser = Auth::user();

        // check facility has appropriate Training action
        if ($loggedUser->isRole('Facility')) {
            $facActions = $loggedUser->userable->actions;

            if (! $facActions || (is_array($facActions) && ! in_array('Training', $facActions))) {
                return Redirect::route('account')->withDanger('You are not a Training approved '.Lang::choice('core::terms.facility', 1).'.');
            }
        }

        $params                  = $this->student->handleSearch();
        $params['searchTypes']   = Session::get('students.search.types');
        $params['searchQueries'] = Session::get('students.search.queries');
        $params['filter']        = Session::get('students.search.filter');

        return View::make('core::students.index')->with($params);
    }

    /**
     * Perform advanced search on students
     *
     * @return Response
     */
    public function search()
    {
        // get search parameters and save them to the session
        $query = Input::get('search');
        $type  = Input::get('search_type');

        // unless we are adding a search to this via the form, reset the params on each search
        if (! Input::get('add_search')) {
            Session::forget('students.search.types');
            Session::forget('students.search.queries');
        }

        // Add search query and type to session to track it
        if (! empty($query) && ! empty($type)) {
            // Push type and search terms to session
            Session::push('students.search.types', $type);
            Session::push('students.search.queries', $query);
        }

        return Redirect::route('students.index');
    }

    /**
     * Perform mass actions on a student (POST-only)
     *
     * @return Response
     */
    public function mass()
    {
        // Make sure there's something checked to work with
        $ids = Input::get('student_ids');

        if (! empty($ids)) {
            // boxes checked, perform action / show view according to action type
            $action = Input::get('action_type');

            switch ($action) {
                case 'change_owner':
                    $students    = Student::whereIn('id', $ids)->get();
                    $instructors = Instructor::orderBy('last')->get();

                    return View::make('core::students.change_owner')->with([
                        'students'    => $students,
                        'studentIds'  => $students->lists('id')->all(),
                        'instructors' => $instructors->lists('full_name', 'id')->all()
                    ]);
                break;

                case 'print-certificates':

                    // get the latest passed student trainings for this instructor, with student
                    $trainings = StudentTraining::with('student', 'facility')
                        ->where('status', 'passed')
                        ->whereIn('student_id', $ids)
                        ->where('instructor_id', Auth::user()->userable_id)
                        ->orderBy('ended', 'desc')
                        ->groupBy('student_id');

                    // filter by discipline if logged in as a single one
                    if (Session::has('discipline')) {
                        $trainings->where('discipline_id', Session::get('discipline.id'));
                    }

                    // grab all the matching students
                    $trainings = $trainings->get();

                    if ($trainings->isEmpty()) {
                        Flash::warning('No printable certificates for selected students.');
                        return Redirect::route('students.index');
                    }

                    // construct new PDF document (P/L,pt/mm/cm/in
                    $pdf = new CertPdf;

                    // call pdf render for each student
                    foreach ($trainings as $t) {
                        $pdf->certificate($t->student, $t, $t->facility);
                    }

                    // render the final result
                    return $pdf->show();
                break;

                case 'print-roster':
                    // Is the logged in person an instructor?
                    if (Auth::user() && ! Auth::user()->isRole('Instructor')) {
                        Flash::warning('You must be an instructor to use this feature.');
                        return Redirect::route('students.index');
                    }

                    // Do we have an instructor with a logged-in discipline?
                    $discipline = Session::get('discipline');
                    if (empty($discipline)) {
                        Flash::warning('Not logged in to a discipline.');
                        return Redirect::route('students.index');
                    }
                    $facilityId = Session::get('discipline.program.id');

                    // grab all the applicable students based on the selected id's
                    $students = Student::with(['trainings' => function ($query) use ($facilityId) {
                        $query->where('instructor_id', Auth::user()->userable_id);
                        $query->where('facility_id', $facilityId);
                        $query->orderBy('started', 'desc');
                    }])->wherein('id', $ids)->get();

                    // did we find any students to put on the roster?
                    if ($students->isEmpty()) {
                        Flash::warning('No eligible students found for printing roster.');
                        return Redirect::route('students.index');
                    }

                    return View::make('core::trainings.roster')->with([
                        'students'   => $students,
                        'discipline' => $discipline,
                        'instructor' => Auth::user()->userable
                    ]);

                break;

                default:
                    // do nothing for now
            }
        } else {
            Flash::danger('You must select at least one person to perform that action.');
        }

        return Redirect::route('students.index');
    }

    /**
     * Change owner for a single student
     * usually coming from students.edit
     */
    public function changeSingleOwner($id)
    {
        $student = Student::where('id', $id)->get();

        if ($student->isEmpty()) {
            return Redirect::route('students.index');
        }

        $instructors = Instructor::orderBy('last')->get();

        return View::make('core::students.change_owner')->with([
            'students'    => $student,
            'studentIds'  => $student->lists('id')->all(),
            'instructors' => $instructors->lists('full_name', 'id')->all(),
            'single'      => true
        ]);
    }

    /**
     * Change password for a single student
     * uses popup to allow change password without access to students.edit
     * Useful for observer changing student pwd right before test, via events.edit
     */
    public function changePassword($id)
    {
        $student = Student::find($id);

        if (is_null($student)) {
            return Redirect::to('/');
        }

        if (Request::isMethod('post')) {
            $studentId = Input::get('student_id');
            $student   = Student::find($studentId);

            if ($this->student->validateChangePassword()) {
                $student->user->resetPassword();
                Flash::success('Updated Student '.$student->full_name.'\'s password');
            } else {
                Flash::danger('Failed to update Student '.$student->full_name.'\'s password<br><br>'.implode('<br>', $this->student->errors->all()));
            }

            
            if (URL::previous() == URL::current()) {
                return Redirect::route('events.index');
            }
            
            return Redirect::back();
        }

        return View::make('core::students.modals.change_password')->with([
            'student' => $student
        ]);
    }

    /**
     * Change the owner post handler method
     *
     * @return Response
     */
    public function changeOwner()
    {
        $ids   = Input::get('student_ids');
        $newId = Input::get('new_owner');

        // did the request come from students.index or students.edit?
        // bring them back to students.edit if they were working with a particular student
        $redirect = Redirect::route('students.index');
        if (Input::get('single')) {
            $redirect = Redirect::route('students.edit', $ids[0]);
        }
        
        if ($ids !== null && $newId !== null) {
            // Set any other active instructors for these students to false
            $update = DB::table('instructor_student')->whereIn('student_id', $ids)->update([
                'active'   => false
            ]);

            // Update or insert any records we need to!
            if ($update !== false) {
                // build multidimensional array where student_id
                $toSync = array();

                // grab the instructor students
                $instructor = Instructor::with('students')->find($newId);

                // combine the selected student id's with the instructor's exsiting students
                $ids = array_merge($ids, $instructor->students->lists('id')->all());
                $ids = array_unique(array_map('intval', $ids));

                foreach ($ids as $s) {
                    $toSync[$s] = ['active' => true];
                }

                // Sync the instructor / student records
                $instructor->students()->sync($toSync);

                Flash::success('Owner changed successfully for selected records.');
            } else {
                Flash::error('Error updating ownership -- please contact developers!');
            }
        } else {
            Flash::danger('You must select at least one person to perform that action.');
        }

        return $redirect;
    }

    /**
     * Delete a given search index out of the session
     *
     * @return Response
     */
    public function searchDelete($index)
    {
        Session::forget('students.search.types.'.$index);
        Session::forget('students.search.queries.'.$index);
        Flash::info('Search type removed.');

        return Redirect::route('students.index');
    }

    /**
     * Clears any student search vars in the session
     *
     * @return Response
     */
    public function searchClear()
    {
        Session::forget('students.search.types');
        Session::forget('students.search.queries');
        Session::forget('students.search.filter');
        Flash::info('Search cleared.');

        return Redirect::route('students.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($disciplineId='', $trainingId='', $programId='')
    {
        $programs    = [];
        $trainings   = [];
        $instructors = [];

        // if invalid params send back to students main page
        // if sending back to students.create it will infinite loop
        $redirect = Redirect::route('students.index');

        // program must be training approved
        if (Session::has('discipline.program.training_approved') && Session::get('discipline.program.training_approved') !== true) {
            Flash::danger('You are logged in at a '.Lang::choice('core::terms.facility_training', 1).' that is <strong>not Training approved</strong>. '.Lang::choice('core::terms.student', 1).' creation is disabled.');
            return Redirect::route('students.index');
        }

        // discipline
        if (Session::has('discipline.id')) {
            $disciplineId = Session::get('discipline.id');
        } elseif (Input::old('discipline_id')) {
            $disciplineId = Input::old('discipline_id');
        }

        // program
        if (Session::has('discipline.program.id')) {
            $programId = Session::get('discipline.program.id');
        } elseif (Input::old('facility_id')) {
            $programId = Input::old('facility_id');
        }

        // training
        if (Input::old('training_id')) {
            $trainingId = Input::old('training_id');
        }

        // if discipline is set
        if (! empty($disciplineId)) {
            $discipline = Discipline::with([
                'training',
                'trainingPrograms',
                'agencyTrainingPrograms'
            ])->find($disciplineId);

            // check this is a legit discipline
            if (is_null($discipline)) {
                return $redirect;
            }

            // check passed in training exists within discipline
            if (! empty($trainingId) && ! in_array($trainingId, $discipline->training->lists('id')->all())) {
                return $redirect;
            }

            // check passed in program exists within discipline
            if (! empty($programId) && ! in_array($programId, $discipline->trainingPrograms->lists('id')->all())) {
                return $redirect;
            }

            // all trainings within current discipline student would be eligible for
            $studentEligibleTrainings = $this->student->availableTrainings($disciplineId);
            $trainings = $studentEligibleTrainings->lists('name', 'id')->all();

            // all programs within current discipline
            $programs = $discipline->trainingPrograms->lists('name', 'id')->all();

            // INSTRUCTORS
            // determine available trainings
            if (Auth::user()->isRole('Instructor')) {
                // intersect trainings this instructor teaches against trainings within discipline
                $instructorTrainingIds   = Auth::user()->userable->teaching_trainings()->lists('id')->all();
                $avInstructorTrainingIds = array_intersect($instructorTrainingIds, $discipline->training->lists('id')->all());

                // instructor teaches no trainings under the current discipline!
                if (empty($avInstructorTrainingIds)) {
                    $trainings = [];
                } else {
                    // now intersect students trainings with instructor eligible trainings
                    $studentInstructorEligibleTrainingIds = array_intersect($studentEligibleTrainings->lists('id')->all(), $avInstructorTrainingIds);

                    if (! empty($studentInstructorEligibleTrainingIds)) {
                        $trainings = Training::whereIn('id', $studentInstructorEligibleTrainingIds)->get()->lists('name', 'id')->all();
                    } else {
                        // no trainings eligible for both logged in instructor (teaching trainings) 
                    // and new student under this discipline
                        $trainings = [];
                    }
                }

                // clear programs, instructor will have this selected on login
                $programs = [];
            } elseif (Auth::user()->ability(['Admin', 'Staff'], [])) {

            // ADMIN/STAFF
            // determine available instructors
                // need both training and program selected to populate instructors
                if (! empty($programId) && ! empty($trainingId)) {
                    // get program
                    $facility = Facility::with([
                        'activeInstructors' => function ($query) use ($disciplineId) {
                            $query->wherePivot('discipline_id', $disciplineId)->orderBy('last', 'ASC');
                        },
                        'activeInstructors.teaching_trainings' => function ($query) use ($trainingId) {
                            $query->wherePivot('training_id', $trainingId);
                        }
                    ])->find($programId);

                    // get all instructors at program
                    $instructors = $facility->activeInstructors->lists('full_name', 'id')->all();
                }
            } elseif (Auth::user()->isRole('Agency')) {
                // AGENCY
            // determine available training programs
                $programs = $discipline->agencyTrainingPrograms->lists('name', 'id')->all();
            }
        }

        return View::make('core::students.create')->with([
            'trStatusOpts'    => $this->student->training_status,
            'trFailReasons'   => $this->student->training_failed_reasons,
            'selDiscipline'   => $disciplineId,
            'selTraining'     => $trainingId,
            'selProgram'      => $programId,
            'avTrDisciplines' => Discipline::all()->lists('name', 'id')->all(),
            'avTrPrograms'    => [0 => 'Select '.Lang::choice('core::terms.facility_training', 1)] + $programs,
            'avTrainings'     => [0 => 'Select Training'] + $trainings,
            'avInstructors'   => [0 => 'Select '.Lang::choice('core::terms.instructor', 1)] + $instructors
        ]);
    }

    /**
     * Print info page for a student
     * After successful student create, user arrives here with Option to review password before encrypted
     */
    public function intermediate($id)
    {
        $student = Student::with([
            'allActiveStudentTrainings',
            'allActiveStudentTrainings.instructor',
            'allActiveStudentTrainings.discipline',
            'allActiveStudentTrainings.facility',
            'allActiveStudentTrainings.training'
        ])->findOrFail($id);

        // Flash a 'please print page' message
        Flash::warning('Please print this page and give it to the student.');

        return View::make('core::students.intermediate')->with([
            'student'         => $student,
            'initialTraining' => $student->allActiveStudentTrainings->first(),
            'password'        => Session::get('student_pwd')
        ]);
    }

    /**
     * If a record being created matches an existing SSN, send them here
     * User chooses to overwrite existing record and activate, or cancel
     * Matched record must be inactive! Cant override active records
     */
    public function duplicate($id)
    {
        // get the duplicate student record
        $student = Student::find($id);

        if (! Input::old()) {
            return Redirect::back();
        }

        // if activate, show warning and bring them back
        // any matching student should be archived after passing or failing tests
        if ($student->is_active) {
            if (Auth::user()->ability(['Admin', 'Staff'], [])) {
                $link = link_to_route('students.edit', $student->full_name, $student->id);
                Flash::danger('Create failed. '.Lang::choice('core::terms.student', 1).' '.$link.' has matching SSN and is active.');
            } else {
                Flash::danger('Create failed. '.Lang::choice('core::terms.student', 1).' with matching SSN is already active. Please contact HeadMaster at '.Formatter::format_phone(Config::get('core.helpPhone')));
            }

            return Redirect::back()->withInput(Input::old());
        }

        return View::make('core::students.duplicate')->with([
            'student'  => $student,
            'training' => Training::find(Input::old('training_id')),
            'program'  => Facility::find(Input::old('facility_id'))
        ]);
    }

    /**
     * Activate the current student
     * For when a new student is being entered and the ssn is already found in the db 
     * (merge new info and reactivate the archived record)
     *
     * Call this merge instead?
     */
    public function merge($id)
    {
        $student = Student::find($id);

        $training = Training::find(Input::get('training_id'));
        // activate student
        $student->activate();

        // update demographics
        $student->first     = Input::get('first');
        $student->middle    = Input::get('middle');
        $student->last      = Input::get('last');
        $student->birthdate = Input::get('birthdate');
        $student->phone     = Input::get('phone');
        $student->alt_phone = Input::get('alt_phone');
        $student->address   = Input::get('address');
        $student->city      = Input::get('city');
        $student->state     = Input::get('state');
        $student->zip       = Input::get('zip');
        
        // append comments
        if (Input::get('comments')) {
            $student->comments  = Input::get('comments').' '.$student->comments;
        }

        // update/save student record
        $student->save();
        
        // update password
        $student->user->resetPassword();
        
        // initial training
        $trStatus  = Input::get('status');
        $trStarted = Input::get('started') ? date('Y-m-d', strtotime(Input::get('started'))) : null;
        $trEnded   = Input::get('ended') ? date('Y-m-d', strtotime(Input::get('ended'))) : null;
        $trExpires = null;

        // passed status, check if an expiration date was set by staff/agency
        if ($trStatus == 'passed') {
            if (Input::get('expires') && Auth::user()->ability(['Admin', 'Staff'], [])) {
                $trExpires = date('Y-m-d', strtotime(Input::get('expires')));
            } else {
                $trExpires = $training->getTrainingExpiration($trEnded);
            }
        }

        // failed
        // set reason for failure
        $reason = Input::get('status') == 'failed' ? Input::get('reason') : null;

        // add initial training
        $student->trainings()->attach($training->id, [
            'discipline_id'     => Input::get('discipline_id'),
            'facility_id'       => Input::get('facility_id'),
            'instructor_id'     => Input::get('instructor_id'),
            'status'            => $trStatus,
            'reason'            => $reason,
            'classroom_hours'   => Input::get('classroom_hours'),
            'distance_hours'    => Input::get('distance_hours'),
            'lab_hours'         => Input::get('lab_hours'),
            'traineeship_hours' => Input::get('traineeship_hours'),
            'clinical_hours'    => Input::get('clinical_hours'),
            'started'           => $trStarted,
            'ended'             => $trEnded,
            'expires'           => $trExpires,
            'creator_id'        => Auth::user()->userable->id,
            'creator_type'      => Auth::user()->userable->getMorphClass()
        ]);

        return Redirect::route('students.edit', $student->id)->withSuccess(Lang::choice('core::terms.student', 1).' activated.');
    }
    

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        // does a student exist with this email / ssn combo already?
        $user = User::with('userable')->where('email', Input::get('email'))->first();
        if ($user && $user->userable && $user->userable_type == 'Student') {
            if ($user->userable->ssn && $user->userable->reallyPlainSsn == Input::get('ssn')) {
                $ignoreId = $user->id;
            }
        } else {
            $ignoreId = null;
        }
        
        // validate new student form
        if ($this->student->fill(Input::all())->validate($ignoreId)) {
            // check if existing SSN here
            // if yes, redirect to intermediate page 
            // ask if override/merge records
            $existingId = $this->student->existingStudent();
            if ($existingId) {
                return Redirect::route('students.duplicate', $existingId)->withInput();
            }

            // ssn is unique, go ahead and create new student
            $studentId = $this->student->addWithInput();

            if ($studentId) {
                Flash::success(Lang::choice('core::terms.student', 1).' added.');
                // flash encrypted password for 1 request
                Session::flash('student_pwd', Input::get('password'));

                return Redirect::route('students.intermediate', $studentId);
            }
        }

        Session::flash('danger', 'Errors found while creating the '.Lang::choice('core::terms.student', 1).', please fix them below.');
        return Redirect::back()->withInput()->withErrors($this->student->errors);
    }

    /**
     * Login as this person
     * @param  int  $id
     * @return Response
     */
    public function loginas($id)
    {
        $person = Student::find($id);
        Auth::logout();
        Auth::loginUsingId($person->user_id);
        Auth::user()->setupSession();

        Flash::success('Logged in as '.Lang::choice('core::terms.student', 1).' <strong>'.$person->full_name.'</strong>');
        return Redirect::route('account');
    }

    /**
     * Generate a fake SSN for a new student
     * Fake SSN format: F[8 digits], always unique
     */
    public function fakeSsn()
    {
        $ssn = $this->student->getFakeSSN();
        return Response::json($ssn);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return Redirect::route('students.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $loggedUser = Auth::user();
        $student = Student::with([
            'events',
            'skillAttempts',
            'skillAttempts.skillexam',
            'certifications',
            'activeAttempts',               // knowledge test attempts NOT ARCHIVED
            'activeSkillAttempts',          // skill test attempts NOT ARCHIVED
            'attempts',
            'attempts.exam',
            'attempts.testevent',
            'scheduledAttempts',
            'scheduledSkillAttempts',
            'allAdas'
        ])->findOrFail($id);

        // count knowledge attempts
        $e = [];
        foreach ($student->activeAttempts as $att) {
            if (! isset($e[$att->exam_id])) {
                $e[$att->exam_id] = 1;
            } else {
                $e[$att->exam_id]++;
            }

            $att->attempt = $e[$att->exam_id];
        }

        // count skill attempts
        $s = [];
        foreach ($student->activeSkillAttempts as $att) {
            if (! isset($s[$att->skillexam_id])) {
                $s[$att->skillexam_id] = 1;
            } else {
                $s[$att->skillexam_id]++;
            }

            $att->attempt = $s[$att->skillexam_id];
        }

        // TRAININGS STUDENT IS ELIGIBLE FOR
        $disciplineId  = Session::has('discipline.id') ? Session::get('discipline.id') : '';
        $avTrainings   = $student->availableTrainings($disciplineId);
        $avTrainingIds = $avTrainings->lists('id')->all();

        // get all trainings for student
        $q = $student->allTrainings()
                    ->with('required_trainings')
                    ->join('instructors', 'instructor_id', '=', 'instructors.id')
                    ->leftJoin('facilities', 'facility_id', '=', 'facilities.id')
                    ->select(
                        'trainings.name AS tr_name',
                        'trainings.abbrev AS tr_abbrev',
                        'facilities.*',
                        DB::raw('CONCAT(instructors.first, " ", instructors.last) AS inc_name')
                    );


        // if instructor...
        // only show instructor student trainings they're related to
        if ($loggedUser->isRole('Instructor')) {
            $q->where('student_training.instructor_id', $loggedUser->userable_id);


            // DETERMINE ELIGIBLE TRAININGS FOR STUDENT
            // used to hide [ADD TRAINING] button when no eligible trainings on add_training page
            // remove trainings if instructor is not teaching them
            $avTrainingIds = array_intersect($loggedUser->userable->teaching_trainings()->lists('id')->all(), $avTrainingIds);
        }

        // if facility...
        // only show trainings at this facility
        if ($loggedUser->isRole('Facility')) {
            $q->where('student_training.facility_id', $loggedUser->userable_id);
        }

        // if NOT poweruser...
        // only show archived training records to staff/admins
        if (! $loggedUser->ability(['Admin', 'Staff', 'Agency'], [])) {
            $q->whereNull('student_training.archived_at');
        }

        // order trainings
        $trainings = $q->get();

        // combine skill/knowledge testing so we can order by date
        $allAttempts = $student->testHistory(false)->sortByDesc('test_date');

        // read-only for Agency
        if (Auth::user()->isRole('Agency')) {
            $this->disableFields();
        }

        return View::make('core::students.edit')->with([
            'allExams'                => Exam::all(),
            'allSkills'               => Skillexam::all(),
            'allCertifications'       => Certification::all(),
            'allAttempts'             => $allAttempts,
            'student'                 => $student,
            'trainings'               => $trainings,
            'allScheduledEventExams'  => $student->scheduledAttempts->lists('testevent_id', 'exam_id')->all(),
            'allScheduledEventSkills' => $student->scheduledSkillAttempts->lists('testevent_id', 'skillexam_id')->all(),
            'ineligibleExams'         => $student->ineligibleExams(),
            'ineligibleSkills'        => $student->ineligibleSkills(),
            'currentInstructor'       => $student->currentInstructor(),
            'eligibleTrainingIds'     => $avTrainingIds,
            'holds'                   => $student->studentHolds(),
            'locks'                   => $student->studentLocks(),
            'rescheduled'             => $student->getRescheduled()
        ]);
    }

    /**
     * Archived record view, staff only
     */
    public function archived($id)
    {
        $user = Auth::user();
        if ($user->isRole('Student') && $user->userable_id != $id) {
            Flash::warning('You do not have access to that record.');
            return Redirect::to('/');
        }
        
        $student = Student::with([
            'events',
            'skillAttempts',
            'skillAttempts.skillexam',
            'certifications',
            'activeAttempts',               // knowledge test attempts NOT ARCHIVED
            'activeSkillAttempts',          // skill test attempts NOT ARCHIVED
            'attempts',
            'attempts.exam',
            'attempts.testevent',
            'scheduledAttempts',
            'scheduledSkillAttempts',
            'allAdas'
        ])->findOrFail($id);

        // count knowledge attempts
        $e = [];
        foreach ($student->activeAttempts as $att) {
            if (! isset($e[$att->exam_id])) {
                $e[$att->exam_id] = 1;
            } else {
                $e[$att->exam_id]++;
            }

            $att->attempt = $e[$att->exam_id];
        }

        // count skill attempts
        $s = [];
        foreach ($student->activeSkillAttempts as $att) {
            if (! isset($s[$att->skillexam_id])) {
                $s[$att->skillexam_id] = 1;
            } else {
                $s[$att->skillexam_id]++;
            }

            $att->attempt = $s[$att->skillexam_id];
        }

        // get all trainings for student
        $trainings = $student->allTrainings()
                    ->with('required_trainings')
                    ->join('instructors', 'instructor_id', '=', 'instructors.id')
                    ->leftJoin('facilities', 'facility_id', '=', 'facilities.id')
                    ->select(
                        'trainings.name AS tr_name',
                        'trainings.abbrev AS tr_abbrev',
                        'facilities.*',
                        DB::raw('CONCAT(instructors.first, " ", instructors.last) AS inc_name')
                    )
                    ->get();

        // combine skill/knowledge testing so we can order by date
        $allAttempts = $student->testHistory(false);

        return View::make('core::students.archived')->with([
            'allExams'                => Exam::all(),
            'allSkills'               => Skillexam::all(),
            'allCertifications'       => Certification::all(),
            'allAttempts'             => $allAttempts->sortByDesc('test_date'),
            'student'                 => $student,
            'trainings'               => $trainings,
            'allScheduledEventExams'  => $student->scheduledAttempts->lists('testevent_id', 'exam_id')->all(),
            'allScheduledEventSkills' => $student->scheduledSkillAttempts->lists('testevent_id', 'skillexam_id')->all(),
            'ineligibleExams'         => $student->ineligibleExams(),
            'ineligibleSkills'        => $student->ineligibleSkills(),
            'currentInstructor'       => $student->currentInstructor(),
            'holds'                   => $student->studentHolds(),
            'locks'                   => $student->studentLocks()
        ]);
    }

    /**
     * Updating an archived record, minimal updates such as comments
     */
    public function archivedUpdate($id)
    {
        $student = Student::findOrFail($id);

        $student->comments = Input::get('comments');
        $student->save();

        return Redirect::route('students.archived', $id)->withSuccess(Lang::choice('core::terms.student', 1).' updated.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $student = Student::find($id);

        if ($this->student->fill(Input::all())->validate($student->user_id)) {
            // param ignores a user id 

            if ($student->updateWithInput()) {
                Flash::success(Lang::choice('core::terms.student', 1).' updated.');
                return Redirect::route('students.edit', [$id]);
            }
        }

        // check for SSN errors (only admin/staff has ability to update ssn)
        if (Auth::user()->ability(['Admin', 'Staff'], []) && $this->student->errors->has('ssn')) {
            // if student with ssn already exists
            if ($this->student->errors->first('ssn') === 'There is already a record matching this SSN.') {
                // lookup existing student already using this ssn
                $existingStudent = Student::where('ssn_hash', saltedHash(Input::get('ssn')))->first();
                
                // error msg including link to offending student record
                $errorMsg = 'This SSN is already in use. '.Lang::choice('core::terms.student', 1).' <a href="'.route('students.edit', $existingStudent->id).'">'.$existingStudent->fullname.'</a> contains conflicting SSN.';
                $this->student->errors->add('existing_ssn_student', $errorMsg);
            }
        }

        Session::flash('danger', 'There were error(s) updating the '.Lang::choice('core::terms.student', 1).'. Please fix them below.');
        return Redirect::back()->withInput()->withErrors($this->student->errors);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Generate a fake json record
     */
    public function populate()
    {
        return Response::json($this->student->populate());
    }

    /**
     * View all test attempts for a student
     */
    public function tests($id)
    {
        // get student with all knowledge and skill attempts
        // only interested in passed or failed attempts, no archived
        $student = Student::with([
            'activeAttempts',
            'activeAttempts.exam',
            'activeAttempts.testevent',
            'activeAttempts.testevent.facility',
            'activeSkillAttempts',
            'activeSkillAttempts.skillexam',
            'activeSkillAttempts.testevent',
            'activeSkillAttempts.testevent.facility',
            'scheduledAttempts',
            'scheduledSkillAttempts'
        ])->find($id);

        // make sure this is the student's attempt
        if (! Auth::user()->ability(['Admin', 'Staff'], []) && ($student->user_id != Auth::user()->id)) {
            return Redirect::route('account');
        }

        // combine and sort all student attempt records, both skill and knowledge
        $allAttempts = $student->testHistory(false);

        // sort by test date
        $allAttempts = $allAttempts->sortBy(function ($att) {
            return $att->testevent->test_date;
        });

        return View::make('core::students.tests')->with([
            'student'        => $student,
            'allAttempts'    => $allAttempts,
            'eligibleExams'  => $student->getEligibleExams(),
            'eligibleSkills' => $student->getEligibleSkills(),
            'exams'          => Exam::all(),
            'skills'         => Skillexam::all()
        ]);
    }

    /**
     * View all training records for a student
     */
    public function trainings($id)
    {
        $student = Student::find($id);

        if (! Auth::user()->ability(['Admin', 'Staff'], []) && $student->user_id != Auth::user()->id) {
            return Redirect::route('account');
        }

        $trainings = $student
                    ->allTrainings()
                    ->leftJoin('facilities', 'facility_id', '=', 'facilities.id')
                    ->join('instructors', 'instructor_id', '=', 'instructors.id')
                    ->select(
                        'trainings.name',
                        'facilities.name AS fac_name',
                        'facilities.license AS fac_license',
                        DB::raw('CONCAT(instructors.first, " ", instructors.last) AS inc_name')
                    )
                    ->get();

        return View::make('core::students.trainings')->with([
            'student'   => $student,
            'trainings' => $trainings
        ]);
    }

    /**
     * View additional detail about a training record
     */
    public function trainingDetail($id, $trainingId)
    {
        $student = Student::find($id);

        if ($student->user_id != Auth::user()->id) {
            return Redirect::route('account');
        }

        // get the training record
        return View::make('core::students.modals.training_detail')->with([
            'student'   => $student,
            'training'  => StudentTraining::find($trainingId)
        ]);
    }


    /**
     * Adds a training record to a student
     *
     * @param  int  $id
     * @return Response
     */
    public function addTraining($id, $disciplineId='', $programId='', $trainingId='')
    {
        // user redirects
        $redirect     = Redirect::route('students.edit', $id);
        $redirectHome = Redirect::to('/');

        // arrays to hold generated records
        // usually these are looked up via ajax UNLESS...
        // form submit fail, or direct URL values passed
        $oldTrainings   = [];
        $oldPrograms    = [];
        $oldInstructors = [];

        $student = Student::find($id);

        // check student was found
        if (is_null($student)) {
            return $redirectHome;
        }

        // get all available disciplines for student
        $avDisciplines = $student->availableDisciplines();
        // get all trainings eligible for student
        $avTrainingIds = $student->availableTrainings()->lists('id')->all();
        
        // set discipline
        if (Session::has('discipline.id')) {
            $disciplineId = Session::get('discipline.id');
        } elseif (Input::old('discipline_id')) {
            $disciplineId = Input::old('discipline_id');
        }

        // check student is eligible for current discipline
        if (! empty($disciplineId) && ! in_array($disciplineId, $avDisciplines->lists('id')->all())) {
            return $redirect->with('warning', Lang::choice('core::terms.student', 1).' not eligible for current discipline training.');
        }

        // set program
        if (Session::has('discipline.program.id')) {
            $programId = Session::get('discipline.program.id');
        } elseif (Input::old('facility_id')) {
            $programId = Input::old('facility_id');
        }

        // set training
        if (Input::old('training_id')) {
            $trainingId = Input::old('training_id');
        }

        // is student eligible for this training?
        if (! empty($trainingId) && ! in_array($trainingId, $avTrainingIds)) {
            return $redirect->with('warning', Lang::choice('core::terms.student', 1).' not eligible for training.');
        }

        // empty discipline
        if (empty($disciplineId)) {
            // cant have passed program without discipline 
            if (! empty($programId)) {
                return $redirect->with('warning', Lang::choice('core::terms.student', 1).' not eligible for training; missing program.');
            }

            // cant have passed training without discipline
            if (! empty($trainingId)) {
                return $redirect->with('warning', Lang::choice('core::terms.student', 1).' not eligible for training; missing training.');
            }
        }
        // non-empty discipline
        // discipline will always be set for a logged in instructor (via setSession, intermediate login page)
        else {
            // get current discipline
            $currDiscipline = Discipline::with([
                'training',
                'trainingPrograms',
                'agencyTrainingPrograms',
                'trainingPrograms.activeInstructors' => function ($query) use ($disciplineId) {
                    $query->wherePivot('discipline_id', $disciplineId);
                }
            ])->find($disciplineId);

            // check program exists within discipline
            if (! empty($programId) && ! in_array($programId, $currDiscipline->trainingPrograms->lists('id')->all())) {
                return $redirect->with('warning', Lang::choice('core::terms.facility_training', 1).' not eligible for current discipline.');
            }

            // get all training programs 
            $oldPrograms = $currDiscipline->trainingPrograms->lists('name', 'id')->all();

            // get all trainings
            $oldTrainings = $currDiscipline->training->lists('name', 'id')->all();

            // check training exists within discipline
            if (! empty($trainingId) && ! in_array($trainingId, $currDiscipline->training->lists('id')->all())) {
                return $redirect->with('warning', 'Training not eligible for current discipline.');
            }

            // if instructors...
            // only allow trainings they teach
            if (Auth::user()->isRole('Instructor')) {
                $currInstructor = Auth::user()->userable;

                // get trainings for instructor
                $instructorTrainingIds = $currInstructor->teaching_trainings()->lists('id')->all();

                // remove trainings if instructor is not teaching them
                // intersect: instructors trainings with students available trainings with discipline trainings
                $avTrainingIds = array_intersect($currDiscipline->training->lists('id')->all(), $instructorTrainingIds, $avTrainingIds);

                // check selected training being taught by instructor
                if (! empty($trainingId) && ! in_array($trainingId, $instructorTrainingIds)) {
                    return $redirect->with('warning', Lang::choice('core::terms.instructor', 1).' not teaching current training.');
                }

                // set old trainings now
                // trainings the current instructor is eligible to add to student
                $oldTrainings = empty($avTrainingIds) ? [] : Training::whereIn('id', $avTrainingIds)->get()->lists('name', 'id')->all();

                // clear programs and instructors dropdowns
                // logged in instructor doesnt select these, they are set for them
                $oldPrograms    = [];
                $oldInstructors = [];
            }

            // ADMIN/STAFF/AGENCY
            // populate available instructors dropdown
            elseif (Auth::user()->ability(['Admin', 'Staff', 'Agency'], [])) {
                // get all instructors
                // if both program/training are set then we can get instructors
                // otherwise instructors will be found via ajax lookup
                if (! empty($trainingId) && ! empty($programId)) {
                    // get all facilities 
                    $currFacility = Facility::with([
                        'activeInstructors' => function ($query) use ($disciplineId) {
                            $query->wherePivot('discipline_id', $disciplineId);
                        },
                        'activeInstructors.teaching_trainings'
                    ])->find($programId);

                    foreach ($currFacility->activeInstructors as $i) {
                        // does this facility instructor do this training?
                        if (! $i->teaching_trainings->isEmpty() && ! in_array($trainingId, $i->teaching_trainings->lists('id')->all())) {
                            continue;
                        }

                        $oldInstructors[$i->id] = $i->full_name;
                    }
                }

                // AGENCY 
                // populate subset of available training program to be "agency_only" special programs
                if (Auth::user()->isRole('Agency')) {
                    $oldPrograms = $currDiscipline->agencyTrainingPrograms->lists('name', 'id')->all();
                }
            }
        }

        $avTrainings   = array(0 => "Select Training") + $oldTrainings;
        $avFacilities  = array(0 => "Select ".Lang::choice('core::terms.facility_training', 1)) + $oldPrograms;
        $avInstructors = array(0 => "Select ".Lang::choice('core::terms.instructor', 1)) + $oldInstructors;
        $avDisciplines = array(0 => "Select Discipline") + $avDisciplines->lists('name', 'id')->all();

        return View::make('core::students.add_training')->with([
            'student'       => $student,
            'instructors'   => $avInstructors, // populated via ajax
            'avFacilities'  => $avFacilities,
            'avTrainings'   => $avTrainings,
            'avDiscipline'  => $avDisciplines,
            'selDiscipline' => $disciplineId,
            'selFacility'   => $programId,
            'selTraining'   => $trainingId,
            'failReasons'   => array(0 => 'Select Reason') + $student->training_failed_reasons
        ]);
    }

    /**
     * Post route for a new training for a student
     */
    public function storeTraining($id)
    {
        if ($this->student_training->validateTraining()) {
            if ($this->student_training->addWithInput($id)) {
                Flash::success('Training added.');
                return Redirect::route('students.edit', [$id]);
            }
        }

        Flash::danger('There was an error adding the '.Lang::choice('core::terms.student', 1).' Training.');
        return Redirect::back()->withInput()->withErrors($this->student_training->errors);
    }

    /**
     * Restore a student training record
     */
    public function restoreTraining($studentTrainingId)
    {
        $tr = StudentTraining::findOrFail($studentTrainingId);
        $tr->archived_at = null;
        $saved = $tr->save();

        if ($saved) {
            Flash::success('Restored training successfully.');
        } else {
            Flash::danger('There was an error restoring this training.');
        }

        return Redirect::route('students.training.edit', [$tr->student_id, $studentTrainingId]);
    }

    /**
     * Archive a Student Training record
     */
    public function archiveTraining($studentId, $attemptId)
    {
        $tr = StudentTraining::with('student')->findOrFail($attemptId);

        $tr->archived_at = date('Y-m-d H:i:s');
        $tr->save();

        Flash::success('Successfully archived training.');
        return Redirect::route('students.training.edit', [$studentId, $attemptId]);
    }

    /**
     * Reconnect a Student's Training and/or Test History to a different Student found by SSN
     *  (Admin only permission protected)
     */
    public function reassignHistory($id)
    {
        $student = Student::with([
            'allStudentTrainings.training',
            'allStudentTrainings.facility',
            'attempts.exam',
            'attempts.testevent.observer',
            'attempts.facility',
            'skillAttempts.skillexam',
            'skillAttempts.testevent.observer',
            'skillAttempts.facility'
        ])->find($id);

        if (is_null($student)) {
            Flash::warning('Unknown '.Lang::choice('core::terms.student', 1));
            return Redirect::back();
        }

        if (Request::isMethod('post')) {

            // strip all characters from ssn
            $ssn    = str_replace(['-', '_', ' '], '', Input::get('ssn'));
            $revSsn = str_replace(['-', '_', ' '], '', Input::get('rev_ssn'));

            if (empty($ssn)) {
                Flash::danger('Please enter a SSN to reassign Training');
                return Redirect::back();
            }

            // check reverse ssn matches rev_ssn
            if (strrev($ssn) != $revSsn) {
                Flash::danger('SSN does not match Reverse SSN');
                return Redirect::back();
            }

            // check at least 1 training or test attempt was submitted for move
            if (! Input::get('move_training_ids') && ! Input::get('move_knowledge_ids') && ! Input::get('move_skill_ids')) {
                Flash::danger('Please select Training or Test History to Reassign');
                return Redirect::back();
            }


            // find student by SSN
            $newStudent = Student::where('ssn_hash', saltedHash($ssn))->first();
            if (is_null($newStudent)) {
                Flash::danger('Unknown SSN '.$ssn);
                return Redirect::back();
            }


            // move trainings
            if (Input::get('move_training_ids')) {
                $trainingIds = Input::get('move_training_ids');

                foreach ($trainingIds as $trId) {
                    // reassign training
                    DB::table('student_training')->where('id', $trId)->update([
                        'student_id' => $newStudent->id
                    ]);

                    // log reassignment
                    \Log::info('Reassigned Training #'.$trId.' from Student ' . $student->fullname . '#' . $student->id . ' to ' . $newStudent->fullname . '#' . $newStudent->id, [
                        'reassignedBy' => Auth::user()->userable->fullName
                    ]);
                }
            }

            // move knowledge attempts
            if (Input::get('move_knowledge_ids')) {
                $attemptIds = Input::get('move_knowledge_ids');

                foreach ($attemptIds as $attId) {
                    // reassign knowledge attempt
                    DB::table('testattempts')->where('id', $attId)->update([
                        'student_id' => $newStudent->id
                    ]);

                    // log reassignment
                    \Log::info('Reassigned Knowledge Attempt #'.$attId.' from Student ' . $student->fullname . '#' . $student->id . ' to ' . $newStudent->fullname . '#' . $newStudent->id, [
                        'reassignedBy' => Auth::user()->userable->fullName
                    ]);
                }
            }

            // move skill attempts
            if (Input::get('move_skill_ids')) {
                $attemptIds = Input::get('move_skill_ids');

                foreach ($attemptIds as $attId) {
                    // reassign skill attempt
                    DB::table('skillattempts')->where('id', $attId)->update([
                        'student_id' => $newStudent->id
                    ]);

                    // log reassignment
                    \Log::info('Reassigned Skill Attempt #'.$attId.' from Student ' . $student->fullname . '#' . $student->id . ' to ' . $newStudent->fullname . '#' . $newStudent->id, [
                        'reassignedBy' => Auth::user()->userable->fullName
                    ]);
                }
            }


            Flash::success('Successfully reassigned History to this '.Lang::choice('core::terms.student', 1).'.');
            return Redirect::route('students.edit', $newStudent->id);
        }

        $testHistory = new Collection;

        if (! $student->attempts->isEmpty()) {
            foreach ($student->attempts as $att) {
                $testHistory->push($att);
            }
        }
        if (! $student->skillAttempts->isEmpty()) {
            foreach ($student->skillAttempts as $att) {
                $testHistory->push($att);
            }
        }

        return View::make('core::students.reassign_history')->with([
            'student'     => $student,
            'testHistory' => $testHistory->sortByDesc('endDate')
        ]);
    }

    /**
     * Edit Student Training record, get route
     */
    public function editTraining($id, $attemptId)
    {
        $loggedUser = Auth::user();

        $studentTraining = StudentTraining::with([
            'student',
            'facility',
            'instructor',
            'training',
            'discipline'
        ])->findOrFail($attemptId);

        return View::make('core::students.edit_training')->with([
            'training'    => $studentTraining,
            'failReasons' => array(0 => 'Select Reason') + $studentTraining->student->training_failed_reasons
        ]);
    }

    /**
     * Update (post) an existing student training record
     */
    public function updateTraining($id)
    {
        if ($this->student_training->validateTraining()) {
            $attemptId       = Input::get('attempt_id');
            $studentTraining = StudentTraining::find($attemptId);

            if ($studentTraining->updateWithInput($attemptId)) {
                Flash::success(Lang::choice('core::terms.student', 1).' training updated.');
                return Redirect::route('students.training.edit', [$id, $attemptId]);
            }
        }

        Flash::danger('There was an error updating the '.Lang::choice('core::terms.student', 1).' Training.');
        return Redirect::back()->withInput()->withErrors($this->student_training->errors);
    }

    /**
     * Receives ended/completion date and determines new expiration date for the training
     */
    public function getTrainingExpiration()
    {
        if (Input::get('ended') && Input::get('training')) {
            $training   = Training::find(Input::get('training'));
            $expiration = $training->getTrainingExpiration(Input::get('ended'));

            return date('m/d/Y', strtotime($expiration));
        }

        return "";
    }

    /**
     * Gets all trainings (in json format) a Student would be eligible for under a specific discipline
     */
    public function getAvailableDisciplineTrainings($id, $disciplineId)
    {
        // coming from students.create
        if ($id == 0) {
            $student = $this->student;
        }
        // coming from page that already has student created
        // (add training)
        else {
            $student = Student::with('passedTrainings')->findOrFail($id);
        }

        return Response::json($student->availableTrainings($disciplineId));
    }

    /**
     * Finds available Events for Student containing Knowledge Exam
     * If requested Knowledge Exam has corequired Skill Exams, it will find all events containing all needed exams
     * (ie: if Exam A has corequired SkillExam B, and Skill B is not already passed, it will find all events containing both events with at least 1 open seat in each)
     */
    public function findKnowledgeEvent($id, $examId)
    {
        $student = Student::with([
            'scheduledExams',
            'passedSkills',
            'passedExams'
        ])->findOrFail($id);

        // get exam and corequired skillexams
        $exam = Exam::with([
            'corequired_skills',
            'required_skills',
            'discipline'
        ])->findOrFail($examId);

        // setup redirect to students.edit
        $redirectTo = Auth::user()->isRole('Student') ? Redirect::to('/') : Redirect::route('students.edit', $id);

        // is the student already scheduled for this exam_id?
        if (in_array($examId, $student->scheduledExams->lists('id')->all())) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> already scheduled for Knowledge Exam <strong>'.$exam->name.'</strong>.');
            return $redirectTo;
        }

        // has student already passed this exam?
        if (in_array($examId, $student->passedExams->lists('id')->all())) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> has already passed Knowledge Exam <strong>'.$exam->name.'</strong>.');
            return $redirectTo;
        }

        // get all skill/knowledge exams that ARE NOT ELIGIBLE for this student
        $ineligibleExams  = $student->ineligibleExams();
        $ineligibleSkills = $student->ineligibleSkills();

        // is student eligible for requested knowledge exam?
        if (in_array($examId, $ineligibleExams->lists('id')->all())) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> not eligible for Knowledge Exam <strong>'.$exam->name.'</strong>.');
            return $redirectTo;
        }

        // check student is eligible for all corequired skill exams
        $scheduleSkillIds = $exam->corequired_skills->lists('id')->all();  // coreqs
        $scheduleKnowIds  = [(int) $examId];                        // main requested knowledge test
        if (! empty($scheduleSkillIds)) {
            foreach ($exam->corequired_skills as $i => $coreqSkill) {
                // student IS eligible, but have they already passed this corequired skillexam?
                if (in_array($coreqSkill->id, $student->passedSkills->lists('id')->all())) {
                    // corequired skillexam is already passed!
                    // remove it from list "eligible events must contain ALL these corequirements"
                    unset($scheduleSkillIds[$i]);
                    continue;
                }

                // student not eligible for corequired skillexam!
                if (in_array($coreqSkill->id, $ineligibleSkills->lists('id')->all())) {
                    Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> not eligible for corequired Skill Exam <strong>'.$coreqSkill->name.'</strong>.');
                    return $redirectTo;
                }
            }
        }

        // get training expirations
        $trainingExpirations = $student->getTrainingExpirationsForExams($scheduleKnowIds, $scheduleSkillIds);

        return View::make('core::students.find_knowledge_event')->with([
            'student'          => $student,
            'exam'             => $exam,
            'scheduleKnowIds'  => $scheduleKnowIds,
            'scheduleSkillIds' => $scheduleSkillIds,
            'expirations'      => $trainingExpirations,
            'eligible_events'  => $student->findEvent($exam->discipline, $scheduleKnowIds, $scheduleSkillIds, 1, $trainingExpirations)
        ]);
    }

    /**
     * Schedule single Student into Event Skillexam
     */
    public function findSkillEvent($id, $skillId)
    {
        $student = Student::with([
            'scheduledSkills',
            'passedSkills',
            'passedExams'
        ])->findOrFail($id);

        // get skillexam and corequired knowledge exams
        $skill = Skillexam::with([
            'corequired_exams',
            'required_exams',
            'discipline'
        ])->findOrFail($skillId);

        // setup redirect depending on user
        $redirectTo = Auth::user()->isRole('Student') ? Redirect::to('/') : Redirect::route('students.edit', $id);

        // student already scheduled for this skill exam?
        if (in_array($skillId, $student->scheduledSkills->lists('skillexam_id')->all())) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> already scheduled for Skill Exam <strong>'.$skill->name.'</strong>.');
            return $redirectTo;
        }

        // student already passed this skill exam?
        if (in_array($skillId, $student->passedSkills->lists('id')->all())) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> already passed Skill Exam <strong>'.$skill->name.'</strong>.');
            return $redirectTo;
        }

        // get skill/knowledge exams that ARE NOT ELIGIBLE for this student
        $ineligibleExams  = $student->ineligibleExams();
        $ineligibleSkills = $student->ineligibleSkills();

        // is student eligible for requested skill exam?
        if (in_array($skillId, $ineligibleSkills->lists('id')->all())) {
            Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> not eligible for Skill Exam <strong>'.$skill->name.'</strong>.');
            return $redirectTo;
        }

        // check if student needs to take all corequired exams
        // have they already passed?
        $scheduleKnowIds  = $skill->corequired_exams->lists('id')->all();      // corequired knowledge exams
        $scheduleSkillIds = [(int) $skillId];                           // main requested skill exam
        if (! empty($scheduleKnowIds)) {
            foreach ($skill->corequired_exams as $i => $coreqExam) {
                // student has already passed this corequired knowledge exam?
                if (in_array($coreqExam->id, $student->passedExams->lists('id')->all())) {
                    unset($scheduleKnowIds[$i]);
                    continue;
                }

                // student has not yet passed corequired knowledge
                // if ineligible at this point its because they dont have the corequirements
                if (in_array($coreqExam->id, $ineligibleExams->lists('id')->all())) {
                    Flash::danger(Lang::choice('core::terms.student', 1).' <strong>'.$student->full_name.'</strong> not eligible for corequired Knowledge Exam <strong>'.$coreqExam->name.'</strong>.');
                    return $redirectTo;
                }
            }
        }

        // get training expirations
        $trainingExpirations = $student->getTrainingExpirationsForExams($scheduleKnowIds, $scheduleSkillIds);

        return View::make('core::students.find_skill_event')->with([
            'student'          => $student,
            'skill'            => $skill,
            'scheduleKnowIds'  => $scheduleKnowIds,
            'scheduleSkillIds' => $scheduleSkillIds,
            'expirations'      => $trainingExpirations,
            'eligible_events'  => $student->findEvent($skill->discipline, $scheduleKnowIds, $scheduleSkillIds, 1, $trainingExpirations)
        ]);
    }

    /**
     * Show Detail info on a scheduled knowledge/skill attempt for a Student
     */
    public function scheduleDetail($id, $attemptId, $testType)
    {
        $student = Student::with(['skillAttempts', 'attempts'])->find($id);
        $user    = Auth::user();

        if (is_null($student)) {
            Flash::danger('Unknown Student.');
            return Redirect::to('/');
        }

        // check for expected test type
        if (! in_array($testType, ['knowledge', 'skill'])) {
            Flash::danger('Unknown Test Type.');
            return Redirect::to('/');
        }

        // check student has an attempt under this ID
        if (! in_array($attemptId, $student->skillAttempts->lists('id')->all()) && ! in_array($attemptId, $student->attempts->lists('id')->all())) {
            Flash::danger(Lang::choice('core::terms.student', 1) . ' does not own this attempt.');
            return Redirect::to('/');
        }

        // get attempt record
        $className = ($testType == 'knowledge') ? 'Testattempt' : 'Skillattempt';
        $attempt   = $className::find($attemptId);

        // check we found the scheduled attempt
        if (is_null($attempt)) {
            Flash::danger('Unknown '.ucfirst($testType).' Attempt.');
            return Redirect::to('/');
        }

        // check for scheduled status
        if (! in_array($attempt->status, ['assigned', 'pending', 'started'])) {
            Flash::danger('This is not a Scheduled Attempt.');
            return Redirect::to('/');
        }

        // if Student, check they are viewing their own record
        if ($user->isRole('Student') && ($user->userable->id != $student->id || $user->userable->id != $attempt->student_id)) {
            Flash::warning('Unauthorized Access.');
            return Redirect::to('/');
        }

        // if Instructor, check this is one of their students
        if ($user->isRole('Instructor')) {
            $ownedStudents = $user->userable->students;

            if (! $ownedStudents || ! in_array($attempt->student_id, $ownedStudents->lists('id')->all())) {
                Flash::warning('Unauthorized Access.');
                return Redirect::to('/');
            }
        }

        return View::make('core::students.schedule_detail')->with([
            'attempt' => $attempt,
            'type'    => $testType,
        ]);
    }

    /**
     * POST route to schedule a single schedule into event
     * Also considers any corequired knowledge exams 
     */
    public function scheduleSkill()
    {
        $eventId   = Input::get('event_id');
        $studentId = Input::get('student_id');
        $skillId   = Input::get('skill_id');
        $coExamIds = Input::get('exam_id');

        // get testevent
        $event = Testevent::findOrFail($eventId);

        // setup redirect depending on user
        $redirectTo = Auth::user()->isRole('Student') ? Redirect::to('/') : Redirect::route('students.edit', $studentId);
        
        // validate schedule request
        if ($event->validateSkillSchedule($skillId)) {
            // schedule student into originally requested skillexam
            if (! $event->scheduleSkillStudent($studentId, $skillId)) {
                return $redirectTo;
            }

            // schedule student into any remaining needed corequired knowledge exams
            if (! empty($coExamIds)) {
                foreach ($coExamIds as $examId) {
                    if (! $event->scheduleKnowledgeStudent($studentId, $examId)) {
                        return $redirectTo;
                    }
                }
            }

            // success messages flashed in testevent model via schedule functions
            return $redirectTo;
        }

        Flash::danger('Unable to schedule '.Lang::choice('core::terms.student', 1));
        return $redirectTo;
    }

    /**
     * POST route to schedule a single student into event
     * Also considers any corequired skill exams 
     */
    public function scheduleKnowledge()
    {
        $eventId    = Input::get('event_id');
        $studentId  = Input::get('student_id');
        $examId     = Input::get('exam_id');
        $coSkillIds = Input::get('skill_id');

        // get testevent
        $event = Testevent::findOrFail($eventId);

        // setup redirect depending on user
        $redirectTo = Auth::user()->isRole('Student') ? Redirect::to('/') : Redirect::route('students.edit', $studentId);

        // validate schedule request
        if ($event->validateSchedule($examId)) {
            // schedule student into originally requested knowledge exam
            if (! $event->scheduleKnowledgeStudent($studentId, $examId)) {
                return $redirectTo;
            }

            // any corequired skillexams (not yet passed) that student needs to schedule for
            if (! empty($coSkillIds)) {
                foreach ($coSkillIds as $skillId) {
                    if (! $event->scheduleSkillStudent($studentId, $skillId)) {
                        return $redirectTo;
                    }
                }
            }

            // success messages flashed in testevent model via schedule functions 
            return $redirectTo;
        }

        Flash::danger('Unable to schedule '.Lang::choice('core::terms.student', 1));
        return $redirectTo;
    }

    /**
     * Remove student from an Event Knowledge Exam
     */
    public function unscheduleKnowledge($studentId, $eventId, $examId)
    {
        $student = Student::find($studentId);
        $exam    = Exam::find($examId);

        if ($student->unscheduleKnowledge($eventId, $examId)) {
            return Redirect::route('students.find.knowledge.event', [$studentId, $examId]);
        }

        return Redirect::route('students.edit', $studentId)->with('danger', 'Unable to re-schedule '.Lang::choice('core::terms.student', 1).' '.$student->fullname.' from Exam '.$exam->name.'.');
    }

    /**
     * Remove student from an Event Skill Exam
     */
    public function unscheduleSkill($studentId, $eventId, $skillexamId)
    {
        $student = Student::find($studentId);
        $exam    = Skillexam::find($skillexamId);

        // remove student from event
        if ($student->unscheduleSkill($eventId, $skillexamId)) {
            return Redirect::route('students.find.skill.event', [$studentId, $skillexamId]);
        }
        
        return Redirect::route('students.edit', $studentId)->with('danger', 'Unable to re-schedule '.Lang::choice('core::terms.student', 1).' '.$student->fullname.' from Exam '.$exam->name.'.');
    }

    public function addAda($id)
    {
        $student = Student::with('adas')->find($id);

        if ($student->adas->isEmpty()) {
            $adas = Ada::orderBy('name')->get();
        } else {
            $adas = Ada::whereNotIn('id', $student->adas->lists('id')->all())->orderBy('name')->get();
        }

        if ($adas->isEmpty()) {
            Flash::warning('There are no ADAs in the system. Please define at least one ADA type.');
            return Redirect::route('students.edit', $id);
        }

        return View::make('core::students.add_ada')->with([
            'student' => $student,
            'adas'    => $adas
        ]);
    }

    public function storeAda($id)
    {
        $student = Student::find($id);
        $adaIds  = Input::get('adas');
        $status  = Input::get('status');

        if ($adaIds) {
            foreach ($adaIds as $adaId) {
                // add each new ADA to student
                $student->adas()->attach($adaId, ['status' => $status]);
            }

            Flash::success('Added '.count($adaIds).' ADA\'s.');
            return Redirect::route('students.edit', $id);
        }

        Flash::danger('Unable to add ADA. Please select one.');
        return Redirect::back();
    }

    public function editAda($id, $ada)
    {
        $student = Student::with([
            'adas' => function ($query) use ($ada) {
                // filter so we only get this ADA
                $query->where('ada_id', '=', $ada);
        }])->find($id);

        return View::make('core::students.edit_ada')
            ->withStudent($student)
            ->withAda($student->adas->first());
    }

    public function updateAda($id, $ada)
    {
        $student = Student::find($id);
        $status  = Input::get('status');
        $notes   = Input::get('notes');

        if ($student !== null) {
            // update the ada 
            $student->adas()->updateExistingPivot($ada, [
                'status' => $status,
                'notes'  => $notes
            ]);

            Flash::success('ADA updated.');
            return Redirect::route('students.edit', $id);
        }

        Flash::danger('ADA could not be updated.');
        return Redirect::back();
    }

    /**
     * Print a training certificate
     */
    public function printTrainingCertificate($studentId, $trainingId)
    {
        // Find the student
        $student = Student::find($studentId);

        // Find the training
        $training = StudentTraining::with('facility')->find($trainingId);


        if ($student && $training) {
            // construct new PDF document (P/L,pt/mm/cm/in
            $pdf = new CertPdf;
            $pdf->certificate($student, $training, $training->facility);


            //Close and output PDF document
            return $pdf->show();
        }

        Flash::danger('Student or training not found.');
        return Redirect::back();
    }

    /**
     * For attaching an image to a given test attempt
     */
    public function attachAttemptImage($attemptId, $type)
    {
        // Check if something was posted to upload
        if (Request::isMethod('post')) {
            // is there something to upload?
            if (Input::hasFile('image')) {
                // There's a file, let's save it to the model now!
                $model = $type === 'skill' ? Skillattempt::find($attemptId) : Testattempt::find($attemptId);

                if ($model) {
                    $img = Input::file('image');
                    $model->image = $img;
                    $saved = $model->save();

                    // now check if there's a matching OTHER model
                    $otherModel = $type === 'skill' ? new Testattempt : new Skillattempt;

                    // try to grab the other model now
                    $otherAttempt = $otherModel->where('testevent_id', $model->testevent_id)->where('student_id', $model->student_id)->first();

                    if ($otherAttempt) {
                        // sync it to other attempt as well
                        $otherAttempt->image_file_name    = $model->image_file_name;
                        $otherAttempt->image_file_size    = $model->image_file_size;
                        $otherAttempt->image_content_type = $model->image_content_type;
                        $otherAttempt->image_updated_at   = $model->image_updated_at;
                        $otherAttempt->save();
                    }

                    if ($saved) {
                        Flash::success('Attached image file successfully.');
                        return Redirect::route('students.edit', $model->student_id);
                    } else {
                        Flash::danger('There was an error uploading the file.');
                        Redirect::route('students.edit', $model->student_id);
                    }
                }
            }
        }

        return View::make('core::students.modals.attach')->with([
            'id'   => $attemptId,
            'type' => $type
        ]);
    }

    /**
     * Edit a Student status to add/resolve hold/lock/etc
     * (Active status is not managed here)
     */
    public function editStatus($id)
    {
        $student = Student::find($id);

        $curHold = $student->getStudentCurrentHold();

        if (count($curHold) == 0) {
            $curHold['instructions'] = '';
            $curHold['comments'] = '';
        }

        $curLock = $student->getStudentCurrentLock();

        if (count($curLock) == 0) {
            $curLock['instructions'] = '';
            $curLock['comments'] = '';
        }

        return View::make('core::students.edit_status')->with([
            'student' => $student,
            'curHold' => $curHold,
            'curLock' => $curLock
        ]);
    }

    /**
     * Toggle holds/locks on/off
     */
    public function updateStatus($id)
    {
        $student = Student::findOrFail($id);

        if ($student->processStudentStatus()) {
            return Redirect::route('students.edit', $student->id)->withSuccess('Successfully updated status.');
        } else {
            return Redirect::route('students.status.edit', $student->id);
        }
    }

    /**
     * Toggle test/skill attempt status on/off
     */
    public function toggleAttempt($id, $attemptId, $type, $action)
    {
        $test = $type == 'knowledge' ? Testattempt::findOrFail($attemptId) : SkillAttempt::findOrFail($attemptId);
        $txtSubject = $type == 'knowledge' ? 'test attempt' : 'skill attempt';

        // archive
        if ($action == 'archive') {
            $txtAction = $test->archived ? 'restored' : 'archived';
            $test->archived = ! $test->archived;
        }
        // hold
        else {
            $txtAction = $test->hold ? 'removed hold from' : 'added hold to';
            $test->hold = ! $test->hold;
        }
        
        $test->save();

        Flash::success('Successfully '.$txtAction.' '.$txtSubject.'.');
        return Redirect::route('students.edit', $id);
    }
}
