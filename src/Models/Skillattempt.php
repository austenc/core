<?php namespace Hdmaster\Core\Models\Skillattempt;

use Session;
use DateTime;
use Input;
use Event;
use \Skilltest;
use \Skillexam;
use \Student;
use \Testevent;
use \SkilltaskResponse;
use \SkilltaskStep;
use \Proctor;
use \User;
use \Pendingscore;
use \Attemptable;
use \Facility;
use \StudentTraining;

use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;

class Skillattempt extends \Eloquent implements StaplerableInterface
{

    // use the attemptable trait to bring in several relations
    use Attemptable, EloquentTrait;

    protected $morphClass = 'Skillattempt';
    protected $fillable = [
        'image',
        'hold'
    ];

    protected $dontKeepRevisionOf = [
        'start_time',
        'end_time',
        'created_at',
        'updated_at'
    ];

    /**
     * Constructor
     */
    public function __construct($attributes = [])
    {
        $this->hasAttachedFile('image', [
            'keep_old_files' => true,
            'url' => '/system/skill/:attachment/:id_partition/:style/:filename'
        ]);

        parent::__construct($attributes);
    }
    /**
     * Boot up the model and specific trait methods
     */
    public static function boot()
    {
        parent::boot();
        static::bootStapler();
        static::bootAttemptable();
    }

    /**
     * RELATIONS
     */
    public function skilltest()
    {
        return $this->belongsTo(Skilltest::class);
    }

    public function skillexam()
    {
        return $this->belongsTo(Skillexam::class);
    }

    // responses
    public function responses()
    {
        return $this->hasMany(SkilltaskResponse::class);
    }

    public function failedResponses()
    {
        return $this->hasMany(SkilltaskResponse::class)->where('skilltask_responses.status', 'failed');
    }

