<?php namespace Hdmaster\Core\Controllers;

use View;
use Lang;
use Input;
use Response;
use Redirect;
use Request;
use Session;
use Auth;
use Paginator;
use Config;
use File;
use Hdmaster\Core\Notifications\Flash;
use Illuminate\Database\Eloquent\Collection;
use \Student;
use \Facility;
use \Exam;
use \Pendingevent;
use \Testevent;
use \Proctor;
use \Actor;
use \Observer;
use \Skilltest;
use \Skillexam;
use \Discipline;
use \User;
use \FormPdf;
use \SkillPdf;
use Carbon\Carbon;

class EventsController extends BaseController
{

    protected $event;
    protected $proctor;
    protected $actor;
    protected $observer;
    protected $pendingEvent;

    public function __construct(Testevent $event, Proctor $proctor, Actor $actor, Observer $observer, PendingEvent $pendingEvent)
    {
        $this->event        = $event;
        $this->proctor      = $proctor;
        $this->actor        = $actor;
        $this->observer     = $observer;
        $this->pendingEvent = $pendingEvent;

        // prevent an event being updated from paper => web
        // only when event has scheduled students 
        // protects against case of oral student showing up to changed event
        $this->beforeFilter('prevent-paper-to-web', ['only' => 'update']);
    }

    /**
     * Display a listing of all events (except past)
     *
     * @return Response
     */
    public function index()
    {
        if (Auth::user()->isRole('Facility')) {
            $loggedUser = Auth::user();
            $facActions = $loggedUser->userable->actions;

            // if not Testing approved, they should not access events page
            if (! $facActions || (is_array($facActions) && ! in_array('Testing', $facActions))) {
                return Redirect::route('account')->withDanger('You are not a Testing approved Facility.');
            }
        }

        $pastOnly   = Input::get('past', false);
        $searchType = Input::get('type', 'Test Date');

        return View::make('core::events.index')->with([
            'events'     => $this->event->getAll($pastOnly),
            'searchType' => $searchType
        ]);
    }

    public function pending()
    {
        return View::make('core::events.pending')->withEvents($this->pendingEvent->getAll());
    }

    /**
     * Json list of events 
     */
    public function json()
    {
        $city     = Input::get('city');
        $facility = Input::get('facility');

        $loggedUser = Auth::user();

        // Eager load seats available
        $this->event->with('testattempts', 'skillattempts', 'exams', 'skills');

        // Only grab the recent events
        $this->event->where('test_date', '>=', date('Y-m-d', strtotime('-1 month')));

        // City / facility filters
        if ($city || $facility) {
            $all = $this->event->join('facilities', 'testevents.facility_id', '=', 'facilities.id');
            
            if ($city) {
                $all->where('city', '=', $city);
            }

            if ($facility) {
                $all->where('facilities.id', '=', $facility);
            }

            $all = $all->get();
        } else {
            $all = $this->event->all();
        }
        
        $list = [];
        foreach ($all as $event) {
            if (! empty($event)) {
                // filter observer events
                if (! is_null($loggedUser)) {
                    if ($loggedUser->isRole('Observer')) {
                        if ($event->proctor_type == Observer::class && ($event->proctor_id != $loggedUser->userable_id)) {
                            continue;
                        } elseif ($event->observer_id != $loggedUser->userable_id) {
                            continue;
                        }
                    }
                    
                    // filter proctor events
                    if ($loggedUser->isRole('Proctor') && $event->proctor_id != $loggedUser->userable_id) {
                        continue;
                    }

                    // filter facility events
                    if ($loggedUser->isRole('Facility') && $event->facility_id != $loggedUser->userable_id) {
                        continue;
                    }
                }

                $start = new Carbon($event->test_date . ' ' . $event->start_time);

                $list[] = [
                    'id'            => $event->id,
                    'url'           => route('events.show', $event->id),
                    'title'         => $event->calendarTitle,
                    'facility'      => $event->facility->name,
                    'start'         => $start->toIso8601String(),
                    'color'         => $event->calendarColor,
                    'paper'         => $event->is_paper,
                    'closed'        => !$event->is_regional,
                    'isFull'        => $event->isFull,
                    'fullKnowledge' => $event->isFullKnowledge,
                    'fullSkill'     => $event->isFullSkill,
                ];
            }
        }

        return Response::json($list);
    }

