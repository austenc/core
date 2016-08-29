<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Event;
use Redirect;
use \Flash;
use \Pendingscore;
use \Student;
use \Testattempt;
use \Skillattempt;
use \Skilltask;
use stdClass;
use Request;

class ScoresController extends BaseController
{

    private $chars = [
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D',
        5 => 'E'
    ];

    // used for the review process
    protected $person             = null;
    protected $knowledgeAnswers   = null;
    protected $knowledgeChoices   = null;
    protected $itemIds            = null;
    protected $skillChoices       = null;
    protected $stepIds            = null;
    protected $stepComments       = [];
    protected $keyStepIds         = null;
    protected $pendingKnowledgeId = null;
    protected $pendingSkillId     = null;
    protected $knowledgeScore     = null;
    protected $knowledgeStatus    = null;
    protected $event              = null;
    protected $kHistory           = null;
    protected $sHistory           = [];
    protected $knowledge        = null;
    protected $skill            = null;
    protected $tasksByStep      = [];
    protected $ordinals         = [];
    protected $setups           = [];
    protected $taskNames        = [];

    // show a list of pending scores
    public function pending()
    {
        // get all student ID's attached to ANY Pendingscore
        // grab all student records
        $allPending = Pendingscore::with('scoreable', 'scoreable.student')->get();
        $kStudents  = $sStudents = $pendingScores = $excludeSkills = [];

        // loop through and build a list of each type of attempt
        // this is so we can 
        foreach ($allPending as $pending) {
            // is this a knowledge test?
            if ($pending->scoreable_type == Testattempt::class) {
                $kStudents[$pending->scoreable->student_id] = $pending;
            }

            // or is it a skill test?
            if ($pending->scoreable_type == Skillattempt::class) {
                $sStudents[$pending->scoreable->student_id] = $pending;
            }
        }

        // loop through knowledge, building main array of objects
        foreach ($kStudents as $studentId => $pending) {

            // create a new object to hold the pending scores related to this person
            $p            = new stdClass;
            $p->student   = $pending->scoreable->student->commaName;
            $p->knowledge = $pending->id;
            $p->type      = 'Knowledge';
            $p->skill     = null;

            // do they have a skill attempt too?
            if (array_key_exists($studentId, $sStudents)) {
                // append skill to the type string
                $p->type .= ', Skill';

                // the pending skill id
                $p->skill        = $sStudents[$studentId]->id;
                $excludeSkills[] = $studentId;
            }

            // add the object to our array we'll use in the view
            $pendingScores[$studentId] = $p;
        }

        // now loop through skills in case there are any skill-only
        foreach ($sStudents as $studentId => $pending) {
            // make sure we haven't handled this above (person has K and S)
            if (! in_array($studentId, $excludeSkills)) {
                $p            = new stdClass;
                $p->student   = $pending->scoreable->student->commaName;
                $p->knowledge = null;
                $p->skill     = $pending->id;
                $p->type      = 'Skill';

                $pendingScores[$studentId] = $p;
            }
        }

        return View::make('core::scores.pending')->with([
            'pendingScores' => $pendingScores
        ]);
    }

