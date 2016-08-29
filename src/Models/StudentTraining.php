<?php namespace Hdmaster\Core\Models\StudentTraining;

use Input;
use Auth;
use Validator;
use Event;
use Lang;
use \Student;
use \Instructor;
use \Facility;
use \Training;
use \Exam;
use \Skillexam;
use \DateTime;
use \Config;
use \Discipline;

class StudentTraining extends \Eloquent
{
    protected $table = 'student_training';

    protected $fillable = [
        'discipline_id',
        'training_id',
        'student_id',
        'facility_id',
        'instructor_id',
        'status',
        'classroom_hours',
        'distance_hours',
        'lab_hours',
        'traineeship_hours',
        'clinical_hours',
        'ended',
        'started',
        'expires',
        'creator_id',
        'creator_type'
    ];

    public static $rules = [
        'discipline_id'     => 'required|numeric|not_in:0',
        'training_id'       => 'sometimes|required|numeric|not_in:0',
        'instructor_id'     => 'sometimes|required|numeric|not_in:0',
        'facility_id'       => 'sometimes|required|numeric|not_in:0',
        'status'            => 'required|in:attending,passed,failed',
        'classroom_hours'   => 'numeric',
        'distance_hours'    => 'numeric',
        'lab_hours'         => 'numeric',
        'traineeship_hours' => 'numeric',
        'clinical_hours'    => 'numeric',
        'ended'             => 'date',
        'started'           => 'required|date'
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        // set ended date
        self::$rules['ended'] = 'date|before:' . date('m/d/Y', strtotime('+1day'));
    }

    // relations
    public function instructor()
    {
        return $this->hasOne(Instructor::class, 'id', 'instructor_id')->withTrashed();
    }

