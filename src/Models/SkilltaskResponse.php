<?php namespace Hdmaster\Core\Models\SkilltaskResponse;

use Session;
use Input;
use Auth;
use \Student;
use \SkilltaskSetup;
use \Skilltask;
use \Skillattempt;

class SkilltaskResponse extends \Eloquent
{

    use \Venturecraft\Revisionable\RevisionableTrait;

    protected $morphClass = 'SkilltaskResponse';

    protected $fillable   = [
        'skillattempt_id',
        'skilltask_id',
        'student_id',
        'setup_id',
        'response',
        'status',
        'creator_type',
        'creator_id'
    ];

    public static function boot()
    {
        parent::boot();
        self::bootRevisionableTrait();
    }

    // relations
    public function setup()
    {
        return $this->belongsTo(SkilltaskSetup::class, 'skilltask_setups');
    }
    public function task()
    {
        return $this->belongsTo(Skilltask::class, 'skilltask_id');
    }
    public function attempt()
    {
        return $this->belongsTo(Skillattempt::class, 'skillattempt_id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // accessors
    public function getStepResponsesAttribute()
    {
        return $this->getDecodedResponseAttribute();
    }
    public function getDecodedResponseAttribute()
    {
        $decodedArray = [];
        $decoded = (array) json_decode($this->response);

        foreach ($decoded as $stepId => $obj) {
            $decodedArray[$stepId] = (array) $obj;
        }

        return $decodedArray;
    }

    /**
     * Determines the reason for a failed task (either key step or percentage too low)
     */
    public function getScoreReasonAttribute()
    {
        // if they didn't fail, nothing to do here
        if ($this->status != 'failed') {
            return '';
        }

        // From the above, we assume this is a failed response, so if the score
        // is at or above the minimum for this task, we assume a key step missed
        // otherwise, it's a percentage-based fail
        if ($this->task && $this->score >= $this->task->minimum) {
            return 'K';
        } else {
            return 'F';
        }
    }

    public function getStatusClassAttribute()
    {
        switch ($this->status) {
            case 'passed':
                return 'success';
            break;
            case 'failed':
                return 'danger';
            break;
            case 'pending':
                return 'warning';
            break;
            case 'assigned':
                return 'info';
            break;
            default:
                return '';
        }
    }

    /**
     * Gathers posted data and formats it keyed by stepId (preparation for json)
     */
    private function organizeTaskData()
    {
        // hold task response data (keyed by stepId)
        $stepResponses = [];

        // get all input pertaining to the step (input fields per step)
        $stepInput = Input::except([
            'check_all',
            'current',
            '_token',
            'next',
            'prev',
            'end',
            'directNav',
            'task_id',
            'attempt_id',
            'step_id',
            'step_comments',
            'step_completed',
            'setup_id',
            'response_id',
            'curr_date',
            'start_time',
            'end_time',
            'out_of_time'
        ]);

        // step ids
        $stepIds        = Input::get('step_id');
        // comments left by proctor about student step progress
        $stepComments    = Input::get('step_comments');
        // checked if student completed the step
        $stepCompleted    = Input::get('step_completed');

        // loop thru each step id (so we have record data for each step)
        foreach ($stepIds as $stepId) {
            $comment = array_key_exists($stepId, $stepComments) ? $stepComments[$stepId] : '';
            $completed = $stepCompleted && array_key_exists($stepId, $stepCompleted) ? true : false;

            // key by step id
            $stepResponses[$stepId]['comment']        = $comment;
            $stepResponses[$stepId]['completed']    = $completed;
        }

        // loop thru input fields (if the step has any input/bbcode)
        if (! empty($stepInput)) {
            foreach ($stepInput as $name => $val) {
                $nameData = explode('-', $name);
                $stepId = (int) trim($nameData[0]);
                
                $stepResponses[$stepId]['data'][] = ['field' => $name, 'value' => $val];
            }
        }

        return $stepResponses;
    }

    /**
     * Save Task response for a Skilltest
     */
    public function saveTask()
    {
        $user = Auth::user();

        // input
        $responseId = Input::get('response_id');        // id for previous response in db
        $taskId     = Input::get('task_id');
        $setupId    = Input::get('setup_id', null);        // selected setup
        $attemptId  = Input::get('attempt_id');
        $outOfTime  = Input::get('out_of_time');

        // Save this out of time value in the session
        Session::put('skills.out_of_time.' . $taskId, $outOfTime);
        
        // find skillattempt record
        $attempt = Skillattempt::find($attemptId);

        // update start end timestamps
        $attempt->saveTimestamps();
        
        // get step data (array will be json encoded and stored)
        $stepResponses = $this->organizeTaskData();

        // does a response already exist with given response ID? 
        $response = SkilltaskResponse::find($responseId);

        // No response found, does one exist for this task for this attempt? 
        // sometimes response_id won't be passed in (i.e. if coming from 'end test' page)
        // this was causing multiple responses to be created and the observer could never finish the test
        if (! $response) {
            $response = SkilltaskResponse::where('skillattempt_id', $attemptId)->where('skilltask_id', $taskId)->get()->first();
        }

        // update task response
        if ($response) {
            $response->setup_id     = $setupId;
            $response->response     = json_encode($stepResponses);
            $response->creator_id   = $user->userable_id;
            $response->creator_type = $user->userable_type;

            return $response->save();
        }
        
        // create task response
        SkilltaskResponse::create([
            'skillattempt_id'    => $attemptId,
            'skilltask_id'        => $taskId,
            'student_id'        => $attempt->student_id,
            'setup_id'            => $setupId,
            'response'            => json_encode($stepResponses),
            'status'            => 'pending',
            'creator_type'        => $user->userable_type,
            'creator_id'        => $user->userable_id
        ]);

        return true;
    }
}
