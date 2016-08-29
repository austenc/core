<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Session;
use Redirect;
use Request;
use Response;
use Flash;
use Config;
use \TaskPdf;
use \Skillexam;
use \Skilltask;
use \SkilltaskStep;
use \SkilltaskSetup;
use \Sorter;

class TasksController extends BaseController
{

    protected $skillTask;

    public function __construct(Skilltask $skillTask)
    {
        $this->skillTask = $skillTask;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $params                  = $this->skillTask->handleSearch();
        $params['searchTypes']   = Session::get('tasks.search.types');
        $params['searchQueries'] = Session::get('tasks.search.queries');
        $params['filter']        = Session::get('tasks.search.filter');

        // are there any tasks pending review?
        $tasksForReview = Skilltask::where('setup_review', true)->count();
        $stepsForReviewByTask = SkilltaskStep::where('vinput_review', true)->count();

        if ($tasksForReview > 0 || $stepsForReviewByTask > 0) {
            $params['tasksForReview'] = true;
        }

        return View::make('core::skills.tasks.index')->with($params);
    }

    public function search()
    {
        // get search parameters and save them to the session
        $query = Input::get('search');
        $type  = Input::get('search_type');

        if (! empty($query) && ! empty($type)) {
            // Push type and search terms to session
            Session::push('tasks.search.types', $type);
            Session::push('tasks.search.queries', $query);
        }

        return Redirect::route('tasks.index');
    }

    /**
     * Clear all seach terms and filters
     */
    public function searchClear()
    {
        Session::forget('tasks.search.types');
        Session::forget('tasks.search.queries');
        Session::forget('tasks.search.filter');
        Flash::info('Search cleared.');

        return Redirect::route('tasks.index');
    }

    /**
     * Remove a single search term
     */
    public function searchDelete($index)
    {
        Session::forget('tasks.search.types.'.$index);
        Session::forget('tasks.search.queries.'.$index);
        Flash::info('Search type removed.');

        return Redirect::route('tasks.index');
    }

