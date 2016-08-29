<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Input;
use Session;
use Redirect;
use Request;
use Response;
use Flash;
use \Testattempt;
use \Skillattempt;
use \Testitem;
use \Subject;

class TestingController extends BaseController
{

    /**
     * Testing Results letter for a passed / failed test
     */
    public function results($type, $attemptId)
    {
        // Get the main attempt as below in 'confirm'
        $type       = ucfirst($type);
        $class      = $type == 'Knowledge' ? 'Testattempt' : $type . 'attempt';
        $otherClass = str_contains($class, 'Skill') ? 'Testattempt' : 'Skillattempt';

        // Do the two classes exist in php?
        if (! class_exists($class) || ! class_exists($otherClass)) {
            return Redirect::back();
        }

        // Grab the test attempt
        // Look for any other attempt type and get model instance as well
        $exam         = $type == 'Knowledge' ? 'exam' : 'skillexam';
        $otherExam    = $type == 'Knowledge' ? 'skillexam' : 'exam';
        $attempt      = $class::with('testevent', 'testevent.facility', 'student', $exam)->find($attemptId);
        $otherAttempt = $otherClass::with('testevent', 'testevent.facility', 'student', $otherExam)
            ->where('testevent_id', $attempt->testevent_id)
            ->where('student_id', $attempt->student_id)
            ->first();

        $user = Auth::user();

        // If this a student make sure they can't access somebody else's
        if ($user->isRole('Student')) {

            // is this the student's own test?
            if ($user->userable_id !== $attempt->student_id) {
                Flash::danger('You cannot access that result.');
                return Redirect::to('/');
            }

            // Make sure results are able to be shown for this attempt
            if (! $attempt->seeResults) {
                Flash::warning('Results are not yet available to view for this test.');
                return Redirect::route('students.tests', $user->userable->id);
            }
        }

        // figure out which one is knowledge and which one is skill
        if (str_contains(class_basename($attempt), 'Skill')) {
            $skill     = $attempt;
            $knowledge = $otherAttempt;
        } else {
            $skill     = $otherAttempt;
            $knowledge = $attempt;
        }

        $kMin     = null;
        $sMin     = null;
        $subjects = null;
        $totals   = null;
        $vocab    = null;
        $tasks    = null;
        $steps    = [];

        // If there's a KNOWLEDGE test
        if ($knowledge) {
            // Grab the testform / testplan so we can get a minimum score
            $form = \Testform::with('testplan')->findOrFail($knowledge->testform_id);
            $plan = $form->testplan;

            // only the subjects from this test
            $itemsBySubject = $plan->itemsBySubjectMapped;
            $subjects   = \Subject::whereIn('id', array_keys($itemsBySubject))->get();
            $totals     = $plan->itemsBySubjectMapped;

            // minimum score for knowledge
            $kMin = $plan->minimum_score;

            $vocab = $knowledge->failedVocab();
        }

        // There's a SKILL test
        if ($skill) {
            // Grab the minimum from the skill testform
            $sMin = \Skilltest::findOrFail($skill->skilltest_id)->minimum;

            // Get any failed tasks / steps
            $tasks = $skill->failedTasks();
            $steps = $skill->failedSteps();
        }

        return View::make('core::testing.results_letter')->with([
            'knowledge' => $knowledge,
            'skill'     => $skill,
            'student'   => $attempt->student,
            'event'     => $attempt->testevent,
            'exam'      => $attempt->$exam->name,
            'kMin'      => $kMin,
            'sMin'      => $sMin,
            'subjects'  => $subjects,
            'totals'    => $totals,
            'vocab'     => $vocab,
            'tasks'     => $tasks,
            'steps'     => $steps
        ]);
    }

