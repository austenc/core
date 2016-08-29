<?php namespace Hdmaster\Core\Models\Skilltest;

use Input;
use Validator;
use Flash;
use \Skilltask;
use \Skillexam;
use \Training;

class Skilltest extends \Eloquent
{

    protected $fillable = ['header', 'minimum', 'description', 'comments'];
    
    public $errors;
    
    protected $rules = [
        'task_ids'    => 'required',
        'minimum'    => 'required|integer',
        'header'    => 'required'
    ];


    // relations
    public function tasks()
    {
        return $this->belongsToMany(Skilltask::class, 'skilltest_tasks')->withPivot('ordinal')->orderBy('ordinal', 'ASC');
    }
    public function exam()
    {
        return $this->belongsTo(Skillexam::class, 'skillexam_id', 'id');
    }

    // scopes
    public function scopeActiveTests($query)
    {
        return $query->with('exam', 'exam.required_trainings')->where('status', '=', 'active');
    }
    
    public function archive()
    {
        $this->status      = 'archived';
        $this->archived_at = date('Y-m-d H:i:s');
        $this->save();

        Flash::success('Skill Test archived');
        return true;
    }

    public function addWithInput()
    {
        $test = new Skilltest;
        
        $test->skillexam_id = Input::get('skillexam_id');
        $test->header      = Input::get('header');
        $test->minimum     = Input::get('minimum');
        $test->status      = 'draft';
        $test->description = Input::get('description');
        $test->comments    = Input::get('comments');
        $test->save();

        // tasks
        $tasks = Input::get('task_ids');
        foreach ($tasks as $ordinal => $id) {
            $test->tasks()->attach($id, ['ordinal' => $ordinal]);
        }

        return $test->id;
    }

    public function updateWithInput()
    {
        $task_ids = Input::get('task_ids');

        //$test->skillexam_id
        $this->header = Input::get('header');
        $this->minimum = Input::get('minimum');
        $this->description = Input::get('description', null);
        $this->comments = Input::get('comments', null);
        $this->save();

        // delete all tasks
        $this->tasks()->sync([]);
        // load tasks (with ordinal)
        foreach ($task_ids as $ordinal => $task_id) {
            $this->tasks()->attach($task_id, ['ordinal' => $ordinal]);
        }

        return true;
    }

    /**
     * Generates a new Skilltest using a weight string
     */
    public function generate($skillexamId, $weightStrs)
    {
        if (empty($weightStrs)) {
            return false;
        }

        $skillexam = Skillexam::find($skillexamId);

        $total_tasks  = strlen($weightStrs);
        $task_weights = array_map("strtoupper", str_split($weightStrs));

        // must be at least one task
        if ($total_tasks < 1) {
            return false;
        }

        $curr_tasks         = [];
        $curr_test_task_ids = [];
        $curr_test_enemies  = [];

        // get all active tasks [id => weight]
        $tasks = $skillexam->tasks()->where('status', '=', 'active')->lists('weight', 'id')->all();

        foreach ($task_weights as $weight) {
            $count = 0;
            $foundTask = false;

            while (($count < count($tasks)) && ($foundTask === false)) {
                // get all active tasks with this $weight, select one random
                $possible_task_ids = array_keys($tasks, $weight);

                // no possible tasks with this weight? goto next... 
                // show alert?
                if (empty($possible_task_ids)) {
                    Flash::warning('Unable to locate eligible Task with weight '.$weight);
                    continue 2;
                }

                $taskIndex = array_rand($possible_task_ids);
                $taskId    = $possible_task_ids[$taskIndex];

                // check if task is in test yet? also check if in enemies array
                if ((! in_array($taskId, $curr_test_task_ids)) && (! in_array($taskId, $curr_test_enemies))) {
                    $curr_test_task_ids[] = $taskId;
                    $curr_tasks[]         = Skilltask::find($taskId);

                    // update test enemies
                    $task_enemies = array();        // get all enemies for the choosen task id
                    $test_enemies = array_merge($curr_test_enemies, $task_enemies);

                    $foundTask = true;
                }

                $count++;
            }
        }

        return $curr_tasks;
    }

    public function validate()
    {
        $rules = $this->rules;

        $validation = Validator::make(Input::get(), $rules);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }
}
