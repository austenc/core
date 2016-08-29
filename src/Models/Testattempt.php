<?php namespace Hdmaster\Core\Models\Testattempt;

use DateTime;
use Session;
use Input;
use Auth;
use Event;
use \Testform;
use \User;
use \Student;
use \Testevent;
use \Exam;
use \Pendingscore;
use \Attemptable;
use \Facility;
use \StudentTraining;
use \Subject;

use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;

class Testattempt extends \Eloquent implements StaplerableInterface
{

    // use the attemptable trait to bring in several relations
    use Attemptable, EloquentTrait;

    protected $morphClass = 'Testattempt';

    protected $fillable = [
        'start_time',
        'status',
        'score',
        'legacy_data',
        'end_time',
        'correct_answers',
        'total_questions',
        'is_oral',
        'correct_by_subject',
        'image',
        'hold'
    ];

    protected $dontKeepRevisionOf = [
        'start_time',
        'end_time',
        'created_at',
        'updated_at',
        'correct_by_subject',
        'score',
        'correct_answers',
        'total_questions',
    ];

    /**
     * Constructor
     */
    public function __construct($attributes = [])
    {
        $this->hasAttachedFile('image', [
            'keep_old_files' => true,
            'url' => '/system/knowledge/:attachment/:id_partition/:style/:filename'
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
     * A test attempt has one associated testform
     * @return Relation
     */
    public function testform()
    {
        return $this->belongsTo(Testform::class);
    }

    /** 
     * A test attempt has one associated testform
     * @return Relation
     */
    public function testevent()
    {
        return $this->belongsTo(Testevent::class);
    }

    /**
     * Who printed the attempt last
     */
    public function printedBy()
    {
        return $this->belongsTo(User::class, 'printed_by', 'user_id');
    }

    /**
     * A test attempt has one associated exam
     * @return Relation
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * A test attempt has one associated student training record
     * Who was the instructor and when did training finish?
     * Makes reporting easy..
     */
    public function studentTraining()
    {
        return $this->belongsTo(StudentTraining::class);
    }

    /**
     * A test attempt has one associated test site
     * Where did the student take this test?
     * Info can also be gathered from testevent but for reporting purposes this duplication helps
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }


    /**
     * Get failed vocab for an attempt
     */
    public function failedVocab()
    {
        $vocabList = [];

        if (empty($this->answers)) {
            $this->answers = '[]';
        }

        // Get testitems with vocab
        $items = $this->testform->testitems->load(['vocab']);

        // get the answers, keyed by testitems.id
        // testitems.id => distractor_id
        $answers = $items->lists('answer', 'id')->all();

        // the person's responses, keyed by testitems.id like 
        // testitems.id => distractors.id (choice)
        $responses = $this->answers;
        $totalCorrect = 0;

        // check each question
        foreach ($responses as $itemId => $answer) {
            // If they didn't answer, or got it wrong
            if (! isset($responses->$itemId) || $answer != $answers[$itemId]) {
                // wrong answer or no response, grab vocab from item
                $vocab = $items->find($itemId)->vocab;

                // If this item has vocab words
                if (! $vocab->isEmpty()) {
                    $vocabList = array_merge($vocabList, $vocab->lists('word')->all());
                }
            }
        }

        // If we have no responses, grab list from all items since they didn't answer anything
        if (empty($responses)) {
            foreach ($items as $i) {
                $vocabList = array_merge($vocabList, $i->vocab->lists('word')->all());
            }
        }

        return $vocabList;
    }

    /**
     * Checks if other test attempt is started
     */
    public function checkOtherStarted()
    {
        if ($this->exists && is_numeric($this->student_id)) {
            // get all test attempts for this student
            // that are STARTED and NOT this testattempt.id
            $tests = $this->where('student_id', '=', $this->student_id)
                ->where('status', '=', 'started')
                ->where('id', '!=', $this->id)->get();
            
            return ! $tests->isEmpty();
        }

        return false; // assume no tests are started
    }

    /**
     * Sets up the session for someone taking the knowledge test
     * @return boolean
     */
    public function setupAndStart()
    {
        // Make sure they don't have any other tests started!
        if ($this->checkOtherStarted()) {
            // Another test is started, nothing to do here.
            return false;
        }

        // is this a test attempt corresponding to a DB record?
        if ($this->exists && ! empty($this->testform_id)) {
            // setup questions array in session, question # keyed by ID, ordered as on testform
            $form = Testform::with(['testplan', 'testitems' => function ($query) {
                $query->orderBy('testform_testitem.ordinal', 'ASC');
            }])->find($this->testform_id);

            // make it 1-based now
            $items = $form->testitems->lists('id')->all();
            array_unshift($items, '');
            unset($items[0]);

            // Double-check that the # of items found equals the # of items on the testform
            // if(count($items) != array_sum((array) $form->testplan->items_by_subject))
            // 	die('# Items does not match testform!'. array_sum((array) $form->testplan->items_by_subject));
            if (! Session::has('testing.questions') && ! empty($items)) {
                Session::put('testing.questions', $items);
            }

            // Initialize the answers array
            $answers = empty($this->answers) ? array() : (array) $this->answers;
            Session::put('testing.answers', $answers);

            // For storing question bookmarks during the test
            Session::put('testing.bookmarks', []);

            // mark test start time in DB and put in session
            if (! $this->getOriginal('start_time')) {
                $this->start_time = date('Y-m-d H:i:s');
                $this->status     = 'started';
                $this->save();
            }

            // Put the start time in the session so we can check time left
            Session::put('testing.start_time', strtotime($this->start_time));

            // set session variable to disable the top menu for now
            Session::put('disable_menu', true);

            // Finally, add the attempt_id so we know which one to track
            Session::put('testing.attempt_id', $this->id);

            return true;
        }

        return false;
    }

    /**
     * Scores a given test attempt
     */
    public function score($updateStatus = true)
    {
        if (empty($this->answers)) {
            $this->answers = '[]';
        }

        // the number of items per subject according to the testplan for this test
        $planItems = $this->testform->testplan->items_by_subject;
        // get rid of any subjects that don't have testplan items defined
        $planItems = array_filter((array) $planItems);
        // Array like subjectID => 0 setup to hold correct by subject
        $correctBySubject = array_fill_keys(array_keys($planItems), 0);

        // Get testitems with just the subject for this exam
        $items = $this->testform->testitems->load(['subjects' => function ($query) {
            $query->where('exam_testitem.exam_id', '=', $this->exam_id);
        }]);

        // get the answers, keyed by testitems.id
        // testitems.id => distractor_id
        $answers = $items->lists('answer', 'id')->all();

        // the person's responses, keyed by testitems.id like 
        // testitems.id => distractors.id (choice)
        $responses = $this->answers;
        $totalCorrect = 0;

        // check each question
        foreach ($responses as $itemId => $answer) {
            // Was an answer provided to this question?
            if (isset($responses->$itemId)) {
                // does their answer match the actual answer?
                if ($answer == $answers[$itemId]) {
                    $subjects = $items->find($itemId)->subjects;

                    if (! $subjects->isEmpty()) {
                        $subject   = $subjects->first();
                        $subjectId = $subject->report_as ? $subject->report_as : $subject->id;
                        
                        // Yes, they answered correctly
                        // Add one to the count of total correct for this subject
                        $correctBySubject[$subjectId]++;
                    }

                    // update the total # correct so far
                    $totalCorrect++;
                }
            }
        }

        // Post-process the number of correct items by subject to account for subject report_as
        $correctBySubject = $this->mapCorrectBySubject($correctBySubject);

        // Tally up the regular total and find score
        $total         = count($answers);
        $score         = ($totalCorrect / $total) * 100;
        $minimum_score = $this->testform->testplan->minimum_score;

        $status = $this->status;

        // update the status?
        if ($updateStatus === true) {
            $status = $score >= $minimum_score ? 'passed' : 'failed';
        }

        // If there were no answers at all
        // and this test hasn't been rescheduled, they were a no-show
        if (empty($this->answers) && $this->status != 'rescheduled') {
            $status = 'noshow';
        }

        // Update the testattempt
        $updated = $this->update([
            'score'              => $score,
            'correct_answers'    => $totalCorrect,
            'total_questions'    => $total,
            'status'             => $status,
            'correct_by_subject' => $correctBySubject
        ]);

        return $updated;
    }

    /**
     * Process an array (subject_id => # correct) accounting for subjects report_as
     */
    protected function mapCorrectBySubject($correct)
    {
        if ($correct && is_array($correct)) {
            // Grab all the subjects pertaining to the correct answers
            $subjects = Subject::whereIn('id', array_keys($correct))->get();

            // if we don't have any subjects found, nothing to check
            if ($subjects->isEmpty()) {
                return null;
            }

            // Go through each subject, if it reports as something else, combine total and remove this key
            foreach ($correct as $subId => $numCorrect) {
                $subject = $subjects->find($subId);

                // does this subject report_as another?
                if ($subject && ! empty($subject->report_as)) {
                    // combine this total with an existing key for report_as id if it exists
                    if (array_key_exists($subject->report_as, $correct)) {
                        $correct[$subject->report_as] += $numCorrect;
                    } else {
                        // otherwise create a key for it with this total
                        $correct[$subject->report_as] = $numCorrect;
                    }

                    // remove this subject's key from the main array, since it's reporting as another now
                    unset($correct[$subId]);
                }
            }
        }

        return $correct;
    }

    /**
     * Stop an in-progress test -- save it, score, clear session
     */
    public function stopTest()
    {
        $questions = Session::get('testing.questions');
        $answered  = Session::get('testing.answers');

        // STOP THE TEST
        // Is there a test attempt, and some questions?
        if ($this->id && ! empty($questions)) {
            // Save the answers in DB 
            // Stored in answers as json 
            $this->answers = json_encode($answered);
            $this->end_time = date('Y-m-d H:i:s');
            $this->save();
            
            // Score the test behind the scenes
            $this->score();

            // Rid session of any testing stuff
            Session::forget('testing');
            Session::forget('disable_menu');
        }
    }

    /**
     * Gets the time remaining or 0
     * @return int
     */
    public function getTimeRemainingAttribute()
    {
        $remaining = 0;
        $extendBy  = 0;

        // Does the student for this test attempt have an ADA?
        if ($this->student) {
            foreach ($this->student->adas as $ada) {
                if ($ada->extend_time > 0) {
                    $extendBy += $ada->extend_time;
                }
            }
        }

        $extendBy *= 60;

        if ($this->testform) {
            $startTime = strtotime($this->start_time);
            $timeTotal = $this->testform->testplan->timelimit * 60; // total test time limit in seconds
            $timeTotal = $timeTotal + $extendBy; // account for ADA time extension
            $elapsed   = time() - $startTime;
            $remaining = $timeTotal - $elapsed;
        }

        return $remaining;
    }

    /**
     * Updates the bookmarks in the session 
     */
    public function updateBookmarks()
    {
        $current      = Input::get('current');
        $bookmark     = Input::get('bookmark');
        $allBookmarks = Session::get('testing.bookmarks');

        if ($bookmark) {
            // Update the list of bookmarks to include this one
            $allBookmarks[] = $bookmark;
            Session::put('testing.bookmarks', array_unique($allBookmarks));
        } else {   // Remove a bookmark if the question # exists in bookmarks but not posted
            if (is_array($allBookmarks)) {
                // unset it by value
                if (($key = array_search($current, $allBookmarks)) !== false) {
                    Session::forget('testing.bookmarks.'.$key);
                }
            }
        }
    }


    /**
     * Update answers to match a a pipe-delimited string like 1,A|2,B|3,A
     * no-shows come in like 1,@|2,@|3,@|4,@
     * where 1 = question #
     * and   A = choice from ABCDE
     */
    public function updateAnswersFromPiped($string, $updateScore = true, $paper = false)
    {
        // get the testitem objects on this testform sorted by ordinal
            // WITH the distractors sorted by ordinal 
        $items = Testform::find($this->testform_id)->testitems()->orderBy('testform_testitem.ordinal');
        $items = $items->with('distractors')->get();
        $dbKnowledge = [];

        // need to get the distractor ID's keyed by ABCDE under each question ID

        // split up the answers into a usable array, to be stored in json like:
        // testitems.id => distractors.id (their choice)
        $map = array_flip(range('A', 'E'));

        foreach (explode('|', $string) as $response) {
            $split = explode(',', $response);
            $q     = array_get($split, 0);        // question number like 1,2,3,4
            $a     = array_get($split, 1);        // choice like ABCDE

            // is this an invalid answer? (not ABCDE)
            if (! array_key_exists($a, $map)) {
                // skip this answer, in case of something like `@`
                continue;
            }

            // does an item exist matching this question number?
            if ($items->offsetExists($q - 1)) {
                // get the item with the given question number from the collection
                $item = $items->offsetGet($q - 1);    // take off one since questions are 1-based

                // use the 'map' array to grab the number that will correspond with the distractors.ordinal
                $choice = array_get($map, $a) + 1;    // add one since distractor ordinal numbers also 1-based

                // find the distractor id of the first distractor matching that ordinal
                $distractor = $item->distractors->filter(function ($d) use ($choice) {
                    if ($d->ordinal == $choice) {
                        return true;
                    }
                })->first();
                
                // add it to our array of answers to store in db for this attempt if this distractor exists!
                if ($distractor) {
                    $dbKnowledge[$item->id] = $distractor->id;
                }
            }
        }

        // Update the test attempt in the database with the answers!
        $this->answers = json_encode($dbKnowledge);

        // should we update the score now?
        if ($updateScore === true) {
            // param specifies we don't want to update the status field this time
            // it might be something like 'unscored' so we'll want it to stay as-is 
            // in case this is a score pending review
            // 
            // we want to update the SCORE, not necessarily the status
            $this->score(false);
        }

        // if coming in from paper scoring API, make sure to set end_time
        if ($paper === true) {
            $this->end_time = date('Y-m-d H:i:s');
        }

        return $this->save();
    }

    public function setCorrectBySubjectAttribute($value)
    {
        $this->attributes['correct_by_subject'] = json_encode($value);
    }

    public function getCorrectBySubjectAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getPercentAttribute()
    {
        if ($this->score !== null) {
            return number_format((float)$this->score, 2, '.', '').'%';
        }

        return null;
    }

    public function getAnswersAttribute($value)
    {
        return json_decode($value);
    }

    public function getAnswersAlphaAttribute()
    {
        // get the answers in ABCDE format, keyed by item ID
        $answers = $this->answers;

        $chars   = [
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E'
        ];

        if (! $answers) {
            return null;
        }

        // get all the itemId's and associated items
        $itemIds = array_keys((array)$answers);
        $items = \Testitem::with('distractors')->whereIn('id', $itemIds)->get();

        $alphaAnswers = [];
        foreach ($answers as $itemId => $distractorId) {
            $char = array_get($chars, $items->find($itemId)->distractors->find($distractorId)->ordinal);
            $alphaAnswers[$itemId] = $char;
        }

        return $alphaAnswers;
    }
}