    /**
     * Confirmation page for a scheduled test
     */
    public function confirm($type, $attemptId)
    {
        $type  = ucfirst($type);
        $class = $type == 'Knowledge' ? 'Testattempt' : $type . 'attempt';

        if (! class_exists($class)) {
            return Redirect::to('/');
        }

        $exam = $type == 'Knowledge' ? 'exam' : 'skillexam';
        $attempt = $class::with('testevent', 'testevent.facility', 'student', $exam)->find($attemptId);

        // does this attempt belong to the student?
        $user = Auth::user();
        if ($user && $user->isRole('Student')) {
            if ($attempt->student_id != $user->userable_id) {
                Flash::warning('You do not have permission to access this page');
                return Redirect::to('/');
            }
        }

        return View::make('core::testing.confirm')->with([
            'exam'  => $attempt->$exam->name,
            'event' => $attempt->testevent,
            'f'     => $attempt->testevent->facility,
            's'     => $attempt->student
        ]);
    }

    /**
     * Re-send email confirmation for a scheduled test
     */
    public function confirmEmail($type, $attemptId)
    {
        $type  = ucfirst($type);
        $class = $type == 'Knowledge' ? 'Testattempt' : $type . 'attempt';

        if (! class_exists($class)) {
            return Redirect::to('/');
        }

        $exam    = $type == 'Knowledge' ? 'exam' : 'skillexam';
        $attempt = $class::with('testevent', 'student', $exam)->find($attemptId);
        $event   = $attempt->testevent;

        // send email notification
        $event->sendScheduleEmail(strtolower($type), $attempt->student, $attempt->$exam);

        Flash::success('Test confirmation email re-sent to ' . $attempt->student->commaName . '.');
        return Redirect::route('events.edit', $attempt->testevent_id);
    }

    /**
     * A test in progress, the 'take test' page
     * @return Response
     */
    public function index($current = 1)
    {
        $total     = count((array) Session::get('testing.questions'));
        $attemptId = Session::get('testing.attempt_id');

        // If no attempt ID, redirect to the homepage
        if (empty($attemptId)) {
            return Redirect::to('/');
        }

        // make sure current is within bounds
        if ($current < 1) {
            return Redirect::route('testing.index', 1);
        }

        if ($current > $total && $total > 0) {
            return Redirect::route('testing.index', $total);
        }

        // get ID of question from session's list
        $itemId = Session::get('testing.questions.'.$current);

        // couldn't find question, error and redirect home
        if (empty($itemId)) {
            Redirect::to('/')->withDanger('Error locating test question, please contact Headmaster, LLP immediately.');
        }

        $attempt = Testattempt::with('exam', 'testform.testplan', 'student', 'student.adas')->find($attemptId);

        // Is this test attempt valid to be testing?
        if ($attempt->status != 'started') {
            return Redirect::route('testing.show', $attempt->id)->withWarning('Invalid test status.');
        }

        return View::make('core::testing.index')
            ->withCurrent($current)
            ->withTimeRemaining($attempt->timeRemaining)
            ->withRemaining($this->questionsRemaining())
            ->withExam($attempt->exam)
            ->withTotal($total)
            ->withStudent($attempt->student)
            ->withBookmarks(Session::get('testing.bookmarks'))
            ->withAnswered(Session::get('testing.answers'))
            ->withQuestion(Testitem::with(['distractors' => function ($query) {
                $query->orderBy('distractors.ordinal');
            }])->find($itemId));
    }

    public function show($id)
    {
        $attempt = Testattempt::with([
            'testform.testplan',
            'exam',
            'student',
            'testevent.facility',
            'testevent.observer',
            'testevent.proctor',
            'testevent.actor',
            'testevent.discipline'
        ])->findOrFail($id);

        // Make sure students can only see their own attempt's detail
        $user = Auth::user();
        $adminUser = $user->ability(['Admin', 'Staff'], []);

        // if this is an instructor, make sure this is one of their students!
        if ($user->hasRole('Instructor')) {
            $ownedStudents = $user->userable->students;

            // if instructor owns some students
            if ($ownedStudents) {

                // if this isn't one of the owned students, show warning
                if (! in_array($attempt->student_id, $ownedStudents->lists('id')->all())) {
                    Flash::warning('You cannot view test attempts for that ' . Lang::choice('core::terms.student', 1));
                    return Redirect::route('students.edit', $attempt->student_id);
                }
            }
        }

        // make sure the attempt belongs to this student OR user is admin/staff
        if ($attempt->student_id != $user->userable->id && ! $adminUser) {
            // Not this student's attempt, and not logged in as admin
            Flash::warning('That test attempt doesn\'t belong to you!');
            return Redirect::route('students.tests', $user->userable->id);
        }

        // Make sure results are able to be shown for this attempt if not admin
        if (! $attempt->seeResults && ! $adminUser) {
            Flash::warning('Results are not yet available to view for this test.');
            return Redirect::route('students.tests', $user->userable->id);
        }

        // Get the subjects we need to display
        $subjectIds = array_keys((array) $attempt->correct_by_subject);
        $subjects   = Subject::whereIn('id', $subjectIds)->get();

        // Grab the testplan and totals by subject (mapped to report_as if applicable)
        $plan = $attempt->testform->testplan;
        $totals = $plan->itemsBySubjectMapped;

        return View::make('core::testing.show')->with([
            'attempt'  => $attempt,
            'totals'   => $totals,
            'subjects' => $subjects
        ]);
    }

