<?php namespace Hdmaster\Core\Traits;

use Carbon\Carbon;
use \Student;
use \Testevent;
use \User;
use \Pendingscore;
use \Testattempt;
use \Skillattempt;
use \Config;

trait Attemptable
{

    use \Venturecraft\Revisionable\RevisionableTrait;

    public static function bootAttemptable()
    {
        static::addGlobalScope(new \ReschedulableScope);
        static::bootRevisionableTrait();
    }

    public static function withRescheduled()
    {
        return with(new static)->newQueryWithoutScope(new \ReschedulableScope);
    }

    /**
     * A test attempt may have one associated pending score (to be reviewed)
     * @return  Relation
     */
    public function pendingReview()
    {
        return $this->morphOne(Pendingscore::class, 'scoreable');
    }

    /**
     * A test attempt has one associated student
     * @return Relation
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /** 
     * A test attempt has one associated event
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
     * Events updated within the last day
     */
    public function scopeUpdatedWithinLastDay($query)
    {
        return $query->where('updated_at', '>=', Carbon::now()->subDay());
    }

    /**
     * Non-archived events
     */
    public function scopeNotArchived($query)
    {
        return $query->where('archived', false);
    }

    /**
     * Bootstrap-esque status class
     */
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
     * Whether the attempt has been taken or not
     */
    public function getTakenAttribute()
    {
        switch ($this->status) {
            case 'passed':
            case 'failed':
            case 'unscored':
                return true;
            break;

            default:
                return false;
        }
    }

    public function getStartDateAttribute()
    {
        $startTime = $this->getOriginal('start_time');

        // tests that havent started yet, show testevent date
        if (empty($startTime) || in_array($this->status, ['assigned', 'pending'])) {
            $startTime = $this->testevent->test_date;
        }

        return date('m/d/Y', strtotime($startTime));
    }

    public function getEndDateAttribute()
    {
        $endTime = $this->getOriginal('end_time');

        // tests that havent started yet, show testevent date
        if (empty($endTime) || in_array($this->status, ['assigned', 'pending'])) {
            $endTime = $this->testevent->test_date;
        }

        return date('m/d/Y', strtotime($endTime));
    }

    public function getStartTimeAttribute($value)
    {
        return $value ? date('h:i A', strtotime($value)) : '';
    }

    public function getEndTimeAttribute($value)
    {
        return $value ? date('h:i A', strtotime($value)) : '';
    }

    public function getTestDateAttribute()
    {
        $evtDate = $this->testevent->test_date;

        return $evtDate ? date('Y-m-d', strtotime($evtDate)) : '';
    }

    /**
     * Return the type of a test.
     * @return boolean
     */
    public function getHasTypeAttribute()
    {
        return ($this->exam_id) ? 'knowledge' : 'skill';
    }

    /**
     *  Determine if test results can be displayed based on both time set in configuration and in the event
     *  both knowledge and skill tests are in the same test event, both must be scored before student can 
     *  see the results of their tests.
     */
    public function getSeeResultsAttribute()
    {
        // Is this attempt on hold?
        if ($this->testHold) {
            return false;
        }

        // Is this an online event, if so, only continue if it's been ended
        if ($this->testevent && ! $this->testevent->is_paper) {
            if (! $this->testevent->ended) {
                return false;
            }
        }

        // Get opposing skill attempt if it exists in this event
        $oppAttempt = $this->opposingAttempt;

        // if we don't have an opposing attempt, show if it's past a certain time
        if (is_null($oppAttempt)) {

            // if the attempt is scored, show if appropriate time constraints are met
            if ($this->status == "passed" || $this->status == "failed" || $this->status == "noshow") {
                return $this->released;
            }
        } else {
            // If the 'sister' attempt is scored, show only if this one is scored too
            if ($oppAttempt->status == "passed" || $oppAttempt->status == "failed" || $this->status == "noshow") {
                if ($this->status == "passed" || $this->status == "failed" || $this->status == "noshow") {
                    return $this->released;
                }
            }
        }

        return false;
    }
    /**
     * Determine if the current date/time is past the config time set for each agency
     * @return [boolean] [true if past config time otherwise false]
     */
    public function getReleasedAttribute()
    {
        // Time results should be released for viewing, based on config
        $configTime = strtotime(date("Y-m-d") . " " . Config::get('core.events.release_results') . ":00");
        $endTime    = $this->getOriginal('end_time');
        $endDate    = date('Y-m-d', strtotime($endTime));
        $curDate    = date("Y-m-d");

        // If it's past the end date of the test attempt, it can be shown
        if ($endDate < $curDate) {
            return true;
        }

        // If it's not past that date, but same-day and past the time
        if (time() >= $configTime && $endDate >= $curDate) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is this attempt on hold?
     * @return boolean
     */
    public function getTestHoldAttribute()
    {
        return $this->hold;
    }

    /**
     * Creates a pending score record associated with this attempt
     *
     * @return  Pendingscore
     */
    public function createPending()
    {
        return Pendingscore::updateOrCreate([
            'scoreable_type' => $this->getMorphClass(),
            'scoreable_id'   => $this->id
        ]);
    }
}