    public function facility()
    {
        return $this->hasOne(Facility::class, 'id', 'facility_id')->withTrashed();
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'id', 'student_id');
    }

    public function training()
    {
        return $this->hasOne(Training::class, 'id', 'training_id');
    }

    public function discipline()
    {
        return $this->hasOne(Discipline::class, 'id', 'discipline_id');
    }


    /**
     * This (and the below attribute) is a workaround for a bug when the creator_type field is set to '' instead of null
     * Please see https://github.com/laravel/framework/issues/11169 for more info -- this was not fixed in Laravel 4.2
     */
    public function creator()
    {
        return $this->morphTo();
    }

    public function getCreatorAttribute()
    {
        if (! empty($this->creator_id)) {
            return $this->relations['creator'] = $this->creator()->getResults();
        }
    }

    // accessors
    public function getStartedAttribute()
    {
        return empty($this->attributes['started']) ? '' : date('m/d/Y', strtotime($this->attributes['started']));
    }
    public function getEndedAttribute()
    {
        return empty($this->attributes['ended']) ? '' : date('m/d/Y', strtotime($this->attributes['ended']));
    }
    public function getExpiresAttribute()
    {
        return empty($this->attributes['expires']) ? '' : date('m/d/Y', strtotime($this->attributes['expires']));
    }
    public function getArchivedAtAttribute()
    {
        if (empty($this->attributes['archived_at']) || $this->attributes['archived_at'] == '0000-00-00 00:00:00') {
            return '';
        }

        return date('m/d/Y g:i A', strtotime($this->attributes['archived_at']));
    }
    public function getUpdatedAtAttribute()
    {
        if (empty($this->attributes['updated_at']) || $this->attributes['updated_at'] == '0000-00-00 00:00:00') {
            return '';
        }

        return date('m/d/Y g:i A', strtotime($this->attributes['updated_at']));
    }
    public function getCreatedAtAttribute()
    {
        if (empty($this->attributes['created_at']) || $this->attributes['created_at'] == '0000-00-00 00:00:00') {
            return '';
        }

        return date('m/d/Y g:i A', strtotime($this->attributes['created_at']));
    }
    public function getArchivedAttribute()
    {
        return ! empty($this->archived_at);
    }
    public function getExpiredAttribute()
    {
        if (empty($this->expires)) {
            return false;
        }

        return time() > strtotime($this->expires);
    }

    // mutators
    public function setStartedAttribute($value)
    {
        // only format if it's a valid date already
        $date = date_parse($value);
        if (checkdate($date['month'], $date['day'], $date['year'])) {
            // valid date, format it to be compatible with our database
            $this->attributes['started'] = date('Y-m-d', strtotime($value));
        } else {
            // invalid, set the attribute to whatever it was so validation takes care of it
            $this->attributes['started'] = $value;
        }
    }

    public function setEndedAttribute($value)
    {
        // only format if it's a valid date already
        $date = date_parse($value);
        if (checkdate($date['month'], $date['day'], $date['year'])) {
            // valid date, format it to be compatible with our database
            $this->attributes['ended'] = date('Y-m-d', strtotime($value));
        } else {
            // invalid, set the attribute to whatever it was so validation takes care of it
            $this->attributes['ended'] = $value;
        }
    }

    /**
     * Adds a new student training record
     */
    public function addWithInput($id)
    {
        $disciplineId  = Input::get('discipline_id');
        $trainingId    = Input::get('training_id');
        $facilityId    = Input::get('facility_id');
        $instructorId  = Input::get('instructor_id');
        $status        = Input::get('status');
        // dates
        $started       = date('Y-m-d', strtotime(Input::get('started')));
        $ended         = null;
        $expires       = null;
        $reason        = null;

        // hours
        $classHours    = Input::get('classroom_hours');
        $distHours     = Input::get('distance_hours');
        $labHours      = Input::get('lab_hours');
        $traineeHours  = Input::get('traineeship_hours');
        $clinicalHours = Input::get('clinical_hours');

        // find training
        $training = Training::find($trainingId);

        // passed and failed trainings
        if (in_array($status, ['passed', 'failed'])) {
            // dates
            $ended         = date('Y-m-d', strtotime(Input::get('ended')));

            // passed
            // set training expiration
            if ($status == 'passed') {
                if (Input::get('expires') && Auth::user()->ability(['Admin', 'Staff'], [])) {
                    $expires = date('Y-m-d', strtotime(Input::get('expires')));
                } else {
                    $expires = $training->getTrainingExpiration($ended);
                }
            }

            // failed
            // set reason for failure
            if ($status == 'failed') {
                $reason = Input::get('reason');
            }
        }

        // get student that is getting the training
        $student = Student::find($id);

        // set new training data
        $trainingInfo = [
            'discipline_id'     => $disciplineId,
            'facility_id'       => $facilityId,
            'instructor_id'     => $instructorId,
            'status'            => $status,
            'reason'            => $reason,
            'classroom_hours'   => empty($classHours) ? null : $classHours,
            'distance_hours'    => empty($distHours) ? null : $distHours,
            'lab_hours'         => empty($labHours) ? null : $labHours,
            'traineeship_hours' => empty($traineeHours) ? null : $traineeHours,
            'clinical_hours'    => empty($clinicalHours) ? null : $clinicalHours,
            'started'           => $started,
            'ended'             => $ended,
            'expires'           => $expires,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
            'creator_id'        => Auth::user()->userable_id,
            'creator_type'      => Auth::user()->userable_type
        ];

        // create student training record
        $student->trainings()->attach($trainingId, $trainingInfo);

        // instructor doing the training "owns" this student now
        $student->setCurrentInstructor($instructorId);

        // notify student if the training was passed
        if ($status == 'passed') {
            $training = Training::find($trainingId);

            $student->user->notify()
                ->withType('info')
                ->withSubject('Training Added')
                ->withBody('Training '.$training->name.' has been passed and added to your record.')
                ->deliver();
        }

        // fire training added event
        // in oregon if the training was passed the record will be sent to OSBN
        Event::fire('student.training_added', [
            'student'  => $student,
            'training' => StudentTraining::matches($trainingInfo)->first()
        ]);

        return true;
    }

    public function updateWithInput($id)
    {
        $status        = Input::get('status');
        // dates
        $started       = date('Y-m-d', strtotime(Input::get('started')));
        $ended         = null;
        $expires       = null;
        // other
        $reason        = null;
        // hours
        $classHours    = Input::get('classroom_hours');
        $distHours     = Input::get('distance_hours');
        $labHours      = Input::get('lab_hours');
        $traineeHours  = Input::get('traineeship_hours');
        $clinicalHours = Input::get('clinical_hours');

        // passed and failed trainings
        if (in_array($status, ['passed', 'failed'])) {
            // set hours
            // set end date
            $ended         = date('Y-m-d', strtotime(Input::get('ended')));

            // if passed..
            // set expiration
            if ($status == 'passed') {
                if (Input::get('expires') && Auth::user()->ability(['Admin', 'Staff'], [])) {
                    $expires = date('Y-m-d', strtotime(Input::get('expires')));
                } else {
                    $expires = $this->training->getTrainingExpiration($ended);
                }
            }

            // if failed..
            // set reason for failure
            if ($status == 'failed') {
                $reason = Input::get('reason');
            }
        }

        // hours
        $this->classroom_hours   = empty($classHours) ? null : $classHours;
        $this->distance_hours    = empty($distHours) ? null : $distHours;
        $this->lab_hours         = empty($labHours) ? null : $labHours;
        $this->traineeship_hours = empty($traineeHours) ? null : $traineeHours;
        $this->clinical_hours    = empty($clinicalHours) ? null : $clinicalHours;
        // dates
        $this->started           = $started;
        $this->ended             = $ended;
        $this->expires           = $expires;
        // other
        $this->reason            = $reason;
        $this->status            = $status;

        // update the student training record
        $saved = $this->save();

        if ($saved) {
            Event::fire('student.training_updated', [
                'student'  => $this->student()->first(),
                'training' => $this
            ]);
        }

        return $saved;
    }

    /**
     * Validate a create/update training POST before updating in db
     */
    public function validateTraining()
    {
        $rules = $this->appendHoursRules(static::$rules);

        $messages = [
            'training_id.not_in'         => 'Invalid Training selected.',
            'instructor_id.not_in'       => 'Invalid '.Lang::choice('core::terms.instructor', 1).' selected.',
            'facility_id.not_in'         => 'Invalid '.Lang::choice('core::terms.facility_training', 1).' selected.',
            'classroom_hours.min'        => 'Classroom Hours must be a minimum of :min.',
            'distance_hours.min'         => 'Distance Hours must be a minimum of :min.',
            'lab_hours.min'              => 'Lab Hours must be a minimum of :min.',
            'traineeship_hours.min'      => 'Traineeship Hours must be a minimum of :min.',
            'clinical_hours.min'         => 'Clinical Hours must be a minimum of :min.',
            'classroom_hours.required'   => 'Classroom Hours is required.',
            'distance_hours.required'    => 'Distance Hours is required.',
            'lab_hours.required'         => 'Lab Hours is required.',
            'traineeship_hours.required' => 'Traineeship Hours is required.',
            'clinical_hours.required'    => 'Clinical Hours is required.',
            'started.required'           => 'Start date is required.',
            'ended.required'             => 'End date is required.',
            'reason.required'            => 'Reason is required.',
            'reason.not_in'              => 'Invalid Reason selected.'
        ];

        $v = Validator::make(Input::all(), $rules, $messages);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }

    public function scopeMatches($query, $studentTraining)
    {
        foreach ($studentTraining as $k => $search) {
            $query = $query->where($k, '=', $search);
        }
        
        return $query;
    }

    /**
     * Appends rules for each hours field depending on Input
     * Used for validation on students.create AND students.edit_training
     *   - Must have minimum hours if PASSED
     *   - Must have reason if FAILED
     *   - Etc..
     */
    public function appendHoursRules($rules)
    {
        if (Input::get('status') == 'passed') {
            $rules['ended'] = 'required|date';

            // if training type set check min required hours
            if (Input::get('training_id')) {
                $trainingId = Input::get('training_id');
                $training   = Training::find($trainingId);

                if (! empty($training->classroom_hours)) {
                    $rules['classroom_hours'] = 'required|numeric|min:'.$training->classroom_hours;
                }

                if (! empty($training->distance_hours)) {
                    $rules['distance_hours'] = 'required|numeric|min:'.$training->distance_hours;
                }

                if (! empty($training->lab_hours)) {
                    $rules['lab_hours'] = 'required|numeric|min:'.$training->lab_hours;
                }

                if (! empty($training->traineeship_hours)) {
                    $rules['traineeship_hours'] = 'required|numeric|min:'.$training->traineeship_hours;
                }

                if (! empty($training->clinical_hours)) {
                    $rules['clinical_hours'] = 'required|numeric|min:'.$training->clinical_hours;
                }
            }
        }

        if (Input::get('status') == 'failed') {
            $rules['ended']  = 'required|date';
            $rules['reason'] = 'required|not_in:0';
        }

        // if ended date is set, it must be valid and after started date
        if (Input::get('ended')) {
            $tomorrow       = date('m/d/Y', strtotime('+1day'));
            $dayBeforeStart = date('m/d/Y', strtotime(Input::get('started') . '-1day'));

            $rules['ended'] = 'date|before:' . $tomorrow . '|after:' . $dayBeforeStart;
        }

        return $rules;
    }
}