    /**
     * Calendar of events
     */
    public function calendar()
    {
        return View::make('core::events.calendar')->with('includeCalendar', true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // get all disciplines in state
        // along with exams/skills under each discipline
        $disciplines = Discipline::with([
            'skills.corequired_exams',
            'exams.corequired_skills'
        ])->get();
        
        // disciplines dont matter for testevents
        // i.e. as long as testing approved, any discipline test may happen at the testsite
        $testsites = Facility::where('actions', 'LIKE', '%Testing%')->orderBy('name', 'ASC')->get();

        return View::make('core::events.create')->with([
            'disciplines' => $disciplines,
            'testsites'   => $testsites
        ]);
    }

    /**
     * Update an assigned testform for a Student in Event Exam
     */
    public function updateTestform()
    {
        $eventId    = Input::get('event_id');
        $testformId = Input::get('testform_id');
        $studentId  = Input::get('student_id');
        $examId     = Input::get('exam_id');

        \DB::table('testattempts')->where('testevent_id', $eventId)->where('exam_id', $examId)->where('student_id', $studentId)->update([
            'testform_id' => $testformId
        ]);

        Flash::success('Successfully updated assigned Testform');

        return Redirect::route('events.edit', $eventId);
    }

    /**
     * Update an assigned skilltest for a Student in Event Skillexam
     */
    public function updateSkilltest()
    {
        $eventId     = Input::get('event_id');
        $skilltestId = Input::get('skilltest_id');
        $studentId   = Input::get('student_id');
        $skillexamId = Input::get('skillexam_id');

        \DB::table('skillattempts')->where('testevent_id', $eventId)->where('skillexam_id', $skillexamId)->where('student_id', $studentId)->update([
            'skilltest_id' => $skilltestId
        ]);

        Flash::success('Successfully updated assigned Skilltest');

        return Redirect::route('events.edit', $eventId);
    }

    /**
     * Change a scheduled students assigned skilltest 
     */
    public function changeTestform($id, $examId, $studentId)
    {
        $event = Testevent::with([
            'exams',
            'testattempts' => function ($q) use ($examId) {
                $q->where('exam_id', $examId);
            },
            'testattempts.student'
        ])->findOrFail($id);

        // ensure exam exists in event
        if (! in_array($examId, $event->exams->lists('id')->all())) {
            return false;
        }

        // ensure event is locked and not ended
        if ($event->locked) {
            Flash::danger('Unable to change Testform -- Event is locked.');
            return Redirect::route('events.edit', $event->id);
        }

        // get knowledge exam with active testforms
        $exam = Exam::with('active_testforms')->findOrFail($examId);

        // get current student schedule record
        $attempt = $event->testattempts->filter(function ($att) use ($studentId) {
            return $att->student_id == $studentId;
        })->first();

        // all other scheduled students for event exam
        $otherStudentAttempts = $event->testattempts->filter(function ($att) use ($studentId) {
            return $att->student_id != $studentId;
        });

        // get student
        $student = Student::with('failedAttempts')->findOrFail($studentId);

        // oral student?
        //  (filter out none oral testforms)
        $testforms = $exam->active_testforms;
        if ($student->is_oral) {
            $testforms = $testforms->filter(function ($form) {
                return $form->getOriginal('oral');
            });
        }

        $pool = new Collection;
        foreach ($testforms as $form) {
            // current assigned testform for student?
            if ($form->id == $attempt->testform_id) {
                $form->notes       = 'Current Testform';
                $form->recommended = 0;
            }

            // check for duplicate testform in event
            elseif (in_array($form->id, $otherStudentAttempts->lists('testform_id')->all())) {
                // does the testform exist for more than 1 person
                $tmp = array_count_values($otherStudentAttempts->lists('testform_id')->all());
                $n   = $tmp[$form->id];

                $msg = 'Assigned to ';
                if ($n > 1) {
                    $msg .= '(' . $n . ') ' . Lang::choice('core::terms.student', $n);
                } else {
                    $msg .= $otherStudentAttempts->keyBy('testform_id')->get($form->id)->student->fullname;
                }

                $form->notes       = $msg;
                $form->recommended = 2;
            }

            // previously failed
            elseif (in_array($form->id, $student->failedAttempts->lists('testform_id')->all())) {
                $form->notes       = 'Previously Failed';
                $form->recommended = 3;
            }

            // no anomalies with this form
            else {
                $form->notes = 'Eligible';
                $form->recommended = 1;
            }

            $pool->push($form);
        }

        $pool = $pool->sortBy('recommended');

        return View::make('core::events.change_testform')->with([
            'event'      => $event,
            'student'    => $student,
            'exam'       => $exam,
            'testforms'  => $pool,
            'currFormId' => $attempt->testform_id
        ]);
    }

    /**
     * Change a scheduled students assigned skilltest 
     */
    public function changeSkilltest($id, $skillId, $studentId)
    {
        $event = Testevent::with([
            'skills',
            'skillattempts' => function ($q) use ($skillId) {
                $q->where('skillexam_id', $skillId);
            },
            'skillattempts.student'
        ])->findOrFail($id);

        // ensure exam exists in event
        if (! in_array($skillId, $event->skills->lists('id')->all())) {
            return false;
        }

        // ensure event is locked and not ended
        if ($event->locked) {
            Flash::danger('Unable to change Testform -- Event is locked.');
            return Redirect::route('events.edit', $event->id);
        }

        $skillexam = Skillexam::with('active_tests')->findOrFail($skillId);

        // get current student schedule record
        $attempt = $event->skillattempts->keyBy('student_id')->get($studentId);

        // all other scheduled students for event exam
        $otherStudentAttempts = $event->skillattempts->filter(function ($att) use ($studentId) {
            return $att->student_id != $studentId;
        });

        // get student
        $student = Student::with('failedSkillAttempts')->findOrFail($studentId);

        $pool = new Collection;
        foreach ($skillexam->active_tests as $test) {
            // current assigned skilltest for student?
            if ($test->id == $attempt->skilltest_id) {
                $test->notes       = 'Current Skilltest';
                $test->recommended = 0;
            }

            // check for duplicate testform in event
            elseif (in_array($test->id, $otherStudentAttempts->lists('skilltest_id')->all())) {
                // does the testform exist for more than 1 person
                $tmp = array_count_values($otherStudentAttempts->lists('skilltest_id')->all());
                $n   = $tmp[$test->id];

                $msg = 'Assigned to ';
                if ($n > 1) {
                    $msg .= '(' . $n . ') ' . Lang::choice('core::terms.student', $n);
                } else {
                    $msg .= $otherStudentAttempts->keyBy('skilltest_id')->get($test->id)->student->fullname;
                }

                $test->notes       = $msg;
                $test->recommended = 2;
            }

            // previously failed
            elseif (in_array($test->id, $student->failedSkillAttempts->lists('skilltest_id')->all())) {
                $test->notes       = 'Previously Failed';
                $test->recommended = 3;
            }

            // no anomalies with this skilltest
            else {
                $test->notes = 'Eligible';
                $test->recommended = 1;
            }

            $pool->push($test);
        }

        $pool = $pool->sortBy('recommended');

        return View::make('core::events.change_skilltest')->with([
            'event'      => $event,
            'student'    => $student,
            'skillexam'  => $skillexam,
            'skilltests' => $pool,
            'currTestId' => $attempt->skilltest_id
        ]);
    }

    /**
     * Changes seating limit for an Event Knowledge Test
     */
    public function changeKnowledgeSeats($id, $examId)
    {
        $event = Testevent::with('exams')->find($id);

        if (is_null($event)) {
            return Redirect::route('events.index');
        }

        // lookup the exam in the event
        $exam = $event->exams->find($examId);

        if (is_null($exam)) {
            return Redirect::route('events.index');
        }

        $maxSeats = $exam->active_testforms()->count();

        // minimum seats (num currently scheduled OR 1)
        $currScheduled = $event->knowledgeStudents()->where('exam_id', $examId)->count();
        $minSeats = ($currScheduled > 0) ? $currScheduled : 1;

        if (Request::isMethod('post')) {
            $newSeats = Input::get('seats');

            // validate seats
            if ($event->validateKnowledgeSeats($examId, $newSeats)) {
                $event->updateKnowledgeSeats($examId, $newSeats);

                return Redirect::route('events.edit', $id)->with('success', 'Updated <strong>'.$exam->name.'</strong> seats.');
            }

            Session::flash('danger', 'There was an error updating seats for Knowledge Test <strong>'.$exam->name.'</strong>.<br><br>'.
                                     $event->errors->first('seats'));
            return Redirect::back()->withInput()->withErrors($event->errors);
        }

        return View::make('core::events.modals.change_knowledge_seats')->with([
            'max_seats'    => $maxSeats,
            'min_seats'    => $minSeats,
            'exam'        => $exam,
            'event'        => $event
        ]);
    }

    /**
     * Changes seating limit for an Event Skill Test
     */
    public function changeSkillSeats($id, $skillId)
    {
        $event = Testevent::with('skills')->find($id);

        $redirectTo = Redirect::to('/');
        if (is_null($event)) {
            return $redirectTo;
        }

        // lookup the skillexams in event
        $skill = $event->skills->find($skillId);
        if (is_null($skill)) {
            return $redirectTo;
        }

        $maxSeats = $skill->active_tests()->count();

        // minimum seats (num currently scheduled OR 1)
        $currScheduled = $event->skillStudents()->where('skillexam_id', $skillId)->count();
        $minSeats = ($currScheduled > 0) ? $currScheduled : 1;
        
        if (Request::isMethod('post')) {
            $newSeats = Input::get('seats');

            // validate seats
            if ($event->validateSkillSeats($skillId, $newSeats)) {
                $event->updateSkillSeats($skillId, $newSeats);

                return Redirect::route('events.edit', $id)->with('success', 'Updated <strong>'.$skill->name.'</strong> seats.');
            }

            Session::flash('danger', 'There was an error updating seats for Skill <strong>'.$skill->name.'</strong>.<br><br>'.
                                     $event->errors->first('seats'));
            return Redirect::back()->withInput()->withErrors($event->errors);
        }

        return View::make('core::events.modals.change_skill_seats')->with([
            'max_seats'    => $maxSeats,
            'min_seats'    => $minSeats,
            'skill'    => $skill,
            'event'        => $event
        ]);
    }

    /**
     * Release tests in an event to begin testing
     */
    public function releaseTests($id)
    {
        $event = Testevent::find($id);

        if (is_null($event)) {
            return Redirect::route('events.index');
        }

        // ensure today and event is locked
        if ($event->locked != 1 || $event->test_date != date('m/d/Y')) {
            return Redirect::to('/events')->with('danger', 'Unable to Release Tests for Event');
        }

        
        $event->releaseTests();

        return Redirect::route('events.edit', $id)->with('success', 'Event Tests Released.');
    }

    /**
     * Acts as a middleman between create testevent and selecting testing team
     */
    public function creating()
    {
        // Create as a pending event?
        if (Input::has('create_as_pending')) {
            // validate to make sure they at least have discipline selected
            if (! Input::get('discipline_id')) {
                Flash::danger('Discipline must be selected before creating a pending event.');
                return Redirect::back()->withInput();
            }

            $disciplineId = Input::get('discipline_id');
            $facilityId   = Input::get('facility_id');

            // check test site was at least selected
            if (! $facilityId) {
                Flash::danger(Lang::choice('core::terms.facility_testing', 1).' must be selected before creating a pending event.');
                return Redirect::back()->withInput();
            }

            // create the event as pending and redirect if success
            $eventIds  = $this->pendingEvent->addWithInput();
            $numEvents = count($eventIds);

            if ($numEvents > 0) {
                // redirect to first event created (if there was multi event)
                Flash::success('Added <strong>'.$numEvents.'</strong> new pending event'.($numEvents==1 ? '' : 's').'.');
                return Redirect::route('events.edit_pending', $eventIds[0]);
            } else {
                Flash::danger('Could not create pending event.');
                return Redirect::back()->withInput();
            }
        }

        // did user want to cancel?
        if (Input::get('cancel')) {
            return Redirect::route('events.create')->withInput();
        }

        // otherwise continue normally with event create..
        if ($this->event->validateBeforeTeam()) {
            // check for other events at this facility on this date
            // (only if we are coming from original event create page, if from site report page, skip past this check)
            if (! Input::get('site_report')) {
                $disciplineId = Input::get('discipline_id');
                $facilityId   = Input::get('facility_id');

                $selTestDates = array_map(function ($n) {
                    return date('Y-m-d', strtotime($n));
                }, Input::get('test_date'));

                // pull all events happening on any of the requested dates, at the requested facility
                $siteEvents = Testevent::where('facility_id', $facilityId)->whereIn('test_date', $selTestDates)->get();
                
                // if facility has other potentially conflict events, show user Test Site Event Report to let them decide
                if (! $siteEvents->isEmpty()) {
                    return Redirect::route('events.site_report', $facilityId)->withInput();
                }
            }

            return Redirect::route('events.select_team')->withInput();
        }

        return Redirect::back()->withInput()->withErrors($this->event->errors);
    }

    /**
     * Delete event 
     *  (Soft-delete, empty events only)
     */
    public function delete($id)
    {
        $event = Testevent::with(['testattempts', 'skillattempts'])->withTrashed()->find($id);

        // check event exists by id
        if (is_null($event)) {
            Flash::danger('Unknown Event.');
            return Redirect::route('events.index');
        }

        // previously soft deleted
        if ($event->deleted_at) {
            Flash::danger('Previously deleted Event.');
            return Redirect::route('events.index');
        }

        // prevent delete if event has any scheduled students (other than rescheduled)
        if (! $event->testattempts->isEmpty() || ! $event->skillattempts->isEmpty()) {
            Flash::danger('Non-empty Event.');
            return Redirect::route('events.edit', $id);
        }
        

        $event->delete();

        \Log::info('Deleted Event #'.$id.' on '. $event->test_date, [
            'deletedBy' => Auth::user()->userable->fullName
        ]);

        Flash::success('Successfully deleted Event #'.$id);
        return Redirect::route('events.edit', $id);
    }

    /**
     * Intermediate page after events.create if the selected facility+testdate has other events scheduled
     * Page shows user all events at this facility+date that could potentially cause a conflict
     * User has options to go back, or continue creating (ie goto selectTeam)
     */
    public function siteReport($facilityId)
    {
        // input old should exist otherwise we didnt come from events.create page
        if (! Input::old()) {
            Flash::warning('Page cannot be refreshed.');
            return Redirect::route('events.create');
        }

        // discipline
        $disciplineId = Input::old('discipline_id');

        // format selected test dates
        $selTestDates = array_map(function ($n) {
            return date('Y-m-d', strtotime($n));
        }, Input::old('test_date'));

        // get all potentially conflicting events
        $siteEvents = Testevent::with([
            'discipline',
            'skills',
            'exams',
            'knowledgeStudents',
            'skillStudents'
        ])->where('facility_id', $facilityId)->whereIn('test_date', $selTestDates)->get();

        // setup knowledge exams
        $knowExams  = array_filter(Input::old('exam_seats'));
        $skillExams = array_filter(Input::old('skill_seats'));

        return View::make('core::events.site_report')->with([
            'facility'   => Facility::find($facilityId),
            'discipline' => Discipline::find($disciplineId),
            'events'     => $siteEvents,
            'knowledge'  => $knowExams,
            'skill'      => $skillExams
        ]);
    }

    /**
     * Select testing team for a new event
     *
     * @return Response
     */
    public function selectTeam()
    {
        $input = Input::old() ? Input::old() : Input::all();

        if (empty($input)) {
            return Redirect::route('events.create')->with('danger', 'Page cannot be refreshed.');
        }

        // clear empty seats/tests
        $input = $this->event->modifyEventInput($input);

        // test date
        $testDate = $input['test_date'];

        // discipline
        $disciplineId = $input['discipline_id'];
        $discipline = Discipline::find($disciplineId);
        
        // test site
        $facilityId   = $input['facility_id'];
        $facility = Facility::find($facilityId);

        // get available test team 
        $avObservers = $this->observer->availableOnDates($disciplineId, $testDate, $facilityId);
        $avProctors  = $this->proctor->availableOnDates($disciplineId, $testDate, $facilityId);
        $avActors    = $this->actor->availableOnDates($disciplineId, $testDate, $facilityId);

        // if no observers send them back
        // need available observers to create an event!
        if ($avObservers->isEmpty()) {
            $msg = 'No available '.Lang::choice('core::terms.observer', 2).' at '.$facility->name.' for '.$discipline->name;

            // include link to facility with missing observers for staff
            if (Auth::user()->ability(['Admin', 'Staff'], [])) {
                $link = link_to_route('facilities.edit', $facility->name, [$facility->id]);
                $msg = 'No available '.Lang::choice('core::terms.observer', 2).' at '.$link.' for '.$discipline->name;
            }

            Flash::danger($msg);
            return Redirect::route('events.create')->withInput($input);
        }

        return View::make('core::events.select_team')->with([
            'event'      => $input,
            'discipline' => Discipline::find($disciplineId),
            'proctors'   => $avProctors,
            'actors'     => $avActors,
            'observers'  => $avObservers,
            'test_site'  => Facility::find($facilityId)
        ]);
    }

    /**
     * Change testing team for an event
     */
    public function changeTeam($id)
    {
        $event = Testevent::with([
            'skills',
            'facility',
            'exams',
            'discipline',
            'knowledgeStudents',
            'skillStudents'
        ])->findOrFail($id);

        if (Request::isMethod('post')) {
            $oldObs = $event->observer_id;

            // must have an observer
            $observer = Input::get('observer_id');
            if (! $observer) {
                return Redirect::back()->withDanger('Event must have an '.Lang::choice('core::terms.observer', 1).'.');
            }
            if (count($observer) > 1) {
                return Redirect::back()->withDanger('Event must have only 1 '.Lang::choice('core::terms.observer', 1).'.');
            }

            // will come in as array, should only have 1 entry
            $observerId = current(Input::get('observer_id'));

            // update observer
            $event->observer_id = $observerId;
            $event->is_mentor   = Input::get('is_mentor') ? true : false;

            // update proctor
            if (Input::get('proctor_id')) {
                $proctorId   = current(Input::get('proctor_id'));
                $proctorType = Input::get('proctor_type.'.$proctorId);

                $event->proctor_id   = User::find($proctorId)->userable_id;
                $event->proctor_type = $proctorType;
            }
            
            // update actor
            if (Input::get('actor_id')) {
                $actorId   = current(Input::get('actor_id'));
                $actorType = Input::get('actor_type.'.$actorId);

                if (! empty($actorType)) {
                    $event->actor_id   = User::find($actorId)->userable_id;
                    $event->actor_type = $actorType;
                }
            }

            // update event
            $event->save();

            return Redirect::route('events.edit', $event->id)->with('success', 'Event Testing Team updated.');
        }

        // filter test team options
        // FOR PROCTOR AND ACTOR
        //   * PROCTOR_ID MIGHT BE OBSERVER TYPE!!!!!!
        //   * ACTOR_ID MIGHT BE OBSERVER TYPE!!!!!!
        $proctors  = $this->proctor->availableOnDates($event->discipline_id, $event->test_date, $event->facility_id, $event->proctor_id);
        $actors    = $this->actor->availableOnDates($event->discipline_id, $event->test_date, $event->facility_id, $event->actor_id);
        $observers = $this->observer->availableOnDates($event->discipline_id, $event->test_date, $event->facility_id, $event->observer_id)->keyBy('user_id');

        // Check if there are any conflicts of interest with the observers who may have also been 
        // an instructor of one of the students scheduled to take the test.
        // Bulid array of student id's for both knowledge and skills
        $studentIdArr = array_merge($event->knowledgeStudents->lists('id')->all(), $event->skillStudents->lists('id')->all());
        $studentIdArr = array_unique($studentIdArr);

        // Get all students and active trainings to check see if there is an observer who is also 
        // an instructor creating a conflict of interest
        $students = Student::with('trainings')->whereIn('id', $studentIdArr)->get();

        $allinstructors = \Instructor::all()->lists('user_id', 'id')->all();
        $observerList = $observers->lists('user_id', 'id')->all();

        // Loop through to remove observers with conflict of interest prior to returning a list 
        // of observers who are able to sit in this exam.
        foreach ($students as $student) {
            foreach ($student->trainings as $training) {
                if (in_array($allinstructors[$training->pivot->instructor_id], $observerList)) {
                    $observers->forget($allinstructors[$training->pivot->instructor_id]);
                    continue 2;
                }
            }
        }

        // PAGINATION 
        // (for the array, since it's not an eloquent model / collection)
        // 
        // $perPage     = Config::get('core.pagination.default');
        // $currentPage = Input::get('page') - 1;
        // $pagedData   = array_slice($proctors, $currentPage * $perPage, $perPage);
        // $proctors    = Paginator::make($pagedData, count($proctors), $perPage);

        return View::make('core::events.change_team')->with([
            'event'      => $event,
            'proctors'   => $proctors,
            'actors'     => $actors,
            'observers'  => $observers
        ]);
    }

    public function editPending($id)
    {
        $event = Pendingevent::with([
            'skills',
            'exams',
            'proctor',
            'facility',
            'discipline.exams',
            'discipline.skills'
        ])->findOrFail($id);

        // default select
        $observers = [0 => 'No '.Lang::choice('core::terms.observer', 1)];
        $proctors  = [0 => 'No '.Lang::choice('core::terms.proctor', 1), '-1|observer' => Lang::choice('core::terms.observer', 1).' filling in'];
        $actors    = [0 => 'No '.Lang::choice('core::terms.actor', 1), '-1|observer' => Lang::choice('core::terms.observer', 1).' filling in'];

        // if a test date is set, look up available test team
        if ($event->test_date) {
            $collectObs  = $this->observer->availableOnDates($event->discipline->id, $event->test_date, $event->facility_id);
            $collectProc = $this->proctor->availableOnDates($event->discipline->id, $event->test_date, $event->facility_id);
            $collectAct  = $this->actor->availableOnDates($event->discipline->id, $event->test_date, $event->facility_id);

            // values in dropdown will need to include person type
            // to differentiate b/t proctor or observer filling as proctor
            $collectObs = $collectObs->map(function ($o) {
                $o->sel_obs = $o->id.'|observer';
                return $o;
            });
            $collectProc = $collectProc->map(function ($p) {
                $p->sel_obs = $p->id.'|proctor';
                return $p;
            });
            $collectAct = $collectAct->map(function ($a) {
                $a->sel_obs = $a->id.'|actor';
                return $a;
            });
            
            // add potential observers
            $observers += $collectObs->lists('full_name', 'sel_obs')->all();

            // add potential proctors
            $proctors += $collectProc->lists('full_name', 'sel_obs')->all();

            // add potential actors
            $actors += $collectAct->lists('full_name', 'sel_obs')->all();

            // if an observer is set, dont include them in possible 'filling in' for actors/proctors
            // option for use current observer is 'Observer filling in'
            if (isset($event->observer_id)) {
                $currObsId = $event->observer_id;

                // use filter to remove the current observer for collection
                $collectObs = $collectObs->filter(function ($o) use ($currObsId) {
                    if ($o->id != $currObsId) {
                        return $o;
                    }
                });
            }

            // add observers filling in
            $proctors += $collectObs->lists('filling_in_full_name', 'sel_obs')->all();
            $actors   += $collectObs->lists('filling_in_full_name', 'sel_obs')->all();
        }

        return View::make('core::events.edit_pending')->with([
            'event'      => $event,
            'observers'  => $observers,
            'proctors'   => $proctors,
            'actors'     => $actors
        ]);
    }

    public function updatePending($id)
    {
        $evt = Pendingevent::with('exams', 'skills')->find($id);

        // update as normal pending event
        if ($evt->validatePending()) {
            if ($evt->updateWithInput()) {
                // Is this a publish?
                if (Input::get('publish_pending') !== null) {
                    // it's a publish, validate everything we need
                    if ($this->pendingEvent->validatePublish() === true) {
                        // publish pending event
                        $created = Testevent::create([
                            'discipline_id' => $evt->discipline_id,
                            'test_date'     => date('Y-m-d', strtotime($evt->test_date)),
                            'start_time'    => $evt->start_time,
                            'facility_id'   => $evt->facility_id,
                            'observer_id'   => $evt->observer_id,
                            'proctor_id'    => $evt->proctor_id,
                            'proctor_type'  => $evt->proctor_type,
                            'actor_id'      => $evt->actor_id,
                            'actor_type'    => $evt->actor_type,
                            'is_regional'   => $evt->is_regional,
                            'is_paper'      => $evt->is_paper,
                            'is_mentor'     => $evt->is_mentor,
                            'comments'      => $evt->comments
                        ]);

                        if ($created) {
                            // add knowledge exams
                            foreach ($evt->exams as $exam) {
                                $created->exams()->attach($exam->id, [
                                    'open_seats'     => $exam->pivot->open_seats,
                                    'reserved_seats' => $exam->pivot->reserved_seats,
                                    'is_paper'         => $exam->pivot->is_paper
                                ]);
                            }

                            // add skill exams
                            foreach ($evt->skills as $exam) {
                                $created->skills()->attach($exam->id, [
                                    'open_seats'     => $exam->pivot->open_seats,
                                    'reserved_seats' => $exam->pivot->reserved_seats
                                ]);
                            }

                            // finally, delete pending event record
                            $evt->exams()->detach();
                            $evt->skills()->detach();
                            $evt->delete();

                            return Redirect::route('events.edit', $created->id)->withSuccess('Pending event published.');
                        }
                    }

                    Flash::danger('There was an error publishing the Pending Event.');
                    return Redirect::back()->withInput()->withErrors($this->pendingEvent->errors);
                } else {
                    // not a publish, return as normal
                    return Redirect::route('events.edit_pending', [$id])->with('success', 'Pending event updated.');
                }
            }
        }

        return Redirect::back()->withInput()->withErrors($this->pendingEvent->errors);
    }

    /**
     * Populate test team (actors/proctor/observer) for dropdown selection in edit pending event
     * Json response
     */
    public function populateTestTeam($id)
    {
        $testTeam = [];

        $disciplineId = Input::get('discipline_id');
        $facilityId   = Input::get('facility_id');
        $testDate     = Input::get('test_date');

        $testTeam['observers'] = $this->observer->availableOnDates($disciplineId, $testDate, $facilityId);
        $testTeam['actors']    = $this->actor->availableOnDates($disciplineId, $testDate, $facilityId);
        $testTeam['proctors']  = $this->proctor->availableOnDates($disciplineId, $testDate, $facilityId);

        return Response::json($testTeam);
    }

    /**
     * Store a newly created testevent in the database
     */
    public function store()
    {
        // did user want to cancel?
        if (Input::get('cancel')) {
            return Redirect::route('events.create')->withInput();
        }

        // Not a pending event, continue as normal
        if ($this->event->fill(Input::all())->validate()) {
            $eventIds  = $this->event->addWithInput();
            $numEvents = count($eventIds);

            if ($numEvents > 0) {
                // events.create facility_id[] is an array, events.edit facility is numeric
                // same thing for test_date, comes thru as array
                // if Input::old('facility_id') is set an error will be thrown about htmlentities expecting string 
                Input::flashOnly('discipline_id', 'is_regional', 'is_paper', 'is_mentor');

                // redirect to first event created (if there was multi event)
                return Redirect::route('events.edit', $eventIds[0])
                    ->with('success', 'Added <strong>'.$numEvents.'</strong> new Test Event'.($numEvents==1 ? '' : 's').'.');
            }
        }

        return Redirect::back()->withDanger($this->event->errors->first('observer_id'))->withInput();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $user = Auth::user();

        // Show the edit page if they can edit
        if ($user && $user->can('events.edit')) {
            return Redirect::route('events.edit', $id);
        }

        // return the view
        return View::make('core::events.show')->with([
            'event' => Testevent::with('skills', 'exams', 'facility')->find($id)
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $event = Testevent::withTrashed()->with([
            'skills.active_tests',
            'skills.required_exams',
            'skills.corequired_exams',
            'exams.active_testforms',
            'exams.required_skills',
            'exams.corequired_skills',
            'observer',
            'observer.user',
            'proctor',
            'actor',
            'testattempts',
            'skillattempts',
            'skillStudents',
            'knowledgeStudents'
        ])->find($id);

        if (is_null($event) || ($event->deleted_at && ! Auth::user()->ability(['Staff', 'Admin'], []))) {
            Flash::danger('Unknown Event');
            return Redirect::to('/');
        }

        // soft-deleted event view
        if ($event->deleted_at) {
            return View::make('core::events.deleted')->with([
                'event' => $event,
            ]);
        }

        // get all files uploaded for this event
        $files = File::files($_SERVER['DOCUMENT_ROOT'] . "/uploads/events/" . $id);

        // organize students per exam
        $students = [];
        foreach ($event->skills as $skill) {
            $students['skill'][$skill->id] = $event->skillStudents->filter(function ($st) use ($skill) {
                return $skill->id == $st->pivot->skillexam_id;
            })->values();
        }
        foreach ($event->exams as $exam) {
            $students['knowledge'][$exam->id] = $event->knowledgeStudents->filter(function ($st) use ($exam) {
                return $exam->id == $st->pivot->exam_id;
            })->values();
        }

        // read-only for Agency
        if (Auth::user()->isRole('Agency')) {
            $this->disableFields();
        }

        return View::make('core::events.edit')->with([
            'event'         => $event,
            'all_released'  => $event->all_released(),
            'uploadedFiles' => $files,
            'loading'       => View::make('core::partials.loading')->render(),
            'students'      => $students
        ]);
    }

    public function knowledgeSearch()
    {
        Session::set('fillKnowledgeSearch', Input::get('search'));
        Session::set('fillKnowledgeSearchFor', Input::get('search-type'));
        return Redirect::route("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
    }

    /**
     * Fill a Knowledge Test in an Event with Students
     */
    public function fillKnowledgeSeats($id, $examId)
    {
        $event = Testevent::with([
            'exams',
            'skills',
            'facility'
        ])->findOrFail($id);

        $exam = Exam::with([
            'active_testforms',
            'corequired_skills',
            'required_trainings'
        ])->findOrFail($examId);

        // check $examId exists in event
        if (! in_array($examId, $event->exams->lists('id')->all())) {
            return Redirect::route('events.index');
        }

        // total # scheduled for event exam
        $numScheduled = $event->knowledgeStudents->filter(function ($st) use ($examId) {
            return $st->pivot->exam_id == $examId;
        })->count();
        // total # seats for event exam
        $numSeats = $event->exams->find($examId)->pivot->open_seats;
        
        // check event exam isnt already full! 
        // (arrived via browser Back button, direct nav, ..)
        if ($numScheduled > 0 && $numSeats <= $numScheduled) {
            return Redirect::route('events.edit', $id);
        }

        // submitted students for scheduling
        if (Request::isMethod('post') && empty(Input::get('searchBtn'))) {
            if ($this->event->validateFillSeats()) {
                $studentIds = Input::get('student_id');

                // setup exam arrays
                $examIds  = array($examId);
                $skillIds = Input::get('exam_coreq_skill_id') ? Input::get('exam_coreq_skill_id') : [];

                // attempt to schedule multiple students in at once
                $event->fillSeats($studentIds, $examIds, $skillIds);

                return Redirect::route('events.edit', $id);
            }

            return Redirect::back()->withInput()->withErrors($this->event->errors);
        }


        // holds easily accessible exam info
        $examInfo = [];

        // corequirements
        if (! $exam->corequired_skills->isEmpty()) {
            foreach ($exam->corequired_skills as $corequired) {
                // is this skillexam being offered in this event?
                if (in_array($corequired->id, $event->skills->lists('id')->all())) {
                    // total # seats
                    $totalSeats = (int) $event->skills->find($corequired->id)->pivot->open_seats;
                    // total # scheduled
                    $numScheduled = $event->skillStudents->filter(function ($st) use ($corequired) {
                        return $st->pivot->skillexam_id == $corequired->id;
                    })->count();

                    // save main exam testform data
                    $examInfo['skill'][$corequired->id]['rem_seats']  = $totalSeats - $numScheduled;
                }
            }
        }

        // total seat count
        $totalSeats   = (int) $event->exams->find($examId)->pivot->open_seats;
        // total # scheduled for event exam
        $numScheduled = $event->knowledgeStudents->filter(function ($st) use ($examId) {
            return $st->pivot->exam_id == $examId;
        })->count();

        // remaining seat count
        // (save main exam testform data)
        $examInfo['exam'][$examId]['rem_seats'] = $totalSeats - $numScheduled;

        return View::make('core::events.fill_knowledge_seats')->with([
            'event'             => $event,
            'exam'              => $exam,
            'exam_info'         => $examInfo,
            'eligible_students' => $event->eligibleStudents($examId)
        ]);
    }

    /**
     * Fill a Skill Test in an Event with Students
     */
    public function fillSkillSeats($id, $skillId)
    {
        $event = Testevent::with([
            'skills',
            'exams',
            'facility'
        ])->findOrFail($id);

        $skill = Skillexam::with([
            'corequired_exams',
            'required_trainings'
        ])->find($skillId);

        // check skillexam exists in event
        if (! in_array($skillId, $event->skills->lists('id')->all())) {
            return Redirect::route('events.index');
        }

        // total # seats
        $numSeats = $event->skills->find($skillId)->pivot->open_seats;
        // total # scheduled students in event skill exam
        $numScheduled = $event->skillStudents->filter(function ($st) use ($skillId) {
            return $st->pivot->skillexam_id == $skillId;
        })->count();

        // check event exam isnt already full! 
        // (arrived via browser Back button, direct nav, ..)
        if ($numScheduled > 0 && $numSeats <= $numScheduled) {
            return Redirect::route('events.edit', $id);
        }

        // submitted students for scheduling
        // Is it a posted form, but NOT a search? do the scheduling
        if (Request::isMethod('post') && empty(Input::get('searchBtn'))) {
            if ($this->event->validateFillSeats()) {
                $studentIds = Input::get('student_id');

                // setup exam arrays
                $skillIds = array($skillId);
                $examIds  = Input::get('skill_coreq_exam_id') ? Input::get('skill_coreq_exam_id') : [];

                // attempt to schedule multiple students in at once
                $event->fillSeats($studentIds, $examIds, $skillIds);

                return Redirect::route('events.edit', $id);
            }

            return Redirect::back()->withInput()->withErrors($this->event->errors);
        }

        // holds easily accessible exam info
        $examInfo = [];

        // corequirements
        if (! $skill->corequired_exams->isEmpty()) {
            foreach ($skill->corequired_exams as $coExam) {
                // is this knowledge exam being offered in this event?
                if (in_array($coExam->id, $event->exams->lists('id')->all())) {
                    // total seat count
                    $totalSeats = (int) $event->exams->find($coExam->id)->pivot->open_seats;
                    // total # scheduled for event exam
                    $numScheduled = $event->knowledgeStudents->filter(function ($st) use ($coExam) {
                        return $st->pivot->exam_id == $coExam->id;
                    })->count();

                    // save main exam testform data
                    $examInfo['exam'][$coExam->id]['rem_seats'] = $totalSeats - $numScheduled;
                }
            }
        }

        // total seat count
        $totalSeats = (int) $event->skills->find($skillId)->pivot->open_seats;
        // total # scheduled for event skillexam
        $numScheduled = $event->skillStudents->filter(function ($st) use ($skillId) {
            return $st->pivot->skillexam_id == $skillId;
        })->count();

        // save main exam testform data
        $examInfo['skill'][$skillId]['rem_seats'] = $totalSeats - $numScheduled;

        return View::make('core::events.fill_skill_seats')->with([
            'event'             => $event,
            'skill'             => $skill,
            'exam_info'         => $examInfo,
            'eligible_students' => $event->eligibleSkillStudents($skillId)
        ]);
    }

    /**
     * Lock an event (i.e. prevent scheduling)
     */
    public function lock($id)
    {
        $event = Testevent::findOrFail($id);

        // protect locking an event unless certain conditions are met
        if (! $event->lockable) {
            Flash::danger('Unable to Lock this event.');
            return Redirect::route('events.edit', $id);
        }

        // proceed locking event
        $event->locked = 1;
        $event->save();

        // if this a paper event AND the event is today, also release the tests automatically
        if ($event->is_paper && $event->test_date == date('m/d/Y')) {
            $event->releaseTests();
            Flash::success('Tests released for this event.');
        }

        return Redirect::route('events.edit', $id)->with('success', 'Event locked.');
    }

    /**
     * Unschedule a Student from a scheduled knowledge exam
     */
    public function unscheduleKnowledge($id, $examId, $studentId)
    {
        $student = Student::find($studentId);
        $exam    = Exam::find($examId);

        if ($student->unscheduleKnowledge($id, $examId)) {
            return Redirect::route('students.find.knowledge.event', [$studentId, $examId]);
        }

        return Redirect::route('events.edit', $id)->with('danger', 'Unable to re-schedule '.Lang::choice('core::terms.student', 1).' '.$student->fullname.' from Exam '.$exam->name.'.');
    }

    /**
     * Unschedule a Student from a scheduled skill exam
     */
    public function unscheduleSkill($id, $skillId, $studentId)
    {
        $student = Student::find($studentId);
        $skill     = Skillexam::find($skillId);

        if ($student->unscheduleSkill($id, $skillId)) {
            return Redirect::route('students.find.skill.event', [$studentId, $skillId]);
        }
        
        return Redirect::route('events.edit', $id)->with('danger', 'Unable to re-schedule '.Lang::choice('core::terms.student', 1).' '.$student->fullname.' from Exam '.$skill->name.'.');
    }

    public function unlock($id)
    {
        $event = Testevent::find($id);

        $event->locked = 0;
        $event->save();

        Flash::warning('This event is happening today. Any changes you make could have a drastic affect. Be careful!', 'Warning');

        return Redirect::route('events.edit', [$id])->with('success', 'Event unlocked.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $evt = Testevent::find($id);

        if ($this->event->fill(Input::all())->validateUpdate()) {
            if ($evt->updateWithInput()) {
                return Redirect::route('events.edit', [$id])->with('success', 'Event updated.');
            }
        }

        return Redirect::back()->withInput()->withErrors($this->event->errors);
    }

    /**
     * Upload files to the specified resource.
     *
     * @param  int  $id
     * @return Redirect
     */
    public function uploadEventFiles($id)
    {
        if (Input::hasFile('eventFiles')) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/events/" . $id . "/";
                    
            foreach (Input::file("eventFiles") as $file) {
                $file->move($target_dir, $file->getClientOriginalName());
            }
        }
        return Redirect::route('events.edit', [$id])->with('success', 'Event Files Saved.');
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
     * Administrator's report for double-checking
     * @param  int $id
     * @return Response
     */
    public function printAdminReport($id)
    {
        $event = Testevent::with(
            'testattempts',
            'testattempts.exam',
            'testattempts.student',
            'skillattempts',
            'skillattempts.student',
            'skillattempts.responses',
            'skillattempts.responses.task',
            'skillattempts.responses.task.steps',
            'skillattempts.skilltest',
            'skillattempts.skillexam',
            'skillattempts.skilltest.tasks'
        )->findOrFail($id);

        // This should become an array like:
        // [student_id] => {
        // 		task => Some Task Name
        // 		steps => [...] // array of steps / input?
        // }

        $stepIds  = [];
        $data     = [];
        $skills   = [];
        $steps    = null;
        $ordinals = null;

        // loop through each attempt
        foreach ($event->skillattempts as $skill) {
            // loop through each response for this attempt
            foreach ($skill->responses as $response) {
                $missedKey      = false; // used to check whether they failed because a key was missed or not
                $minimum        = $skill->skilltest->minimum;
                $totalSteps     = $response->task->steps->count();
                $steps          = $response->decodedResponse;
                $stepsCompleted = 0;
                $keySteps       = $response->task->steps->lists('is_key', 'id')->all();

                // Set the task name and the skilltest ID
                $skills[$skill->student_id][$response->skilltask_id]['task']   = $response->task->title;
                $skills[$skill->student_id][$response->skilltask_id]['abbrev'] = $skill->skillexam->abbrev;
                $skills[$skill->student_id][$response->skilltask_id]['test']   = $skill->skilltest_id;

                if (is_array($steps)) {
                    foreach ($steps as $stepId => $s) {
                        // Is this step correct?
                        if ($s['completed']) {
                            $stepsCompleted++;
                        } else {
                            // They missed this step... did they miss a key step?
                            if (array_key_exists($stepId, $keySteps) && $keySteps[$stepId] !== false) {
                                $missedKey = true;
                            }
                        }
 
                        // - if the response has a INCOMPLETE step 
                        // - OR there is data / variable response, add it to the array				
                        if (! $s['completed'] || array_key_exists('data', $s)) {
                            $stepIds[] = $stepId;

                            // make sure this student is included in the list
                            if (! array_key_exists($skill->student_id, $data)) {
                                $data[$skill->student_id] = [
                                    'student'   => $skill->student,
                                    'anomalies' => $skill->anomalies
                                ];
                            }

                            // now append the task name and step
                            $data[$skill->student_id]['tasks'][$response->skilltask_id]['name'] = $response->task->title;
                            $data[$skill->student_id]['tasks'][$response->skilltask_id]['steps'][$stepId] = $s;
                        }
                    }
                }

                // calculate their score for this task
                $taskScore = round(($stepsCompleted / $totalSteps) * 100);

                // Set the score for this task
                $skills[$skill->student_id][$response->skilltask_id]['score'] = $taskScore;

                // set the failType, if applicable
                if ($missedKey === true) {
                    $skills[$skill->student_id][$response->skilltask_id]['scoreType'] = 'K';
                } elseif ($taskScore < $minimum) {
                    $skills[$skill->student_id][$response->skilltask_id]['scoreType'] = 'F';
                } else {
                    $skills[$skill->student_id][$response->skilltask_id]['scoreType'] = '';
                }
            }
        }

        if (! empty($stepIds)) {
            $allSteps = \SkilltaskStep::whereIn('id', $stepIds)->get();
            $ordinals = $allSteps->lists('ordinal', 'id')->all();
            $steps    = $allSteps->lists('expected_outcome', 'id')->all();
        }

        // Grab the knowledge attempts !!!!

        return View::make('core::events.admin_report')->with([
            'event'           => $event,
            'written'         => $event->testattempts,
            'skills'          => $skills,
            'skillsWithSteps' => $data,
            'steps'           => $steps,
            'ordinals'        => $ordinals
        ]);
    }

    /**
     * Print a 1250 for the test event
     * @param  int $id
     * @return Response
     */
    public function print1250($id)
    {
        $event = Testevent::with('discipline')->findOrFail($id);

        if ($event) {
            $pdf = new FormPdf;
            return $pdf->adminReport($event);
        }
    }

    /**
     * End a test event
     * @param  int $id
     * @return Response
     */
    public function end($id)
    {
        $event = Testevent::with('facility', 'observer', 'knowledgeStudents', 'skillStudents')->find($id);

        // if observer, make sure this is their event
        $user = Auth::user();
        if ($user && $user->isRole('Observer')) {
            if ($user->userable_id != $event->observer_id) {
                Flash::danger('This event does not belong to you.');
                return Redirect::route('events.index');
            }
        }

        if ($event->end()) {
            Flash::success('Event has been ended.');
        } else {
            Flash::error('Error ending event, please contact Headmaster immediately.');
        }

        return Redirect::route('events.index');
    }

    /**
     * Print out the skilltest forms
     */
    public function printSkill($id, $studentId = null)
    {
        $event = Testevent::with(
            'skillattempts',
            'skillattempts.responses',
            'skillattempts.student',
            'skillattempts.skilltest.tasks',
            'skillattempts.skilltest.tasks.steps',
            'skillattempts.skilltest.tasks.setups'
        )->findOrFail($id);

        $pdf = new SkillPdf;
        return $pdf->skills($event, $studentId);
    }

    /**
     * Print out the verification report used for paper tests
     */
    public function printVerification($id)
    {
        $event = Testevent::findOrFail($id);

        $pdf = new FormPdf;
        return $pdf->verification($event);
    }

    /**
     * Print test confirmation letters for everyone scheduled in this event
     */
    public function printConfirmations($id)
    {
        $exams    = Exam::all()->lists('name', 'id')->all();
        $skills   = Skillexam::all()->lists('name', 'id')->all();
        $event    = Testevent::with('facility')->findOrFail($id);
        $students = $event->students;

        return View::make('core::events.print_confirmations')->with([
            'exams'    => $exams,
            'skills'   => $skills,
            'event'    => $event,
            'students' => $students,
            'facility' => $event->facility
        ]);
    }
}