    /**
     * Review a single scanform
     * @param  int $first  a pendingscore ID
     * @param  int $second a pendingscore ID (optional)
     * @return Response
     */
    public function review($first, $second = null)
    {
        // prep the answers for both possible test types
        $this->prepAnswers($first);

        // only try prepping answers if we have a second pending score ID
        if ($second !== null) {
            $this->prepAnswers($second);
        }

        // is this a no-show?
        $noshow = false;
        if ($this->knowledge && $this->knowledge->status == 'noshow') {
            $noshow = true;
        }
        if ($this->skill && $this->skill->status == 'noshow') {
            $noshow = true;
        }
        if ((empty($this->knowledgeChoices) && empty($this->skillChoices))) {
            $noshow = true;
        }

        if ($noshow === true) {
            Flash::warning('It looks like this person might be a no-show for at least one test. If this is true click "Accept Answers" below to confirm.', 'Warning');
        }

        // return the review page with appropriate vars
        return View::make('core::scores.review')->with([
            'person'           => $this->person,
            'event'            => $this->event,
            'kChoices'         => $this->knowledgeChoices,
            'kAnswers'         => $this->knowledgeAnswers,
            'sChoices'         => $this->skillChoices,
            'stepIds'          => $this->stepIds,
            'stepComments'     => $this->stepComments,
            'keyStepIds'       => $this->keyStepIds,
            'itemIds'          => $this->itemIds,
            'pendingKnowledge' => $this->pendingKnowledgeId,
            'pendingSkill'     => $this->pendingSkillId,
            'knowledgeScore'   => $this->knowledgeScore,
            'knowledgeStatus'  => $this->knowledgeStatus,
            'kHistory'         => $this->kHistory,
            'sHistory'         => $this->sHistory,
            'knowledge'        => $this->knowledge,
            'skill'            => $this->skill,
            'tasksByStep'      => $this->tasksByStep,
            'taskNames'        => $this->taskNames,
            'ordinals'         => $this->ordinals,
            'setups'           => $this->setups
        ]);
    }

    /**
     * Prep answers for a pending score
     * @param  int $pending
     * @return void
     */
    private function prepAnswers($pendingId)
    {
        // make sure we have an actual numeric ID to work with
        if (! is_numeric($pendingId)) {
            return null;
        }

        // find the pending score record matching this pendingscores.id
        $pending     = Pendingscore::find($pendingId);
        $attempt     = new $pending->scoreable_type;
        $attempt     = $attempt::withRescheduled()->with('student', 'testevent', 'testevent.facility')->find($pending->scoreable_id);
        $this->event = $attempt->testevent;

        // KNOWLEDGE
        if ($pending->scoreable_type === Testattempt::class) {
            // load some other relations
            $attempt->load('testform', 'testform.testplan', 'testform.testitems', 'testform.testitems.distractors');

            // handle the knowledge attempt info and vars
            $this->pendingKnowledgeId = $pending->id;
            $this->knowledgeAnswers   = $this->knowledgeAnswers($attempt->testform_id);
            $this->knowledgeChoices   = $attempt->answersAlpha;
            $this->knowledgeScore     = $attempt->percent;
            $this->knowledgeStatus    = $attempt->percent >= $attempt->testform->testplan->minimum_score ? 'passed' : 'failed';
            $this->person             = $attempt->student;

            // Revision history?
            $this->kHistory           = $attempt->revisionHistory;

            $this->knowledge = $attempt;
        } elseif ($pending->scoreable_type === Skillattempt::class) {
            // SKILL
            // Pending SKILL score
            // load some other relations for skillattempt
            $attempt->load('responses', 'skilltest', 'skilltest.tasks');

            $this->pendingSkillId = $pending->id;

            // get a list of all step ID's and key steps
            $taskIds = $attempt->skilltest->tasks->lists('id')->all();
            if ($taskIds) {
                $tasks = $attempt->skilltest->tasks;
                $tasks->load('steps');

                $this->taskNames = $tasks->lists('title', 'id')->all();

                // get step id's, key step id's, tasks keyed by step_id, and ordinals keyed by step_id for whole test
                foreach ($tasks as $task) {
                    // stepId's in a 0-based array (array-merge reorders keys)
                    $this->stepIds     = array_merge((array) $this->stepIds, $task->steps->lists('id')->all());

                    // key steps in a 0-based array (array-merge reorders keys)
                    $this->keyStepIds  = array_merge((array) $this->keyStepIds, $task->steps->lists('is_key', 'id')->all());

                    // the task title, keyed by step_id
                    foreach ($task->steps as $s) {
                        $this->tasksByStep[$s->id] = $task->title;
                    }

                    // the ordinal values, keyed by step_id
                    $this->ordinals    = (array) $this->ordinals + $task->steps->lists('ordinal', 'id')->all(); // union
                }
            }

            $stepResponses = [];

            reset($attempt->responses);
            $firstResponseKey = key($attempt->responses);

            // for each response, add each step response to a master array like stepID => true/false 
            foreach ($attempt->responses as $key => $response) {
                // grab the setup used for this task
                if (! empty($response->setup_id)) {
                    $this->setups[$response->skilltask_id] = \DB::table('skilltask_setups')->where('id', $response->setup_id)->first();
                }

                // get the list of step responses from this task, merge it with our other array
                $steps = $response->decodedResponse;

                // is this the first response? (used for checking if 100%)
                $isFirstResponse = $key == $firstResponseKey;

                if (is_array($steps)) {

                    // Grab the first element
                    reset($steps);
                    $firstKey = key($steps);

                    foreach ($steps as $stepId => $step) {
                        if (is_array($step)) {
                            
                            // Did they complete the step?
                            if (array_key_exists('completed', $step)) {
                                $stepComplete     = $step['completed'];
                                $completeNotEmpty = $stepComplete !== -1 && $stepComplete == true;

                                // if this is the first key and step completed, 
                                // we want to give it an actual 'A', otherwise null
                                $aMark = ($isFirstResponse && $stepId === $firstKey && $completeNotEmpty) ? 'A' : null;

                                // mark it as 'B' if they didn't complete the step
                                $letter                 = $stepComplete ? $aMark : 'B';
                                $stepResponses[$stepId] = $letter;
                            }
                        
                            // Is there a comment?
                            if (array_key_exists('comment', $step) && ! empty($step['comment'])) {
                                $this->stepComments[$stepId] = $step['comment'];
                            }
                        }
                    } // end foreach

                    // If there are any 'B' marks, get rid of the first 'A' mark
                    if (in_array('B', $stepResponses)) {
                        reset($stepResponses);
                        $key = key($stepResponses);

                        if (current($stepResponses) == 'A') {
                            $stepResponses[$key] = null;
                        }
                    }
                }

                // Does this task/step response have any revisions?
                if ($response->revisionHistory) {
                    // if it's null, make sure it's an array at least
                    if ($this->sHistory === null) {
                        $this->sHistory = [];
                    }

                    $this->sHistory[] = $response->revisionHistory;
                }
            }

            // we have a skill attempt, process it / set answer vars!
            $this->skillChoices = empty($stepResponses) ? null : $stepResponses;
            $this->person       = $attempt->student;
            $this->skill        = $attempt;
        } // elseif Skillattempt
    }

