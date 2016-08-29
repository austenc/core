<?php

/**
 * Add a notice if you have test you can start / resume / end
 */
Route::filter('actionableTests', function () {
    $tests = null;
    $type  = null;

    if (Auth::check()) {
        $user  = Auth::user();

        if ($user->isRole('Student')) {
            // get tests and share a var to all views
            $tests = $user->userable->actionableTests();
            $type  = $user->userable->actionableType;
        }
    }

    View::share('actionableTests', $tests);
    View::share('actionableType', $type);
});

/**
 * Makes sure the logged-in student matches the attempt, takes in an optional attemptId field
 */
Route::filter('attemptMatchesStudent', function ($route, $request, $attemptId = null) {
    $attemptId = $attemptId === null ? $route->parameter('attempt') : $attemptId;
    $attempt   = \Testattempt::find($attemptId);

    // if we have an attempt, make sure the logged in student's id matches student_id on the attempt row
    if (! $attempt->empty) {
        // get currently logged in user type
        $user            = Auth::user();
        $studentId       = $user->userable_id;
        $matchingStudent = ($user->userable_type == 'Student' && $studentId == $attempt->student_id);

        // If they're not a matching student, don't give them access
        if (! $matchingStudent && ! $user->ability(['Admin', 'Staff', 'Agency'], [])) {
            Flash::warning('That action is not allowed.');
            return Redirect::to('/');
        }
    }
});

/**
 * Make sure this is somebody's 'owned' student
 */
Route::filter('editOwnStudent', function ($route) {
    $studentId = $route->parameter('id');
    $user      = Auth::user();

    if ($studentId !== null) {
        // is an instructor logged in?
        if ($user->isRole('Instructor')) {
            // then does this student currently 'belong' to this instructor?
            $isOwner = DB::table('instructor_student')
                ->where('instructor_id', '=', $user->userable_id)
                ->where('student_id', '=', $studentId)
                ->where('active', '=', true)
                ->count();

            // If this person isn't the owner, don't let them edit
            if ($isOwner < 1) {
                Flash::warning('Unable to edit. This person is owned by another '.Lang::choice('core::terms.instructor', 1).'.');
                return Redirect::route('students.index');
            }
        }
    }
});

/**
 * Only allows access if NOT production environment
 */
Route::filter('nonProduction', function ($route) {
    if (App::environment('production')) {
        Flash::warning('This action is not allowed on production server(s).');
        return Redirect::route('home');
    }
});

Route::filter('csrf-except', function () {
    // protected routes
    // need to disable csrf to allow codeception testing with filters enabled
    if (! Request::is('api/*') && ! Request::is('email/change') && ! Request::is('facilities/select/login') && ! Request::is('instructors/select/login')) {
        if (Session::token() !== Input::get('_token')) {
            throw new Illuminate\Session\TokenMismatchException;
        }
    }
});

// Redirect request if record is archived
// applied to edit RESTful routes
Route::filter('check-archived', function ($route, $request) {
    // expecting route resource names: facilities.edit, observers.edit	
    if (strpos($route->getName(), '.') === false) {
        return Redirect::to('/');
    }

    $type = $request->segment(1);
    $id   = $request->segment(2);

    switch ($type) {
        case 'facilities':
            $record = Facility::find($id);
            break;
        case 'observers':
            $record = Observer::find($id);
            break;
        case 'actors':
            $record = Actor::find($id);
            break;
        case 'students':
            $record = Student::find($id);
            break;
        case 'instructors':
            $record = Instructor::find($id);
            break;
        case 'proctors':
            $record = Proctor::find($id);
            break;
        default:
            return Redirect::to('/');
    }

    // couldnt find record in any region
    if (is_null($record)) {
        return Redirect::to('/');
    }

    // archived record found!
    if ($record->isArchived) {
        // if power-user, allow viewing
        if (Auth::user()->ability(['Admin', 'Staff', 'Agency'], [])) {
            return Redirect::route($type.'.archived', $id);
        }
        
        // non-power user, back to all records
        return Redirect::route($type.'.index');
    }
});

// Redirect request if record is active
// Only power users access this route to begin with
// facilities.archived
Route::filter('check-active', function ($route, $request) {
    // expecting route resource names: facilities.edit, observers.edit	
    if (strpos($route->getName(), '.') === false) {
        return Redirect::to('/');
    }

    $type = $request->segment(1);
    $id   = $request->segment(2);

    switch ($type) {
        case 'facilities':
            $record = Facility::find($id);
            break;
        case 'observers':
            $record = Observer::find($id);
            break;
        case 'actors':
            $record = Actor::find($id);
            break;
        case 'students':
            $record = Student::find($id);
            break;
        case 'instructors':
            $record = Instructor::find($id);
            break;
        case 'proctors':
            $record = Proctor::find($id);
            break;
        default:
            return Redirect::to('/');
    }

    // couldnt find record in any region, return to homepage
    if (is_null($record)) {
        return Redirect::to('/');
    }

    // active record found!
    if ($record->isActive) {
        return Redirect::route($type.'.edit', $id);
    }
});