    /**
     * Show the demographic confirmation page and start code entry form
     * @return [type] [description]
     */
    public function start($attempt_id)
    {
        // Is this a valid test attempt?
        $attempt = Testattempt::with('student', 'student.user')->find($attempt_id);

        // If no attempt, we have nothing to do here...
        if (! $attempt) {
            return Redirect::to('/')->withDanger('No test attempt found matching that ID.');
        }

        // Make sure this test can be started
        if ($attempt->status == 'started') {
            return Redirect::route('testing.index');
        } elseif ($attempt->status != 'pending') {
            return Redirect::route('testing.show', $attempt->id)->withWarning('That test cannot be started.');
        }

        return View::make('core::testing.start')
            ->withStudent($attempt->student)
            ->withAttempt($attempt);
    }

    /**
     * Resume a test in progress
     */
    public function resume($attempt_id)
    {
        $attempt = Testattempt::find($attempt_id);

        if ($attempt->exists && $attempt->setupAndStart()) {
            // Get the last answered question and start there
            $answers   = Session::get('testing.answers');
            $questions = (array) Session::get('testing.questions');
            $at        = 1; // start at question 1 default

            if (is_array($answers)) {
                end($answers); // set pointer to end
                $questionId = key($answers); // question ID of last answer

                // get the last answer's question # (1-based)
                $questions = array_flip($questions);
                if (array_key_exists($questionId, $questions)) {
                    $at = $questions[$questionId];
                }
            }
            return Redirect::route('testing.index', $at);
        }

        Flash::warning('Test attempt could not be resumed.');
        return Redirect::route('students.tests');
    }

    /**
     * Submit the test start form to here, check the start code, setup the test, etc...
     */
    public function initialize($attempt_id)
    {
        $attempt = Testattempt::with('testevent')->find($attempt_id);
        $startcode = Input::get('startcode');

        // If already started, take them to test immediately
        if ($attempt->status == 'started') {
            return Redirect::route('testing.index');
        }

        // if no startcode inform them
        if (empty($startcode)) {
            return Redirect::back()->withDanger('The start code is required.');
        }


        if ($attempt && $attempt->testevent && $startcode) {
            if (strtolower($startcode) == strtolower($attempt->testevent->start_code)) {
                // Start codes match, initialize the test and redirect
                // Init test
                if ($attempt->setupAndStart()) {
                    // Take them to beginning of the test
                    return Redirect::route('testing.index');
                } else {
                    return Redirect::back()->withInput()->withDanger('There was an error starting the test. Please contact Headmaster immediately.');
                }
            } else {
                // start code didn't match
                return Redirect::back()->withInput()->withDanger('Please enter the correct start code to begin.');
            }
        }


        return Redirect::to('/')->withDanger('There was an error initializing the test attempt. Please contact HeadMaster immediately.');
    }

