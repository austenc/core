<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Input;
use Lang;
use Redirect;
use Response;
use \Sorter;
use Request;
use Config;
use Session;
use \BBCode;
use \Skilltask;
use \Skilltest;
use \Skillexam;
use \Skillattempt;
use \Training;
use \SkilltaskResponse;
use \Flash;

class SkillsController extends BaseController
{

    protected $task;
    protected $skill;
    protected $response;

    public function __construct(Skilltest $skill, Skilltask $task, SkilltaskResponse $response)
    {
        $this->skill    = $skill;
        $this->task     = $task;
        $this->response = $response;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $skilltests = $this->skill->with('tasks', 'exam')
                            ->where('header', 'like', '%'.Input::get('search', null).'%')
                            ->orderBy(Input::get('sort', 'status'), Sorter::order())
                            ->paginate(Config::get('paginate.default'));

        $count['active'] = Skilltest::where('status', '=', 'active')->count();
        $count['draft'] = Skilltest::where('status', '=', 'draft')->count();
        $count['archived'] = Skilltest::where('status', '=', 'archived')->count();

        return View::make('core::skills.tests.index')->with([
            'skills' => $skilltests,
            'count'  => $count
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $skillexams = Skillexam::with([
            'tasks' => function ($query) {
                $query->where('status', 'active');
            }
        ])->get();
        
        // get info on active tasks per skillexam
        $skillexam_tasks = [];
        foreach ($skillexams as $exam) {
            $skillexam_tasks[$exam->id]['weights'] = [];
            $skillexam_tasks[$exam->id]['name'] = $exam->name;

            // foreach active task in the exam
            foreach ($exam->tasks as $task) {
                if (array_key_exists($task->weight, $skillexam_tasks[$exam->id]['weights'])) {
                    $skillexam_tasks[$exam->id]['weights'][$task->weight]++;
                } else {
                    $skillexam_tasks[$exam->id]['weights'][$task->weight] = 1;
                }
            }
        }

        return View::make('core::skills.tests.create')
            ->withTaskInfo($skillexam_tasks)
            ->withSkillexams($skillexams);
    }


    /**
     * Store a newly created skill test
     */
    public function store()
    {
        if ($this->skill->fill(Input::all())->validate()) {
            $skill_id = $this->skill->addWithInput();
            return Redirect::route('skills.edit', $skill_id)->withSuccess('Skill Test created');
        }

        return Redirect::back()->withInput()->withErrors($this->skill->errors);
    }


    /**
     * Display a skillattempt record
     */
    public function show($id)
    {
        $attempt = Skillattempt::with([
            'skillexam',
            'skilltest.tasks.steps',
            'student',
            'testevent.discipline',
            'testevent.facility',
            'testevent.observer',
            'testevent.proctor',
            'testevent.actor',
            'responses'
        ])->findOrFail($id);

        // Make sure students can only see their own attempt's detail
        $user = Auth::user();
        $adminUser = $user->ability(['Admin', 'Staff'], []);


        // INSTRUCTOR LOGGED IN
        //  (make sure this is one of their students)
        if ($user->isRole('Instructor')) {
            $ownedStudents = $user->userable->students;

            // check instructor owns some students
            //  (if this isn't one of the owned students, show warning)
            if ($ownedStudents) {
                if (! in_array($attempt->student_id, $ownedStudents->lists('id')->all())) {
                    Flash::warning('You cannot view skill attempts for that ' . Lang::choice('core::terms.student', 1));
                    return Redirect::route('students.index');
                }
            }
        }
        // NON-INSTRUCTOR
        else {
            // Not this student's attempt
            //  (and not logged in as admin/staff or current instructor)
            if ($attempt->student_id != $user->userable->id && ! $adminUser) {
                Flash::warning('That skill attempt doesn\'t belong to you!');
                return Redirect::route('students.tests', $user->userable->id);
            }
        }

        // Make sure results are able to be shown for this attempt (if not admin)
        if (! $attempt->seeResults && ! $adminUser) {
            Flash::warning('Results are not yet available to view for this test.');
            return Redirect::route('students.tests', $attempt->student_id);
        }

        return View::make('core::skills.testing.show')->with([
            'attempt'       => $attempt,
            'taskResponses' => $attempt->responses->keyBy('skilltask_id')
        ]);
    }


    /**
     * Generates a skill test
     */
    public function generate()
    {
        // get skillexam
        $skillexamId = Input::get('skill_exam');
        if (empty($skillexamId)) {
            return Redirect::route('skills.index')->withDanger('Could not generate Skill Test -- missing Skill Exam ID.');
        }

        $skillexam = Skillexam::find($skillexamId);

        // if input old, get those task_ids and dont generate new
        if (Input::old('task_ids')) {
            $tasks = [];

            // now need to reorder them
            foreach (Input::old('task_ids') as $i => $task_id) {
                $tasks[] = Skilltask::find($task_id);
            }
        }
        // otherwise generate new tasks with weight str
        else {
            $tasks = $this->skill->generate($skillexamId, Input::get('task_weights'));
        }

        // any tasks generated?
        if (empty($tasks)) {
            return Redirect::route('skills.create')->withInput()->withDanger('You must enter a Test Generation string');
        }

        // setup task id string
        $task_ids = [];
        foreach ($tasks as $t) {
            $task_ids[] = $t->id;
        }

        return View::make('core::skills.tests.generate')->with([
            'skill'            => new Skilltest,
            'skillexam'        => $skillexam,
            'tasks'            => $tasks,
            'task_ids'        => $task_ids
        ]);
    }

    /**
     * Save As (clone) a skill test
     */
    public function saveAs($id)
    {
        $skilltest = Skilltest::with('tasks')->find($id);
        $skillexam = Skillexam::find($skilltest->skillexam_id);

        // clone the skill test
        $new_skill = clone $skilltest;
        $new_skill->parent_id = $id;
        // cloned tasks
        $tasks = $new_skill->tasks;

        if (Input::old('task_ids')) {
            $tasks = [];

            // now need to reorder them
            foreach (Input::old('task_ids') as $i => $task_id) {
                $tasks[] = Skilltask::find($task_id);
            }
        }

        return View::make('core::skills.tests.generate')->with([
            'skill'     => $new_skill,
            'skillexam' => $skillexam,
            'tasks'     => $tasks
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $skill = Skilltest::with('tasks', 'tasks.steps', 'exam')->find($id);

        return View::make('core::skills.tests.edit')->with([
            'skill' => $skill,
            'exam'  => $skill->exam,
            'tasks' => $skill->tasks
        ]);
    }

    /**
     * Removing a single Task from a Skill Test
     */
    public function removeTask($id, $task_id)
    {
        if (Request::ajax()) {
            $skill = Skilltest::find($id);

            // remove the task
            $skill->tasks()->detach($task_id);

            $rem_tasks = $skill->tasks()->get();

            // adjust ordinals
            foreach ($rem_tasks as $i => $task) {
                $task->pivot->ordinal = ($i + 1);
                $task->pivot->save();
            }

            return Response::json(true);
        }

        return Response::json(false);
    }

    /**
     * Popup for adding a new active task to a skill test
     */
    public function addTask($id='')
    {
        // list of task ids that will not be returned
        $disabledTaskIds = [];

        $skillexamId = Input::get('id');
        $excludedIds = Input::get('exclude');
        
        // if a skill id was passed, get all tasks for that skill test
        if (! empty($id)) {
            $skill = Skilltest::with('tasks')->find($id);

            // disable the current tasks
            $disabledTaskIds = $skill->tasks->lists('id')->all();

            // disable each tasks enemies
            foreach ($disabledTaskIds as $tid) {
                $task = Skilltask::with('enemies')->find($tid);

                $disabledTaskIds = array_merge($disabledTaskIds, $task->enemies->lists('id')->all());
            }
        }

        if ($excludedIds) {
            // get all url excluded ids 
            $excludedIds = array_map('intval', explode(',', $excludedIds));

            // diff of disabled and excluded (to get only ids that have not yet had their enemies looked up)
            $diffIds = array_diff($excludedIds, $disabledTaskIds);

            // get enemies for url excluded ids (ids that are not in db yet)
            foreach ($diffIds as $id) {
                $task = Skilltask::with('enemies')->find($id);

                // add the current task (is not in db yet) and all current tasks enemies
                $disabledTaskIds = array_merge($disabledTaskIds, [$task->id], $task->enemies->lists('id')->all());
            }
        }

        $skillexam = Skillexam::with([
            'tasks' => function ($query) use ($disabledTaskIds) {
                $query->where('status', 'active');
                if (! empty($disabledTaskIds)) {
                    $query->whereNotIn('id', $disabledTaskIds);
                }
                $query->orderBy('weight', 'ASC');
                $query->orderBy('title', 'ASC');
            }
        ])->find($skillexamId);

        return View::make('core::skills.tests.modals.add_task')->withTasks($skillexam->tasks);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $skill = Skilltest::find($id);

        if ($skill->updateWithInput()) {
            return Redirect::route('skills.edit', $id)->with('success', 'Skill Test updated.');
        }
        
        Session::flash('danger', 'There was an error updating Skill Test.');
        return Redirect::back()->withInput();
    }

    public function activate($id)
    {
        $skill = Skilltest::with('tasks')->find($id);
        
        // get all tasks, ensure all are active
        foreach ($skill->tasks as $task) {
            if ($task->status != "active") {
                return Redirect::back()
                                ->withInput()
                                ->withDanger('Skill Test may not be activated -- it contains a non-active Task!');
            }
        }

        $skill->status = 'active';
        $skill->save();

        return Redirect::route('skills.edit', $id)->with('success', 'Skill Test activated.');
    }

    /**
     * Setup a Skill Test (to record student progress)
     */
    public function initialize($attempt_id)
    {
        $attempt = Skillattempt::with('testevent')->find($attempt_id);

        if ($attempt && $attempt->testevent) {
            if ($attempt->setupAndStart()) {
                // Take them to beginning of the test
                return Redirect::route('skills.in_progress');
            }

            return Redirect::back()->withInput()->withDanger('There was an error starting the Skill Test. Please contact Headmaster immediately.');
        }

        return Redirect::to('/')->withDanger('There was an error initializing the Skill Test. Please contact HeadMaster immediately.');
    }

    /**
     * Scoring (doublecheck) a finished Skill Test
     */
    public function scoring($attempt_id)
    {
        dd('scoring attempt '.$attempt_id);
    }

    /**
     * A current Skill Test in progress
     *   - typically the Proctor
     *   - $current is current task #
     */
    public function inProgress($current = 1)
    {
        $totalTasks = count((array) Session::get('skills.testing.tasks'));
        $attemptId = Session::get('skills.testing.attempt_id');
        Session::put('skills.current', $current);
        
        if (empty($attemptId)) {
            return Redirect::to('/')->withDanger('Error locating Skilltest Attempt, please contact HeadMaster, LLP immediately.');
        }

        // make sure current is within bounds
        if ($current < 1) {
            return Redirect::route('skills.in_progress', 1);
        }
        if ($current > $totalTasks && $totalTasks > 0) {
            return Redirect::route('skills.in_progress', $total);
        }

        // get ID of question from session's list
        $taskId = Session::get('skills.testing.tasks.'.$current);

        // couldn't find question, error and redirect home
        if (empty($taskId)) {
            return Redirect::to('/')->withDanger('Error locating Skilltest Task, please contact HeadMaster, LLP immediately.');
        }

        // get the skillattempt record (for this current task)
        $attempt = Skillattempt::with(['responses' => function ($query) use ($taskId) {
            $query->where('skilltask_id', '=', $taskId);
        }, 'skilltest', 'student'])->find($attemptId);

        // get any previous response record
        $response = $attempt->responses->first();

        // get the response data (as array)
        $decodedResponse = $response ? $response->decoded_response : null;

        // combine previous response data with parsed BBCode here?
        $inputData = [];
        if ($decodedResponse) {
            foreach ($decodedResponse as $stepId => $stepResponse) {
                // any input response data?
                if (isset($stepResponse['data'])) {
                    foreach ($stepResponse['data'] as $inputResponse) {
                        $inputData[$inputResponse->field] = $inputResponse->value;
                    }
                }
            }
        }

        // Is this skillattempt valid to be testing?
        if ($attempt->status != 'started') {
            return Redirect::to('/')->withWarning('Invalid Skill Test status.');
        }

        // get all skilltest tasks (so we can page it)
        $skillTasks = Skilltest::with('tasks')->find($attempt->skilltest_id);
        $testTasks = Skilltask::whereIn('id', $skillTasks->tasks->lists('id')->all())->paginate(1);

        // get the current task along with steps, setups, and responses (if any exist)
        $task = Skilltask::with(['steps' => function ($query) {
            $query->orderBy('ordinal', 'ASC');
        }, 'setups'])->find($taskId);


        // mashup every step with any previous recorded response data
        $stepsWithInput = $task->steps->map(function ($step) use ($inputData) {
            $step->expected_outcome = BBCode::parseInput($step->expected_outcome, $step->id, 'web', $inputData);
            return $step;
        });

        return View::make('core::skills.testing.index')
            ->withSkill($attempt->skilltest)
            ->withStudent($attempt->student)
            ->withResponse($response)
            ->withData($decodedResponse)
            ->withAttempt($attempt)
            ->withCurrent($current)
            ->withTotal($totalTasks)
            ->withTask($task)
            ->withAllTasks($testTasks)
            ->withSteps($stepsWithInput)
            ->withSetups($task->setups);
    }

    /**
     * Save progress on a Skill Test
     */
    public function save()
    {
        if ($this->response->saveTask()) {
            // task ordinal #
            $current    = Input::get('current');
            $attemptId  = Input::get('attempt_id');
            $directNav  = Input::get('directNav');
            // determine which button clicked
            $prev        = Input::has('prev');
            $next        = Input::has('next');
            $end        = Input::has('end');
            $directNav  = Input::has('directNav');

            // if there are skills.errors, do validation again!
            if (Session::has('skills.errors') || Session::has('skills.general_errors')) {
                $attempt = Skillattempt::with([
                    'skilltest',
                    'skilltest.tasks',
                    'skilltest.tasks.setups',
                    'student',
                    'responses'
                ])->find($attemptId);

                $attempt->validate();
            }

            // end test
            if ($end) {
                return Redirect::route('skills.end', $attemptId);
            }
            // next task
            if ($next) {
                return $this->navigate($current+1);
            }
            // prev task
            if ($prev) {
                return $this->navigate($current-1);
            }
            // direct task nav
            if ($directNav) {
                return $this->navigate(Input::get('directNav'));
            }

            // default nav to first task
            return $this->navigate(1);
        }

        return Redirect::back()->withInput()->withErrors('Unable to save Skilltest progress.');
    }

    /**
     * End a skill test
     */
    public function end($attemptId)
    {
        $attempt = Skillattempt::with([
            'skilltest',
            'skilltest.tasks',
            'skilltest.tasks.setups',
            'student',
            'student.user',
            'responses'
        ])->find($attemptId);

        $tasks = Skilltask::whereIn('id', $attempt->skilltest->tasks->lists('id')->all())->paginate(1);

        if (is_null($attempt)) {
            return Redirect::to('/');
        }

        if (Request::isMethod('post')) {
            // If we clicked on a number, process the form submit as if it were a direct nav
            $directNav = Input::get('directNav');
            if (is_numeric($directNav) && $directNav > 0) {
                // Save the 'anomalies' into the session in case there's errors on skill test
                Session::set('skills.anomalies', Input::get('anomalies'));

                // Go to the correct task #
                return $this->inProgress($directNav);
            }

            $validated = $attempt->validate();
            
            if ($validated === true) {
                // preliminary scoring, create pending score to be double checked, send notifications, ..
                $attempt->finish();

                return Redirect::route('events.edit', $attempt->testevent_id)
                    ->withSuccess('Successfully ended '.Lang::choice('core::terms.student', 1).' '.$attempt->student->full_name.'\'s Skill attempt.');
            }

            return Redirect::back()->withInput();
        }

        return View::make('core::skills.testing.end')
            ->withAttempt($attempt)
            ->withStudent($attempt->student)
            ->withTasks($tasks)
            ->withSkill($attempt->skilltest);
    }


    /**
     * Navigating a test via prev/next or jump
     * @param  int $to 	where to navigate
     * @return Response
     */
    private function navigate($to, $messages=[])
    {
        // # of questions on test
        $total = count(Session::get('skills.testing.tasks'));

        // default to one for any out of range
        if ($to < 1) {
            $to = 1;
        } elseif ($to > $total) {
            $to = $total;
        }

        if (Request::ajax()) {
        }

        // flash any messages
        foreach ($messages as $k => $v) {
            Session::flash($k, $v);
        }

        return Redirect::route('skills.in_progress', $to);
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
}