    /**
     * Gets knowledge test answers like ABCABACDDEA
     */
    private function knowledgeAnswers($testformId)
    {
        $items            = \Testform::with('testitems', 'testitems.distractors')->find($testformId)->testitems;
        $knowledgeAnswers = [];
        $answerIds        = $items->lists('answer', 'id')->all();
        $chars            = $this->chars;
        $this->itemIds    = $items->lists('id')->all();

        foreach ($answerIds as $itemId => $distractorId) {
            $char = array_get($chars, $items->find($itemId)->distractors->find($distractorId)->ordinal);
            $knowledgeAnswers[$itemId] = $char;
        }

        return $knowledgeAnswers;
    }

    /**
     * Update knowledge and/or skill attempt answers
     */
    public function update()
    {
        $pendingK = Input::get('pending_knowledge');
        $pendingS = Input::get('pending_skill');
        $kAnswers = Input::get('items');
        $sAnswers = Input::get('steps');

        // update the answers
        if ($pendingK !== null) {
            // process pending knowledge test and update the answers
            $pk = Pendingscore::find($pendingK);
            if ($pk) {
                $attempt = new $pk->scoreable_type;
                $attempt = $attempt::withRescheduled()->find($pk->scoreable_id);

                // parse the knowledge answers into a pipe-delimited string to easily save them as if it were an API call
                if (! empty($kAnswers)) {
                    $answers = [];
                    $i = 1;
                    $foundAnswer = false;

                    // Make an array of values like 1,2
                    foreach ($kAnswers as $a) {
                        $answers[$i] = $i.','.$a;

                        // if we found any kind of answer, it's not a no-show
                        if (! empty($a)) {
                            $foundAnswer = true;
                        }

                        $i++;
                    }

                    $piped = implode('|', $answers);
                    $updated = $attempt->updateAnswersFromPiped($piped);

                    // if they're marked as a noshow, but there are answers marked, mark as unscored
                    if ($attempt->status == 'noshow' && $foundAnswer === true) {
                        $attempt->status = 'unscored';
                        $attempt->save();
                    } elseif ($foundAnswer === false) {
                        $attempt->status = 'noshow';
                        $attempt->save();
                    }

                    if ($updated) {
                        Flash::success('Updated knowledge answers.');
                    }
                }
            }
        }


        if ($pendingS !== null) {
            // process / save the pending skill answers / steps
            $ps = Pendingscore::find($pendingS);

            if ($ps) {
                $attempt = new $ps->scoreable_type;
                $attempt = $attempt::withRescheduled()->find($ps->scoreable_id);

                // parse the skill answers into a pipe-delimited string to update like the API would
                if (! empty($sAnswers)) {
                    $answers = [];
                    $i = 1;
                    // make a 1-based array of their responses
                    foreach ($sAnswers as $a) {
                        $answers[$i] = $i.','.$a;
                        $i++;
                    }

                    $piped = implode('|', $answers);
                    $updated = $attempt->updateAnswersFromPiped($piped);

                    if ($updated) {
                        $attempt->score(true);
                        Flash::success('Updated skill answers.');
                    } else {
                        Flash::danger('Failed to update skill answers.');
                    }
                }
            }
        }
        
        // are we accepting them?
        if (Input::has('accept_answers')) {
            $bothHandled = false;

            // update knowledge record score / status
            if (isset($pk) && $pk) {
                // score one last time AND update the status
                $kAttempt = Testattempt::with('student')->find($pk->scoreable_id);
                $kAttempt->score();

                $name = $pk->scoreable->student->commaName;

                // If there's a skill attempt, fire combined event
                if (isset($ps) && $ps) {

                    // score the pending skill
                    $sAttempt =  Skillattempt::with('student')->find($ps->scoreable_id);
                    $sAttempt->score(true);


                    // Fire the 'both tests finished' event
                    Event::fire('student.finished_tests', [
                        'student' => $pk->scoreable->student,
                        'attempt' => $kAttempt,
                        'skill'   => $sAttempt
                    ]);
                    $bothHandled = true;
                } else {
                    // Otherwise, fire just finished_knowledge event
                    Event::fire('student.finished_knowledge', [
                        'student' => $pk->scoreable->student,
                        'attempt' => $kAttempt
                    ]);
                }

                // remove the pending knowledge score record
                $pk->delete();

                // if both were handled, delete the pending skill score too
                if ($bothHandled === true) {
                    $ps->delete();
                    Flash::success('Knowledge and Skill test answers accepted for ' . $name);
                } else {
                    Flash::success('Knowledge score accepted for ' . $name .'.');
                }
            }

            // update skill record / score / status
            // only if it wasn't already handled above!
            if (isset($ps) && $ps && $bothHandled === false) {
                $sAttempt = Skillattempt::find($ps->scoreable_id);
                $name = $ps->scoreable->student->commaName;

                // score skill AND UPDATE SKILLATTEMPT STATUS
                $sAttempt->score(true);
                
                // trigger finished skill test event
                // assuming if there's an associated knowledge attempt too,
                // it will have already been handled above this
                Event::fire('student.finished_skill', [
                    'student' => $ps->scoreable->student,
                    'attempt' => $sAttempt
                ]);

                // remove the record for pending score
                $ps->delete();

                Flash::success('Skill score accepted for ' . $name . '.');
            }

            return Redirect::route('scores.pending');
        }

        // Are we marking these are rescheduled?
        if (Input::has('mark_rescheduled')) {

            // Do we have a knowledge attempt?
            if (isset($pk) && $pk) {
                $this->markRescheduled($pk);
            }

            // Do we have a skill attempt?
            if (isset($ps) && $ps) {
                $this->markRescheduled($ps);
            }

            return Redirect::route('scores.pending');
        }

        $scores = [];

        // is there a pending knowledge?
        if (! empty($pendingK)) {
            $scores[] = $pendingK;
        }
        // is there a pending skill?
        if (! empty($pendingS)) {
            $scores[] = $pendingS;
        }

        return Redirect::route('scores.review', $scores);
    }

