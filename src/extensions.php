<?php

Blade::directive('datetime', function ($expression) {
    return "<?php echo with{$expression}->format('m/d/Y H:i'); ?>";
});

Validator::extend('array_has_one', function ($attribute, $value, $params) {
    if (! is_array($value)) {
        return false;
    }

    $found_val = false;
    foreach ($value as $val) {
        if (isset($val) && $val != '') {
            $found_val = true;
        }
    }

    return $found_val;
});

/**
 * Checks if a single Event Exam has remaining open seats (only need 1 seat)
 */
Validator::extend('event_exam_has_seats', function ($attribute, $value, $params) {
    $examId = $params[0];
    $eventId = $params[1];
    
    $event = Testevent::find($eventId);
    $eventExam = $event->exams()->where('exam_id', $examId)->first();

    // get seats
    $takenSeats = $event->knowledgeStudents()->where('exam_id', $examId)->count();
    $totalSeats = (int) $eventExam->pivot->open_seats;
    $remSeats   = $totalSeats - $takenSeats;

    return $remSeats > 0;
});

/**
 * Checks if a single Event Skillexam has remaining open seats (only need 1 seat)
 */
Validator::extend('event_skill_has_seats', function ($attribute, $value, $params) {
    $skillId = $params[0];
    $eventId = $params[1];
    
    $event = Testevent::find($eventId);
    $event_skill = $event->skills()->where('skillexam_id', '=', $skillId)->first();

    // get seats
    $takenSeats = $event->skillStudents()->where('skillexam_id', $skillId)->count();
    $totalSeats = (int) $event_skill->pivot->open_seats;
    $remSeats   = $totalSeats - $takenSeats;

    return $remSeats > 0;
});

/**
 * Checks if a single Event Exam has remaining unique testforms (only need 1 available)
 */
Validator::extend('event_exam_has_testforms', function ($attribute, $value, $params) {
    $examId  = $params[0];
    $eventId = $params[1];

    $event = Testevent::find($eventId);
    $exam  = Exam::with('active_testforms')->find($examId);

    $allTestforms  = $exam->active_testforms->lists('id')->all();
    $usedTestforms = $event->knowledgeStudents()->where('exam_id', $examId)->get()->lists('testform_id')->all();

    $remTestforms = array_diff($allTestforms, $usedTestforms);

    return count($remTestforms) > 0;
});

/**
 * Student create check the initial training start date comes before the ended date
 */
Validator::extend('start_before_end', function ($attribute, $value, $params) {
    $start = Input::get('started');
    $end   = Input::get('ended');

    // only if ended date is set
    if (! empty($end)) {
        // ensure start comes before end
        if (strtotime($start) > strtotime($end)) {
            return false;
        }
    }

    return true;
});

/**
 * Check a new event doesnt exceed facility max seats limit
 */
Validator::extend('facility_max_seats', function ($attribute, $value, $params) {
    $errors = [];

    // get discipline 
    $disciplineId = Input::get('discipline_id');

    // get test site
    $facilityId = Input::get('facility_id');
    $facility   = Facility::find($facilityId);

    // no max seats set? allow continue, no limit
    if (empty($facility->max_seats)) {
        return true;
    }

    // get test seats
    $knowledge = Input::get('exam_seats');
    $skill     = Input::get('skill_seats');

    // knowledge test seats
    foreach ($knowledge as $comboInfo => $examSeats) {
        list($examId, $disciplineId) = explode('|', $comboInfo);

        if (! empty($examSeats) && $facility->max_seats < $examSeats) {
            $exam = Exam::find($examId);
            $errors[] = 'Knowledge Test <strong>'.$exam->name.'</strong> exceeds max seat limit of '.$facility->max_seats.' for '.Lang::choice('core::terms.facility_testing', 1).' <strong>'.$facility->name.'</strong>';
        }
    }

    // skill test seats
    foreach ($skill as $comboInfo => $skillSeats) {
        list($skillId, $disciplineId) = explode('|', $comboInfo);

        if (! empty($skillSeats) && $facility->max_seats < $skillSeats) {
            $skillexam = Skillexam::find($skillId);
            $errors[]  = 'Skill Test <strong>'.$skillexam->name.'</strong> exceeds the max seat limit of '.$facility->max_seats.' for '.Lang::choice('core::terms.facility_testing', 1).' <strong>'.$facility->name.'</strong>';
        }
    }

    // any exam seats exceeding max seat limit?
    if ($errors) {
        Session::flash('danger', implode('<br>', $errors));
        return false;
    }

    return true;
});

/**
 * Before event create OR publish pending
 * When coming from events.create the seats fields will have composite key (examId+disciplineId)
 * When coming from events.edit_pending the seats fields are keyed normally
 * Check knowledge/skill exams have enough unique testforms available for requested # seats
 */