    /** 
     * Save the posted answer
     * @return mixed
     */
    public function save()
    {
        $answer    = Input::get('answer');
        $current   = Input::get('current');
        $attemptId = Session::get('testing.attempt_id');

        // The test attempt
        $attempt   = Testattempt::find($attemptId);
        $user      = Auth::user();

        if (! $attempt || ! $user) {
            Flash::danger('There was an error saving your test attempt.');
            \Log::info('attempts.save - not found error');
            \Log::info($attempt);
            \Log::info($user);
            return Redirect::to('/');
        }

        // Does this attempt match the logged in student?
        if (! ($user->userable_type == 'Student' && $attempt->student_id == $user->userable_id)) {
            Flash::danger('Action not allowed.');
            return Redirect::to('/');
        }

        // Save this person's answer (if there is one) to the session first!
        if ($answer) {
            // Get the question ID
            $question = Session::get('testing.questions.'.$current);

            // Put the answer into the session, keyed by testitem id!
            if ($question) {
                Session::put('testing.answers.'.$question, $answer);
            }
        }

        // Session variables
        $questions    = Session::get('testing.questions');
        $answered     = Session::get('testing.answers');

        // save answers in database
        $attempt->answers = json_encode($answered);
        $attempt->save();


        // Is there any time left?
        if ($attempt->timeRemaining <= 0) {
            // No time left!
            $attempt->stopTest();
            // Redirect to the score detail page!
            return Redirect::route('testing.show', $attempt->id)->withWarning('Testing time expired.');
        }

        // Bookmarks!
        $attempt->updateBookmarks();

        // Now determine which button clicked
        $prev = Input::has('prev');
        $next = Input::has('next');
        $jump = Input::get('jump_to');
        $stop = Input::get('end-test');

        // Where is the tester headed next?
        if ($next) {
            return $this->navigate($current+1);
        } elseif ($prev) {
            return $this->navigate($current-1);
        } elseif ($stop) {
            // Stop the test
            $attempt->stopTest();
            // Redirect to the score detail page!
            Flash::info('Your answers have been submitted. Please check back later to see your results.');
            return Redirect::route('students.tests', $attempt->student_id);
        } else {

            // If they submitted an actual jump-to #
            if ($jump) {
                // jump sent
                return $this->navigate($jump);
            } else {
                
                // Any NOT answered questions?
                $unanswered = array_diff($questions, array_flip($answered));
                
                // rewind pointer to first element
                reset($unanswered);

                if (! empty($unanswered)) {
                    // gives key from array pointer
                    return $this->navigate(key($unanswered));
                }

                // there must be answers for all of them
                return $this->navigate(end($questions), [
                    'warning' => ['You have answered all the questions, please review your answers.']
                ]);
            }
        }
    }

    /**
     * Navigating a test via prev/next or jump
     * @param  int $to 	where to navigate
     * @return Response
     */
    private function navigate($to, $messages=[])
    {
        // # of questions on test
        $total = count(Session::get('testing.questions'));

        // default to one for any out of range
        if ($to < 1) {
            $to = 1;
        } elseif ($to > $total) {
            $to = $total;
        }

        if (Request::ajax()) {
            $itemId = Session::get('testing.questions.'.$to);
            $item = Testitem::with('distractors')->find($itemId);

            // Found a testitem corresponding to this jump value
            if ($item) {
                return Response::json([
                    'stem'          => $item->stem,
                    'distractors'   => $item->distractors->lists('content')->all(),
                    'distractorIds' => $item->distractors->lists('id')->all(),
                    'current'       => $to,
                    'total'         => $total,
                    'remaining'     => $this->questionsRemaining(),
                    'bookmarks'     => Session::get('testing.bookmarks'),
                    'response'      => Session::get('testing.answers.'.$item->id),
                    'messages'      => $messages
                ]);
            } else {
                // Didn't find an item corresponding to this jump value, go to 1
            }
        }  // end ajax

        // flash any messages
        foreach ($messages as $k => $v) {
            Session::flash($k, $v);
        }

        return Redirect::route('testing.index', $to);
    }

    private function questionsRemaining()
    {
        $remaining = [];

        $questions = Session::get('testing.questions');
        $answers   = Session::get('testing.answers');

        if (is_array($questions) && is_array($answers)) {
            $remaining = array_diff($questions, array_keys($answers));
        }

        // just return 1, 2, 3, etc... not the actual id's
        return array_keys($remaining);
    }
}