    /**
     * Show a list of tasks needing either setup or variable input review
     */
    public function review()
    {
        // grab tasks and steps that need reviewing
        $tasksForReview = Skilltask::where('setup_review', true)->get();
        $stepsForReview = SkilltaskStep::where('vinput_review', true)->get();

        // if there are no tasks for review, redirect to tasks.index
        if ($tasksForReview->count() < 1 && $stepsForReview->count() < 1) {
            Flash::info('There are no tasks that need to be reviewed at this time.');
            return Redirect::route('tasks.index');
        }

        // key the steps by the skilltask_id
        $stepsForReview = $stepsForReview->keyBy('skilltask_id');

        $toReview = [];

        foreach ($tasksForReview as $task) {
            $toReview[$task->id]['setup'] = true;
        }

        foreach ($stepsForReview as $taskId => $step) {
            $toReview[$taskId]['steps'] = true;
        }

        return View::make('core::skills.tasks.review')->with([
            'toReview'  => $toReview,
            'taskNames' => Skilltask::whereIn('id', array_keys($toReview))->get()->keyBy('id')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::skills.tasks.create')->with([
            'task'            => new Skilltask,
            'skillexams'    => Skillexam::all(),
            'enemies'        => '',
            'setups'        => [],
            'steps'            => []
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if ($this->skillTask->fill(Input::all())->validate()) {
            $skillId = $this->skillTask->addWithInput();

            if ($skillId) {
                return Redirect::route('tasks.edit', $skillId)->with('success', 'Skill Task Added.');
            }
        }

        Session::flash('danger', 'There was an error creating the Skill Task.');
        return Redirect::back()->withInput()->withErrors($this->skillTask->errors);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    public function activate($id)
    {
        $step = Skilltask::find($id);

        $step->status = 'active';
        $step->save();

        return Redirect::route('tasks.edit', $id)->with('success', 'Skill Task activated.');
    }


    /**
     * Replace active Task before archiving
     */
    public function replace($id)
    {
        $task = Skilltask::find($id);

        return View::make('core::skills.tasks.replace')->with([
            'task'            => $task,
            'replace_tasks'    => Skilltask::ActiveTasks()
                                ->whereNotIn('id', [$id])
                                ->where('weight', '=', $task->weight)->get()
        ]);
    }

    /**
     * Enemies for a Skill Task shown in popup
     */
    public function enemies($id='')
    {
        $exclude_ids = [];

        if ($id) {
            // exclude the current task
            $task = Skilltask::with('enemies')->find($id);
            $exclude_ids[] = $task->id;

            // exclude the current tasks enemies
            $exclude_ids = array_merge($exclude_ids, $task->enemies->lists('id')->all());
        }

        // any additional tasks to exclude? (ie not in db but showing on page)
        $extra_ids = Input::get('exclude');

        if ($extra_ids) {
            $extra_ids = array_map('intval', explode(',', $extra_ids));

            $exclude_ids = array_unique(array_merge($exclude_ids, $extra_ids));
        }

        $all_tasks = empty($exclude_ids) ? Skilltask::all() : Skilltask::whereNotIn('id', $exclude_ids)->get();

        return View::make('core::skills.tasks.modals.enemies')->withTasks($all_tasks);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $task = Skilltask::with([
            'steps',
            'steps.inputs',
            'setups',
            'skillexams',
            'parent',
            'children'
        ])->find($id);

        if (empty($task)) {
            return Redirect::route('tasks.index')->withDanger('Unknown Task ID.');
        }

        $stepsToReview = false;
        foreach ($task->steps as $s) {
            if ($s->vinput_review == true) {
                $stepsToReview = true;
            }
        }

        return View::make('core::skills.tasks.edit')->with([
            'task'            => $task,
            'skillexams'    => Skillexam::all(),
            'enemies'        => ($task->status == 'draft') ? $task->enemyString : $task->enemyLinks,
            'setups'        => $task->setups,
            'steps'            => $task->steps,
            'stepsToReview' => $stepsToReview
        ]);
    }

    /**
     * Removes a setup from a task 
     */
    public function removeSetup($id)
    {
        if (Request::ajax()) {
            $setup = SkilltaskSetup::find($id);

            // remove the setup, return true
            SkilltaskSetup::destroy($setup->id);
            
            return Response::json(true);
        }

        return Response::json(false);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $task = Skilltask::find($id);

        if ($this->skillTask->fill(Input::all())->validate()) {
            if ($task->updateWithInput()) {
                return Redirect::route('tasks.edit', $task->id)->with('success', 'Skill Task updated.');
            }
        }
        
        Session::flash('danger', 'There was an error updating the Skill Task.');
        return Redirect::back()->withInput()->withErrors($this->skillTask->errors);
    }

    /**
     * Save As (clone) a skill task
     */
    public function saveAs($id)
    {
        $task = Skilltask::with([
            'skillexams',
            'steps',
            'steps.inputs',
            'setups',
            'enemies'
        ])->find($id);

        // clone the object
        $new_task = clone $task;
        $new_task->parent_id = $id;
        
        return View::make('core::skills.tasks.create')->with([
            'skillexams' => Skillexam::all(),
            'task'       => $new_task,
            'enemies'    => $new_task->enemyString,
            'setups'     => $new_task->setups,
            'steps'      => $new_task->steps
        ]);
    }


    /**
     * Print the specified task
     *
     * @param  int  $id
     * @return Response
     */
    public function printTask($id)
    {
        $task = Skilltask::with([
            'steps',
            'steps.inputs',
            'setups',
            'skillexams',
            'parent',
            'children'
        ])->find($id);

        // Make sure we have a valid task
        if (empty($task)) {
            return Redirect::route('tasks.index')->withDanger('Unknown Task ID.');
        }

        $pdf = new TaskPdf;
        return $pdf->task($task);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $task = Skilltask::find($id);

        if (empty($task)) {
            return Redirect::route('tasks.index');
        }

        if ($task->status != "draft") {
            return Redirect::back()->withDanger('Only draft Tasks may be deleted.');
        }

        Skilltask::destroy($id);

        return Redirect::route('tasks.index')->with('success', 'Skill Task #'.$id.' deleted.');
    }
}