Validator::extend('check_event_exams', function ($attribute, $value, $params) {
    $errors = [];

    // get seat counts
    $knowledge = Input::get('exam_seats');
    $skill     = Input::get('skill_seats');

    // check if testevent has at least 1 seat (test) listed
    $hasTest = false;

    // go thru all knowledge exams
    // check enough testforms for requested # seats
    if ($knowledge) {
        foreach ($knowledge as $comboInfo => $examSeats) {
            if (strpos($comboInfo, '|') !== false) {
                list($examId, $disciplineId) = explode('|', $comboInfo);
            } else {
                $examId = $comboInfo;
            }

            if (! empty($examSeats)) {
                // get current exam
                $exam = Exam::with('active_testforms')->find($examId);

                if ($exam->active_testforms->count() < $examSeats) {    // if more seats than testforms, error..
                    $errors[] = 'Knowledge Test <strong>'.$exam->name.'</strong> is limited to a maximum of '.$exam->active_testforms->count().' seats.';
                } elseif (! is_numeric($examSeats)) {
                    $errors[] = 'Knowledge Test <strong>'.$exam->name.'</strong> seats must be numeric.';
                } else {
                    $hasTest = true;
                }
            }
        }
    }

    // go thru all skill exams
    if ($skill) {
        foreach ($skill as $comboInfo => $skillSeats) {
            if (strpos($comboInfo, '|') !== false) {
                list($skillId, $disciplineId) = explode('|', $comboInfo);
            } else {
                $skillId = $comboInfo;
            }

            if (! empty($skillSeats)) {
                $skillexam = Skillexam::with('active_tests')->find($skillId);

                if ($skillexam->active_tests->count() < $skillSeats) {    // if more seats than testforms, error.. 
                    $errors[] = 'Skill Test <strong>'.$skillexam->name.'</strong> is limited to a maximum of '.$skillexam->active_tests->count().' seats.';
                } elseif (! is_numeric($skillSeats)) {
                    $errors[] = 'Skill Test <strong>'.$skillexam->name.'</strong> seats must be numeric.';
                } else {
                    $hasTest = true;
                }
            }
        }
    }

    // no seats lists in any event?
    if ($errors) {
        Session::flash('danger', implode('<br>', $errors));
        return false;
    }

    // set error msg if no tests listed
    if (! $hasTest) {
        Session::flash('danger', 'Event must have at least 1 Exam Seat listed.');
        return false;
    }

    return true;
});

/**
 * Checks each step in a task contains a weight value
 */
Validator::extend('check_task_weights', function ($attribute, $value, $params) {
    return ! in_array("", $value, true);
});

Validator::extend('reverse_match', function ($attribute, $value, $parameters) {
    $ssn = Input::get('ssn');
    return strrev($ssn) == $value;
});

/**
 * Check new Input for Skill Step contains at least 2 options (text+value pairs)
 */
Validator::extend('step_inputs', function ($attribute, $value, $parameters) {
    $c = 0;
    foreach ($value as $opt) {
        if (! empty($opt)) {
            $c++;
        }
    }

    return $c > 1;
});

/**
 * Checks selected answer for new Input has an associated option
 */
Validator::extend('input_option_answer', function ($attribute, $value, $parameters) {
    $options = Input::get('option');
    return ! empty($options[$value]);    // check answer exists in options
});

/**
 * Check all Input Options are unique
 */
Validator::extend('input_option_unique', function ($attribute, $value, $parameters) {
    $filtered = array_filter($value);
    return count($filtered) === count(array_unique($filtered));
});

/**
 * Check all Input Values are unique
 */
Validator::extend('input_value_unique', function ($attribute, $value, $parameters) {
    $filtered = array_filter($value);
    return count($filtered) === count(array_unique($filtered));
});

/**
 * Checks if an SSN is already in use before updating student record
 */
Validator::extend('unique_ssn', function ($attribute, $value, $parameters) {
    $ignoreStudentId = isset($parameters[0]) ? $parameters[0] : '';

    // check if another student is using this ssn
    $ssn = str_replace(['-', '_', ' '], '', Input::get('ssn'));
    $dup = Student::where('ssn_hash', saltedHash($ssn));

    // specific student id to ignore? if add student no, if update student yes
    if (! empty($ignoreStudentId)) {
        $dup->whereNotIn('id', [$ignoreStudentId]);
    }

    $dup = $dup->first();

    return is_null($dup);
});

/**
 * Checks if a License is already in use for another Observer
 */
Validator::extend('unique_observer_license', function ($attribute, $value, $parameters) {
    $ignoreObsId = isset($parameters[0]) ? $parameters[0] : '';

    $obs = Observer::where('license', $value);

    if (! empty($ignoreObsId)) {
        $obs->whereNotIn('id', [$ignoreObsId]);
    }

    $obs = $obs->first();

    return is_null($obs);
});


/**
 * Checks if an SSN is properly formed
 * Strips extra chars and checks ssn is length 9
 */
Validator::extend('proper_ssn', function ($attribute, $value, $parameters) {
    // remove all possible 
    $ssn = str_replace(['-', '_', ' '], '', Input::get('ssn'));

    return strlen($ssn) == 9;
});