    public function testevent()
    {
        return $this->belongsTo(Testevent::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * A skillattempt has one associated instructor
     * Who was the instructor during the time the student tested?
     */
    public function studentTraining()
    {
        return $this->belongsTo(StudentTraining::class);
    }

    /**
     * A skillattempt has one associated test site
     * Where did the student take this test?
     * Info can also be gathered from testevent but for reporting purposes this duplication helps
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Gets failed tasks for a given skill attempt
     */
    public function failedTasks()
    {
        $responses = $this->failedResponses()->get();

        // If there are no responses, nothing to return
        if ($responses->isEmpty()) {
            return null;
        }

        // If we made it this far, list task id's from the failed responses
        $taskIds = $responses->lists('skilltask_id')->all();

        return \Skilltask::with('steps')->whereIn('id', $taskIds)->get();
    }

    /**
     * A list of failed STEPs associated with this attempt
     * @return  array
     */
    public function failedSteps()
    {
        $stepsByTask = [];
        $responses = $this->responses()->get();

        // If there are no responses, nothing to return
        if ($responses->isEmpty()) {
            return [];
        }

        // get all tasks from the responses and all steps for those tasks
        $taskIds = $responses->lists('skilltask_id')->all();
        $steps   = \SkilltaskStep::with('task')->whereIn('skilltask_id', $taskIds)->get();

        // Grab all steps not completed
        foreach ($responses as $r) {
            $task    = $r->task;
            $answers = $r->decodedResponse;
            foreach ($answers as $stepId => $a) {
                // if the step wasn't completed, add this to the list
                if (array_key_exists('completed', $a) && $a['completed'] == false) {
                    $taskId = $r->skilltask_id;
                    $step   = $steps->find($stepId);

                    if ($step) {
                        $stepsByTask[$taskId]['task']    = $task->title;
                        $stepsByTask[$taskId]['steps'][] = $step->expected_outcome;
                    }
                }
            }
        }

        return $stepsByTask;
    }

    /**
     * Setup a Skillattempt in preparation for recording Student progress
     */
    public function setupAndStart()
    {
        // make sure this attempt hasnt already been scored
        if (in_array($this->status, ['scored', 'passed', 'failed'])) {
            return false;
        }

        // forget any previous session errors
        Session::forget('skills');    // clear errors and testing indexes

        // get the skilltest w/ tasks (and setups/steps)
        $skill = Skilltest::with(['tasks' => function ($query) {
            $query->orderBy('skilltest_tasks.ordinal', 'ASC');
        }, 'tasks.steps', 'tasks.setups'])->find($this->skilltest_id);

        // check the skilltest is found
        if (! $skill->exists) {
            return false;
        }

        // make it 1-based now
        $taskIds = $skill->tasks->lists('id')->all();
        array_unshift($taskIds, '');
        unset($taskIds[0]);

        // if attempt has already started, validate so we can show current errors
        if ($this->status === 'started') {
            $this->validate();
        }

        // set attempt as started
        $this->start_time = date('Y-m-d H:i:s');
        $this->status     = 'started';
        $this->save();

        // save skilltest tasks to session
        Session::put('skills.testing.tasks', $taskIds);
        // add the attempt_id so we know which one to track
        Session::put('skills.testing.attempt_id', $this->id);
        // start on first page
        Session::put('skills.current', 1);

        return true;
    }

    /**
     * Validation for a finished (END TEST) skilltest
     */
    public function validate()
    {
        // clear any previous session skill errors
        Session::forget('skills.errors');
        Session::forget('skills.general_errors');    // timestamps, affidavit, etc.. (things on end page)

        $errors     = [];
        $gen_errors = [];    // end page errors (affidavit, timestamps)

        // ensure affidavit is checked
        if (! Input::get('affidavit')) {
            $gen_errors[] = 'You must check the Affidavit.';
        }

        // check date
        $currDate = Input::get('curr_date');
        if (empty($currDate)) {
            $gen_errors[] = 'Enter a Test Date';
        }

        // check times
        $startTime = Input::get('start_time');
        $endTime   = Input::get('end_time');
        if (empty($startTime)) {
            $gen_errors[] = 'Enter a Start Time';
        }
        if (empty($endTime)) {
            $gen_errors[] = 'Enter an End Time';
        }

        // check skillattempt status (watch for invalid status; should be STARTED)
        if ($this->status !== 'started') {
            $gen_errors[] = 'Invalid attempt status';
        }

        // all task ids in current skilltest
        $tasks = $this->skilltest->tasks;
        $taskIds = $this->skilltest->tasks->lists('id')->all();

        // used to check we have a response for each task in the test
        $matchedTaskIds = [];
        foreach ($tasks as $t) {
            $matchedTaskIds[$t->pivot->ordinal] = $t->id;
        }

        // loop thru all task responses
        if ($this->responses->count() > 0) {
            foreach ($this->responses as $i => $response) {
                // if the current task ran out of time, ignore validation
                $outOfTime = Session::get('skills.out_of_time.' . $response->skilltask_id);
                if ($outOfTime == true) {
                    continue;
                }

                // get current task
                $currTask = $this->skilltest->tasks->find($response->skilltask_id);

                // check task was found in skilltest tasks
                if (! in_array($response->skilltask_id, $taskIds)) {
                    $errors[$currTask->pivot->ordinal][] = 'Response not found.';
                }

                // does current task require a setup?
                if ($currTask->setups->count() > 0 && $response->setup_id == null) {
                    $errors[$currTask->pivot->ordinal][] = 'Missing setup.';
                }
                
                // find matching response for skilltask id, remove it (we have the response for this task)
                if (($key = array_search($response->skilltask_id, $matchedTaskIds)) !== false) {
                    unset($matchedTaskIds[$key]);
                }


                // response input data
                $taskResponse = (array) json_decode($response->response);

                // loop thru task steps, checking each steps input data
                foreach ($taskResponse as $stepId => $stepResponse) {
                    $inputCount['textbox'] = 1;
                    $inputCount['radio'] = 1;
                    $inputCount['dropdown'] = 1;

                    // lookup step record
                    $step = SkilltaskStep::with('inputs')->find($stepId);

                    if (is_null($step)) {
                        continue;
                    }

                    // showing errors w task/step ordinals
                    $errorPrefix = '<strong>Step #'.$step->ordinal.'</strong>: ';

                    // step not completed! did they leave a comment?
                    if ($stepResponse->completed == 0 && empty($stepResponse->comment)) {
                        $errors[$currTask->pivot->ordinal][$stepId][] = $errorPrefix.'Missing comment.';
                    }

                    // check if step has inputs (especially for sneaky missing radio/checkbox inputs)
                    if ($step->inputs->count() > 0) {
                        foreach ($step->inputs as $inputField) {
                            // setup expected name of input field (in response)
                            $expectedInputName = $stepId.'-'.$inputField->type.'-'.$inputCount[$inputField->type];

                            // if input response has data
                            if (! isset($stepResponse->data)) {
                                $errors[$currTask->pivot->ordinal][$stepId][] = $errorPrefix.ucfirst($inputField->type).' input must be selected.';
                                continue;
                            }

                            // look at submitted data (answers)
                            $foundInput = false;
                            $inputValue = '';
                            foreach ($stepResponse->data as $d) {
                                if ($expectedInputName == $d->field) {
                                    // found user input for this step input!
                                    $inputValue = $d->value;
                                    $foundInput = true;
                                }
                            }

                            // did we find matching input?
                            if ($foundInput !== true) {
                                $errors[$currTask->pivot->ordinal][$stepId][] = $errorPrefix.'Missing expected '.$expectedInputName.' '.$inputField->type.' input.';
                            }

                            // is the input non-default?
                            switch ($inputField->type) {
                                case 'textbox':
                                    // textbox inputs must not be null!
                                    if (empty($inputValue)) {
                                        $errors[$currTask->pivot->ordinal][$stepId][] = $errorPrefix.'Textbox input must not be empty.';
                                    }
                                    $inputCount['textbox']++;
                                    break;
                                case 'radio':
                                    // if we found matching input (we did), then radio was choosen, good enough!
                                    $inputCount['radio']++;
                                    break;
                                case 'dropdown':
                                    if ($inputValue == '-1') {
                                        $errors[$currTask->pivot->ordinal][$stepId][] = $errorPrefix.'Dropdown input must be selected.';
                                    }
                                    $inputCount['dropdown']++;
                                    break;
                                default:
                                    $errors[$currTask->pivot->ordinal][$stepId][] = $errorPrefix.'Unknown input type';
                            }
                        } // end FOREACH step input
                    } // end IF step has input
                } // end FOREACH step input
            } // end FOREACH responses
        } // end IF responses
        else {
            // no responses found!
            $gen_errors[] = 'No Task responses found.';
        }

        // check if theres any remaining taskIds (if remaining, we are missing a response for a task!)
        if (! empty($matchedTaskIds)) {
            foreach ($matchedTaskIds as $ord => $matchedTaskId) {
                $outOfTime = Session::get('skills.out_of_time.' . $matchedTaskId);

                // only if they didn't run out of time
                if ($outOfTime != true) {
                    $errors[$ord][] = 'Missing Response.';
                }
            }
        }

        Session::put('skills.errors', $errors);
        Session::put('skills.general_errors', $gen_errors);

        return empty($errors) && empty($gen_errors) ? true : false;
    }

    /**
     * Saves the start/end timestamps for a skill test
     */
    public function saveTimestamps()
    {
        $currDate  = Input::get('curr_date');
        $startTime = Input::get('start_time');
        $endTime   = Input::get('end_time');

        // check valid start date&time
        $startDateObj = DateTime::createFromFormat('m/d/Y', $currDate);
        $startTimeObj = DateTime::createFromFormat('h:i a', $startTime);
        if (is_object($startTimeObj) && $startDateObj && $startDateObj->format('m/d/Y') == $currDate) {
            $this->start_time = $startDateObj->format('Y-m-d').' '.$startTimeObj->format('H:i:s');
            $this->save();
        }

        // check valid end date&time
        $endDateObj = DateTime::createFromFormat('m/d/Y', $currDate);
        $endTimeObj = DateTime::createFromFormat('h:i a', $endTime);
        if (is_object($endTimeObj) && $endDateObj && $endDateObj->format('m/d/Y') == $currDate) {
            $this->end_time = $endDateObj->format('Y-m-d').' '.$endTimeObj->format('H:i:s');
            $this->save();
        }

        return true;
    }

    /**
     * Finalize a skillattempt
     */
    public function finish()
    {
        $this->status     = 'unscored';
        $this->anomalies  = Input::get('anomalies');
        $this->end_time   = date('Y-m-d H:i:s'); // end time is today
        $this->saveTimestamps();
        $this->save();

        // remove anomalies from session if set
        Session::forget('skills.anomalies');

        // do some preliminary scoring
        return $this->score();
    }

    /**
     * Update task responses to match a a pipe-delimited string like 1,A|2,B|3,A...
     * In the case of no answer and/or no-shows, they come in like 1,@|2,@|3,@...
     * where 1 = step # on this skilltest
     * and   A = choice from ABCDE
     */
    public function updateAnswersFromPiped($string, $paper = false)
    {
        // grab all tasks (with steps) for this skilltest
        $skilltest = Skilltest::with(['tasks', 'tasks.steps', 'tasks.responses' => function ($query) {
            $query->where('skilltask_responses.skillattempt_id', '=', $this->id);
        }])->find($this->skilltest_id);
        $tasks = $skilltest->tasks;

        // loop through each task, make an array like
        // stepID => taskID
        $tasksByStep = [];
        $stepIds     = [];
        foreach ($tasks as $task) {
            foreach ($task->steps as $step) {
                $stepIds[]              = $step->id;
                $tasksByStep[$step->id] = $task->id;
            }
        }
            
        // make a list like from string:
        // stepID => ['completed' => true/false]
        $stepUpdateInfo = [];

        // did they not have any responses?
        $firstCompletedOnly = false;
        $foundResponse = false;
        foreach (explode('|', $string) as $k => $response) {
            $split = explode(',', $response);
            $q     = array_get($split, 0); // question number like 1,2,3,4
            $a     = array_get($split, 1); // choice like ABCDE

            // if this is the first response and it's an 'A' mark, might be 100%
            if ($k === 0 && $a == 'A') {
                $firstCompletedOnly = true;
            }

            // A == passed, B == failed
            // if there was a 'B' mark, they missed this one
            if ($a == 'B') {
                $completed = false;
                $foundResponse = true;

                // we found a 'B' mark, so this obviously isn't 100%
                $firstCompletedOnly = false;
            } else {
                // in the case of '@', there was no answer
                if ($a == '@') {
                    $completed = -1;
                } else {
                    // empty = 'A' mark, they completed this step
                    $completed = true;
                }

                // if there was an 'A' mark, and it's not '@', that's some sort of response
                if (! empty($a) && $a != '@') {
                    $foundResponse = true;
                }
            }

            // array[stepID] => ['completed' => $completed, 'comment' => '']
            $stepUpdateInfo[array_get($stepIds, $k)] = [
                'comment'   => '',
                'completed' => $completed
            ];
        }

        // If there were no responses found, this should be a noshow!
        // - mark attempt status
        // - clear out completed array (in below foreach)
        if ($foundResponse === false) {
            $this->status = 'noshow';
        }

        // If it IS a noshow, but now has answers, it should be unscored
        if ($foundResponse === true && $this->status == 'noshow') {
            $this->status = 'unscored';
        }

        // loop through the steps, building a final 'update' array like:
        // taskID => [stepID => true, step2ID => false]
        $tasksToUpdate = [];
        foreach ($stepUpdateInfo as $stepId => $response) {
            // If no response was found above, make sure to get rid of them all
            if ($foundResponse === false) {
                $response['completed'] = -1;
            } elseif ($firstCompletedOnly === true) {
                // If only first 'A' mark and nothing else, this is 100%
                $response['completed'] = true;
            }

        

            if (array_key_exists($stepId, $tasksByStep)) {

                // if this is coming in from the paper API and there are no responses, don't create any
                if ($paper === true && $foundResponse === false) {
                    unset($tasksToUpdate[$tasksByStep[$stepId]]);
                } else {
                    // [taskId][$stepId] => response
                    $tasksToUpdate[$tasksByStep[$stepId]][$stepId] = $response;
                }
            }
        }

        // loop through the final array, updating each task with json encoded version of steps
        $allSaved = true;
        if (! empty($tasksToUpdate)) {

            // Go through each task we need to update and merge the responses
            foreach ($tasksToUpdate as $taskId => $stepResponses) {
                $taskResponse  = $tasks->find($taskId)->responses->first();

                // if there's no SkilltaskResponse record, create one
                if ($taskResponse === null) {
                    $taskResponse = SkilltaskResponse::create([
                        'skillattempt_id' => $this->id,
                        'skilltask_id'    => $taskId,
                        'student_id'      => $this->student_id
                    ]);
                }

                // Do we have an existing task response?
                if (! empty($taskResponse->response)) {
                    // Yes, merge the response with existing response so we keep comments and variable inputs
                    $current        = json_decode($taskResponse->response, true);
                    $new            = $stepResponses;
                    $mergedResponse = [];

                    // go through each new response and merge it with the existing one if needed					
                    foreach ($new as $stepId => $newResponse) {

                        // if we don't have a response for this stepId, then just use the new one
                        if (! array_key_exists($stepId, $current)) {
                            $mergedResponse[$stepId] = $newResponse;
                        } else {
                            // if we DO have a response, use it as a basis, but use the new completed field
                            $mergedResponse[$stepId] = $current[$stepId];

                            // updated the completed key with new value for this step
                            if (array_key_exists('completed', $newResponse)) {
                                $mergedResponse[$stepId]['completed'] = $newResponse['completed'];
                            }
                        }

                        // if there were no responses at all, don't mark completed
                        if ($foundResponse === false) {
                            $mergedResponse[$stepId]['completed'] = -1;
                        }
                    }

                    // set response as merged version
                    $taskResponse->response = json_encode($mergedResponse);
                } else {
                    // Just set the response
                    $taskResponse->response = json_encode($stepResponses);
                }
                
                // if a fask fails to save, we've got an error
                if (! $taskResponse->save()) {
                    $allSaved = false;
                }
            }
        }

        // if we're updating a paper attempt, make sure to set the end_time
        if ($paper === true) {
            $this->end_time = date('Y-m-d H:i:s');
        }

        // save the attempt
        $this->save();

        return $allSaved;
    }

    /**
     * Score the current skillattempt
     *
     * boolean - whether the skillattempt's own status should be updated or not
     */
    public function score($updateAttempt = false)
    {
        // Is this a no-show? Nothing to do here...
        if ($this->status == 'noshow') {
            return $this->status;
        }
        
        // Keep track of status / score for each task, keyed by taskID
        $performance = [];

        // Grab all tasks on this test with steps
        $test  = Skilltest::with('tasks', 'tasks.steps')->find($this->skilltest_id);
        $tasks = $test->tasks;

        // Get collection of all the student responses
        $responses = $this->responses()->get();

        // Loop through the tasks
        foreach ($tasks as $t) {

            // Grab the response to this particular task 
            $response = $responses->filter(function ($r) use ($t) {
                return $r->skilltask_id == $t->id;
            })->first();

            // if no response, they've failed the task, continue to next
            if (! $response) {
                SkilltaskResponse::create([
                    'skillattempt_id' => $this->id,
                    'skilltask_id'    => $t->id,
                    'student_id'      => $this->student_id,
                    'status'          => 'failed',
                    'score'           => 0
                ]);
                continue;
            }
            
            //	Grab the individual step responses
            $steps = $response->stepResponses;

            // Track a few things for each task
            $totalSteps  = 0;
            $missedSteps = 0;
            $missedKey   = 0;
            $status      = '';

            // Loop through the steps for each task, checkking:
            // -if they got at least 80% and didn't miss any key steps = passed
            // -else mark the task response as failed
            foreach ($t->steps as $s) {
                // is this step in our responses array?
                if (array_key_exists($s->id, $steps)) {
                    $stepCompleted = $steps[$s->id]['completed'];

                    // did the person miss this step?
                    if ($stepCompleted !== true) {
                        // add this to the list of missed steps
                        $missedSteps++;

                        // is this a key step?
                        if ($s->is_key) {
                            $missedKey++;
                        }
                    }

                    // increment our total
                    $totalSteps++;
                }
            }

            // Get the final percentage of steps completed and mark performance
            $score = (($totalSteps - $missedSteps) / $totalSteps) * 100;

            // Did they fail this task by missing a key or getting less than minimum?
            if ($missedKey > 0 || $score < 80) {
                $status = 'failed';
            } else {
                // mark the task response as PASSED
                $status = 'passed';
            }

            // update the response
            $response->status = $status;
            $response->score  = $score;
            $response->save();
        } // end foreach tasks

        $failed    = $this->failedTasks();
        $numFailed = $failed ? $failed->count() : 0;
        $status    = $numFailed > 0 ? 'failed' : 'passed';

        // should we update the attempt status too? only if they're not a noshow
        if ($updateAttempt === true && $this->status != 'noshow') {
            $this->status = $status;
            $this->save();
        }
        
        return $status;
    }
}