    /**
     * Mark an attempt as rescheduled
     */
    public function markRescheduled($pending)
    {
        // make sure we have a pending attempt and the associated scoreable
        if (empty($pending) || empty($pending->scoreable)) {
            return false;
        }

        $attempt = $pending->scoreable;
        $attempt->status = 'rescheduled';
        $saved = $attempt->save();
        
        if ($saved) {
            // Flash a message saying it was rescheduled, and delete the pending score
            Flash::info($attempt->getMorphClass() . ' has been marked as rescheduled.');
            $pending->delete();
        }

        return $saved;
    }

    /**
     * View what changed for a particular scoring revision
     */
    public function revision($id)
    {
        $revision = \Venturecraft\Revisionable\Revision::findOrFail($id);

        switch ($revision->revisionable_type) {
            case 'Testattempt':
                return $this->knowledgeRevision($id);
            break;

            case 'SkilltaskResponse':
                return $this->skillRevision($id);
            break;

            default:
                // do nothing
        }
    }

    /**
     * Show details for a knowledge attempt revision
     */
    private function knowledgeRevision($id)
    {
        $revision  = \Venturecraft\Revisionable\Revision::findOrFail($id);
        $attempt   = $revision->revisionable;
        $old       = $revision->oldValue();
        $new       = $revision->newValue();


        $old = is_array($old) || is_object($old) ? json_encode($old) : $old;
        $new = is_array($new) || is_object($new) ? json_encode($new) : $new;

        $attempt->answers = $old;
        $oldAlpha = $attempt->answersAlpha;

        $attempt->answers = $new;
        $newAlpha = $attempt->answersAlpha;

        $values = [];
        $items  = $attempt->testform->testitems;
        foreach ($items as $item) {
            $obj = new stdClass;

            $obj->old = is_array($oldAlpha) && array_key_exists($item->id, $oldAlpha) ? $oldAlpha[$item->id] : null;
            $obj->new = is_array($newAlpha) && array_key_exists($item->id, $newAlpha) ? $newAlpha[$item->id] : null;

            $values[] = $obj;
        }

        return View::make('core::scores.partials.knowledge_revision')->with([
            'values' => $values
        ]);
    }

    /**
     * Show details for a skill revision
     */
    private function skillRevision($id)
    {
        $revision  = \Venturecraft\Revisionable\Revision::findOrFail($id);
        $old       = json_decode($revision->oldValue());
        $new       = json_decode($revision->newValue());
        $task      = $revision->revisionable->task;
        
        // array to hold the new / old values
        $values = [];

        // get a list of all the step ID's for this test
        $stepIds     = [];
        foreach ($task->steps as $step) {
            $stepIds[] = $step->id;
        }

        // swap the values so step_id's are the keys
        foreach ($stepIds as $k => $stepId) {
            $obj    = new stdClass;
            $stepId = (string) $stepId;

            // is there an old value?
            if (property_exists($old, $stepId)) {
                $obj->old = $old->$stepId->completed == true ? 'A' : 'B';
            } else {
                $obj->old = null;
            }

            // is there a new value?
            if (property_exists($new, $stepId)) {
                $obj->new = $new->$stepId->completed == true ? 'A' : 'B';
            } else {
                $obj->new = null;
            }

            $values[$k] = $obj;
        }

        return View::make('core::scores.partials.skill_revision')->with([
            'values' => $values
        ]);
    }
}