// Prevent logged in facility from accessing an Out Of Bounds student (isn't connected under logged in discipline)
// i.e. Logged in Discipline A, trying to access Student that trained Discipline B
// students.edit
Route::filter('prevent-access-ob-student', function ($route, $request) {
    // Facility Only
    if (Auth::user()->isRole('Facility')) {
        // get requested student
        $studentId = $request->segment(2);
        $student   = Student::with('allActiveStudentTrainings')->findOrFail($studentId);

        $currFacility = Auth::user()->userable;

        // only Training approved Facilities should have access to students
        if (empty($currFacility->actions) || (is_array($currFacility->actions) && ! in_array('Training', $currFacility->actions))) {
            return Redirect::route('account')->withDanger('You are not an approved '.Lang::choice('core::terms.facility_training', 1).'.');
        }

        // all sites (current facility + all affiliated)
        // find out if student has any active&passed trainings at any of the Affiliated Sites (or current facility)
        $affliatedSiteIds = array_unique(array_merge(array(Auth::user()->userable->id), $currFacility->affiliated->lists('id')->all()));
        $matchedSites = array_intersect($student->allActiveStudentTrainings->lists('facility_id')->all(), $affliatedSiteIds);

        // if no matched sites, student does not "belong" to this training program!
        if (empty($matchedSites)) {
            return Redirect::route('students.index')->withDanger('Only '.Lang::choice('core::terms.student', 2).' trained at this '.Lang::choice('core::terms.facility_training', 1).' or Affiliated Sites are accessible.');
        }

        // check the student has a training with the current discipline at the training program
        $withinBounds = DB::table('student_training')
            ->where('student_id', $studentId)
            ->where('discipline_id', Session::get('discipline.id'))
            ->where('facility_id', $currFacility->id)
            ->whereNull('archived_at')
            ->first();

        if (! $withinBounds) {
            $reset = link_to_route('facilities.login', 'Reset Login');
            $msg = Lang::choice('core::terms.student', 1)." outside Discipline filter set on login. Use $reset to change Discipline.";

            return Redirect::route('students.index')->withDanger($msg);
        }
    }

});

/**
 * Restrict event viewing for a facility to only logged in discipline
 */
Route::filter('prevent-access-ob-event', function ($route, $request) {
    // get requested event
    $eventId = $request->segment(2);
    $event   = Testevent::find($eventId);

    $currUser = Auth::user();

    if ($event) {
        // Facility
        if ($currUser->isRole('Facility')) {
            // Testing approved only
            if (! in_array('Testing', $currUser->userable->actions)) {
                return Redirect::route('events.index')->withDanger('Not a Testing approved '.Lang::choice('core::terms.facility', 1).'.');
            }

            // Must be an event at this Facility
            if ($currUser->userable->id != $event->facility_id) {
                return Redirect::route('events.index');
            }

            // Must be an event within current Discipline
            if ($event->discipline_id != Session::get('discipline.id')) {
                $reset = link_to_route('facilities.login', 'Reset Login');
                $msg = "Event outside Discipline filter set on login. Use $reset to change Discipline.";
                
                return Redirect::route('events.index')->withDanger($msg);
            }
        }

        // Proctor
        if ($currUser->isRole('Proctor')) {
            if ($currUser->userable->id != $event->proctor_id) {
                return Redirect::route('events.index');
            }
        }

        // Observer
        if ($currUser->isRole('Observer')) {
            if ($currUser->userable->id != $event->observer_id) {
                return Redirect::route('events.index');
            }
        }
    }
});

/**
 * Prevent Paper-To-Web event update once any students are scheduled
 * Protect previously scheduled Oral students (oral must be paper event)
 */
Route::filter('prevent-paper-to-web', function ($route, $request) {
    // get event being updated
    $eventId = $request->segment(2);
    $event = Testevent::with('knowledgeStudents', 'skillStudents')->find($eventId);

    // get is_paper data
    $currType = Input::get('is_paper') ? 1 : 0;    // currType 0 = web event

    if ($event && (! $event->knowledgeStudents->isEmpty() || ! $event->skillStudents->isEmpty()) && ($event->is_paper != $currType) && ! $currType) {
        Flash::danger('Unable to change Event from Paper to Web due to scheduled Students');
        return Redirect::route('events.edit', $event->id);
    }
});
