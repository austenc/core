<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Lang;
use Input;
use Config;
use Session;
use Redirect;
use Response;
use Request;
use Log;
use DB;
use URL;
use Hdmaster\Core\Notifications\Flash;
use Illuminate\Database\Eloquent\Collection;
use \Training;
use \Instructor;
use \Facility;
use \Role;
use \Discipline;
use \Sorter;

class InstructorsController extends BaseController
{
    protected $instructor;
    protected $training;
    protected $user;

    public function __construct(Instructor $instructor, Training $training)
    {
        $this->instructor = $instructor;
        $this->training   = $training;
        $this->beforeFilter('check-archived', ['only' => 'edit']);
        $this->beforeFilter('check-active', ['only' => 'archived']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $params = $this->instructor->handleSearch();

        $params['searchTypes']   = Session::get('instructors.search.types');
        $params['searchQueries'] = Session::get('instructors.search.queries');
        $params['filter']        = Session::get('instructors.search.filter');

        $disciplines = Discipline::all();

        return View::make('core::instructors.index')->with($params)->with(['disciplines' => $disciplines]);
    }

    /**
     * Remove an existing discipline from instructor
     * All trainings under this discipline will also be removed from instructor_trainings table
     */
    public function deactivateDiscipline($id, $disciplineId)
    {
        $instructor = Instructor::with('teaching_trainings', 'disciplines')->find($id);
        $discipline = Discipline::with('training')->find($disciplineId);

        if (is_null($instructor) || is_null($discipline)) {
            return Redirect::route('instructors.index');
        }

        // ensure this instructor has the discipline before removing it
        if (in_array($disciplineId, $instructor->disciplines->lists('id')->all())) {
            // remove the discipline for the instructor
            $instructor->disciplines()->detach($disciplineId);
            
            // deactivate any training programs under this discipline
            \DB::table('facility_person')
                ->where('discipline_id', '=', $discipline->id)
                ->where('person_id', '=', $instructor->id)
                ->where('person_type', '=', 'Instructor')
                ->update([
                    'active' => false
                ]);

            // remove any trainings under this discipline
            foreach ($discipline->training as $tr) {
                if (in_array($tr->id, $instructor->teaching_trainings->lists('id')->all())) {
                    $instructor->teaching_trainings()->detach($tr->id);
                }
            }

            return Redirect::route('instructors.edit', $instructor->id)->withSuccess('Successfully removed Discipline.');
        }

        return Redirect::route('instructors.edit', $instructor->id)->withDanger('Failed to remove Discipline.');
    }

    /**
     * Gets all training programs available for an instructor by certain discipline
     * Training programs the instructor is already working at (in this discipline) will be omitted
     */
    public function availablePrograms($id, $disciplineId)
    {
        // get instructor with all current programs for this discipline
        $instructor = Instructor::with(['facilities' => function ($query) use ($disciplineId) {
            $query->where('facility_person.discipline_id', $disciplineId);
        }])->find($id);

        // get all training programs doing this discipline
        $disciplinePrograms = Discipline::with('trainingPrograms')->find($disciplineId);

        $programs = new Collection;

        foreach ($disciplinePrograms->trainingPrograms as $program) {
            // if instructor isn't already doing this discipline, its available 
            if (! in_array($program->id, $instructor->facilities->lists('id')->all())) {
                $programs->push($program);
            }
        }

        return Response::json($programs);
    }

    /**
     * Activate Training for Instructor
     */
    public function activateTraining($id, $trainingId)
    {
        $instructor = Instructor::with('teaching_trainings')->find($id);

        // ensure training doesnt already exist
        if (! in_array($trainingId, $instructor->teaching_trainings->lists('id')->all())) {
            $instructor->teaching_trainings()->attach($trainingId);
            return Redirect::back()->withSuccess('Activated Training.');
        }

        return Redirect::back()->withErrors('Unable to activate Training.');
    }

    /**
     * Deactivate Training for Instructor
     */
    public function deactivateTraining($id, $trainingId)
    {
        $instructor = Instructor::with('teaching_trainings')->find($id);

        // ensure training exists
        if (in_array($trainingId, $instructor->teaching_trainings->lists('id')->all())) {
            $instructor->teaching_trainings()->detach($trainingId);
            return Redirect::back()->withSuccess('Deactivated Training.');
        }
        
        return Redirect::back()->withErrors('Unable to deactivate Training.');
    }

    /**
     * Instructor searching
     */
    public function search()
    {
        // get search parameters and save them to the session
        $query = Input::get('search');
        $type  = Input::get('search_type');

        // Set the search discipline
        Session::set('instructors.search.discipline', Input::get('search-discipline'));

        // unless we are adding a search to this via the form, reset the params on each search
        if (! Input::get('add_search')) {
            Session::forget('instructors.search.types');
            Session::forget('instructors.search.queries');
        }

        // Add search query and type to session to track it
        if (! empty($query) && ! empty($type)) {
            // Push type and search terms to session
            Session::push('instructors.search.types', $type);
            Session::push('instructors.search.queries', $query);
        }

        return Redirect::route('instructors.index');
    }

   /**
    * Perform mass actions on an instructor (POST-only)
    */
    public function mass()
    {
        return Redirect::route("instructors.index");
    }

    /**
     * Clear all search parameters
     */
    public function searchClear()
    {
        Session::forget('instructors.search.types');
        Session::forget('instrcutors.search.discipline');
        Session::forget('instructors.search.queries');
        Session::forget('instructors.search.filter');
        
        Flash::info('Search Cleared.');
        return Redirect::route('instructors.index');
    }

    /**
     * Remove a single search parameter
     */
    public function searchDelete($index)
    {
        Session::forget('instructors.search.types.'.$index);
        Session::forget('instructors.search.queries.'.$index);
        
        Flash::info('Search type removed.');
        return Redirect::route('instructors.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return View::make('core::instructors.create')->with([
            'disciplines' => Discipline::with('training', 'trainingPrograms')->get()
        ]);
    }

    /**
     * Login as this instructor
     */
    public function loginas($id, $license='')
    {
        $loggedUser = Auth::user();

        // is this user a facility (training program)?
        // if so, we need to check if this instructor should be accessible for them
        if ($loggedUser->isRole('Facility')) {
            $facility = Facility::with('instructors')->find($loggedUser->userable->id);

            if ($facility->instructors->isEmpty() || ! in_array($id, $facility->instructors->lists('id')->all())) {
                return Redirect::to('/');
            }
        }
        
        $instructor = Instructor::find($id);
        
        Auth::logout();

        if (! empty($license)) {
            // get facility_instructor record for this license
            $res = DB::table('facility_person')
                        ->where('tm_license', $license)
                        ->orWhere('old_license', $license)
                        ->first();

            // set discipline session
            if ($res) {
                $instructor->setSession($res->facility_id, $res->discipline_id, $res->tm_license);
            } else {
                Flash::danger('Failed to Login As '.Lang::choice('core::terms.instructor', 1).' with license '.$license);
                return Redirect::route('instructors.edit', $id);
            }
        } else {
            Auth::loginUsingId($instructor->user_id);
        }

        Auth::user()->setupSession();
        Flash::success('Logged in as '.Lang::choice('core::terms.instructor', 1).' <strong>'.$instructor->full_name.'</strong>');
        return Redirect::route('account');
    }

    /**
     * Intermediate login, select discipline/program
     * App::before filter redirects here
     */
    public function login()
    {
        $user       = Auth::user();
        $instructor = $user->userable;
        $roles      = $user->roles()->get();

        // get instructor discipline/programs
        $disciplines      = $instructor->disciplines;
        $activeFacilities = $instructor->activeFacilities;

        // person only has 1 facility and 1 role
        if ($activeFacilities && $activeFacilities->count() == 1 && $roles->count() == 1) {
            $facility   = $activeFacilities->first();
            $discipline = $disciplines->keyBy('id')->get($facility->pivot->discipline_id);

            $instructor->setSession($facility->id, $facility->pivot->discipline_id, $facility->pivot->tm_license);

            return Redirect::to('/')->withSuccess('Successfully logged in.');
        }
        
        // core::person.login
        return View::make('core::instructors.login')->with([
            'roles'            => $roles,
            'disciplines'      => $disciplines->lists('name', 'id')->all(),
            'activeFacilities' => $activeFacilities
        ]);
    }

    /**
     * Select a discipline/program to login as
     */
    public function updateLogin()
    {
        if (Input::get('license')) {
            $res = \DB::table('facility_person')
                        ->where('tm_license', Input::get('license'))
                        ->where('person_type', 'Instructor')
                        ->first();

            $facility   = Facility::find($res->facility_id);
            $discipline = Discipline::find($res->discipline_id);

            Auth::user()->userable->setSession($facility->id, $discipline->id, $res->tm_license);

            return Redirect::to('/')->withSuccess('Successfully logged in.');
        }

        return Redirect::route('instructors.login');
    }

        /**
         * Remap all students, discipline programs, etc from instructor to a new instructor
         */
        public function remapAndDelete($id)
        {
            $instructor = Instructor::find($id);

            if (is_null($instructor)) {
                return Redirect::to('/');
            }

            if ($instructor->isActive) {
                Flash::warning('Cannot Remap active ' . Lang::choice('core::terms.facility', 1));
                return Redirect::route('instructors.edit', $instructor->id);
            }

            if (Request::isMethod('post')) {
                if (! Input::get('remap_to')) {
                    Flash::danger('Please select an '.Lang::choice('core::terms.instructor', 1).'.');
                    return Redirect::back()->withInput();
                }

                $remapId         = Input::get('remap_to');
                $remapInstructor = Instructor::with([
                    'studentTrainings',
                    'allStudents',
                    'facilities'
                ])->find($remapId);

                if (is_null($remapInstructor)) {
                    Flash::danger('Please select an '.Lang::choice('core::terms.instructor', 1).' to remap records to.');
                    return Redirect::to('/');
                }


                // remap training programs
                \DB::table('facility_person')->where('person_type', 'Instructor')->where('person_id', $instructor->id)->update([
                    'person_id' => $remapInstructor->id
                ]);
                // remap student training
                \DB::table('student_training')->where('instructor_id', $instructor->id)->update([
                    'instructor_id' => $remapInstructor->id
                ]);
                // remap student ownership
                \DB::table('instructor_student')->where('instructor_id', $instructor->id)->update([
                    'instructor_id' => $remapInstructor->id
                ]);


                // record old instructors.license (RN#) to comments for new Instructor
                //  (gives us a short history)
                $appendComment = 'merged&deleted from instructor#' . $instructor->id . ' ' . $instructor->license . ' ' . date('m/d/Y H:i'); 
                if (empty($remapInstructor->comments)) {
                    $remapInstructor->comments = $appendComment;
                } else {
                    $remapInstructor->comments = $remapInstructor->comments;
                }
                $remapInstructor->save();

                // save instructor info we will delete
                //  (used for flash alert)
                $insName = $instructor->fullname;
                $insId   = $instructor->id;

                // get instructor role id
                $role = Role::where('name', 'Instructor')->first();


                // DELETE teachable trainings
                \DB::table('instructor_training')->where('instructor_id', $instructor->id)->delete();

                // DELETE user & roles
                //  (delete user record only if it doesnt have any other roles)
                if ($instructor->user->roles->count() > 1) {
                    // dual user, dont delete user record
                    // only delete instructor role
                    \DB::table('assigned_roles')->where('user_id', $instructor->user_id)->where('role_id', $role->id)->delete();
                }
                if ($instructor->user->roles->count() == 1 && $instructor->user->roles->first()->name == 'Instructor') {
                    \DB::table('assigned_roles')->where('user_id', $instructor->user_id)->delete();
                    \DB::table('users')->where('id', $instructor->user_id)->delete();
                }

                \Log::info('Deleted and Merged Instructor #'.$insId.' '. $insName, [
                    'deleted'   => $instructor->toJson(),
                    'deletedBy' => Auth::user()->userable->fullName
                ]);

                // DELETE instructor model
                \DB::table('instructors')->where('id', $instructor->id)->delete();

                
                Flash::success('Successfully remapped ' . $insName . '#' . $insId . ' to ' . $remapInstructor->fullname . '#' . $remapInstructor->id);
                Flash::success('Successfully deleted ' . Lang::choice('core::terms.instructor', 1) . ' ' . $insName . '#' . $insId . '.');
                
                return Redirect::route('instructors.edit', $remapInstructor->id);
            }

            // get all potential matching instructors
            $matches = Instructor::where('user_id', '!=', $instructor->user_id)
                                ->whereNested(function($q) use ($instructor) {
                                    $q->where('birthdate', $instructor->getOriginal('birthdate'));
                                    $q->orWhere('first', 'LIKE', substr($instructor->first, 0, 5).'%');
                                })
                                ->get();

            return View::make('core::instructors.remap')->with([
                'instructor' => $instructor,
                'matches'    => $matches
            ]);
        }

    /**
     * Change an instructors role
     * triggered on intermediate login page
     */
    public function swapRole()
    {
        $user = Auth::user();

        // forget discipline filter
        Session::forget('discipline');

        // redirect to person.login with the selected role
        $role   = Role::where('name', Input::get('role'))->first();
        $plural = strtolower($role->name).'s';

        // only need to update users table if requested role is not current role
        if ($user->userable_type != $role->name) {
            $record = DB::table($plural)->where('user_id', $user->id)->first();

            // update user record to request role
            $user->userable_type = $role->name;
            $user->userable_id   = $record->id;
            $user->save();

            Flash::success('Successfully swapped role to '.$role->name);
        }

        // remember we have already selected discipline/program for this login
        // used in App::before filter to not trigger role popup on instructors.login page if redirected there
        Session::set('discipline.selected', true);

        Session::put('user.current_role', $role->name);
        Session::put('user.current_role_id', $role->id);

        if ($user->userable_type == 'Instructor') {
            return Redirect::route('instructors.login', ['role' => true]);
        }

        return Redirect::to('/');
    }

    /**
     * Force re-select of discipline/program
     * Role already selected, dont prompt
     */
    public function resetLogin()
    {
        Session::forget('discipline');
        return Redirect::route('instructors.login', ['role' => true]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        // Fill instructor object with form data see if it validates
        if ($this->instructor->fill(Input::all())->validate()) {
            $instructor_id = $this->instructor->addWithInput();

            // it validated, try to create user
            if ($instructor_id) {
                return Redirect::route('instructors.edit', $instructor_id)->with('success', Lang::choice('core::terms.instructor', 1).' Added.');
            }
        }

        Session::flash('danger', 'There was an error creating the user.');
        return Redirect::back()->withInput()->withErrors($this->instructor->errors);
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $instructor = Instructor::with([
            'students',
            'teaching_trainings',
            'disciplines',
            'disciplines.training',
            'facilities',
            'facilities.disciplines',
            'studentTrainings' => function ($q) {
                $q->whereNull('student_training.archived_at');
            },
            'studentTrainings.student',
            'studentTrainings.facility',
            'studentTrainings.discipline',
            'studentTrainings.training'
        ])->findOrFail($id);
        
        // order by training
        $programs = [];
        foreach ($instructor->facilities as $f) {
            $programs[$f->pivot->training_id][] = $f;
        }

        // order students/facilities by discipline for easy access in view
        $disciplineInfo = [];
        foreach ($instructor->disciplines as $disc) {
            $disciplineInfo[$disc->id]['facilities'] = $instructor->facilities->filter(function ($f) use ($disc) {
                return $f->pivot->discipline_id == $disc->id;
            });

            $disciplineInfo[$disc->id]['students'] = $instructor->studentTrainings->filter(function ($st) use ($disc) {
                return $st->discipline_id == $disc->id;
            });

            $disciplineInfo[$disc->id]['trainings'] = $disc->training->filter(function ($tr) use ($disc) {
                return $tr->discipline_id = $disc->id;
            });
        }

        // check if instructor already working under ALL disciplines?
        $avDisciplines = array_diff(Discipline::all()->lists('id')->all(), $instructor->disciplines->lists('id')->all());


        // read-only for Agency
        if (Auth::user()->isRole('Agency')) {
            $this->disableFields();
        }

        return View::make('core::instructors.edit')->with([
            'instructor'     => $instructor,
            'programs'       => $programs,
            'facilities'     => Facility::where('actions', 'LIKE', '%Training%')->orderBy('name')->get(),
            'avDisciplines'  => $avDisciplines,
            'trainings'      => Training::all(),
            'disciplineInfo' => $disciplineInfo
        ]);
    }

    /**
     * Show archived Student Trainings connected to this instructor
     */
    public function archivedStudents($id, $disciplineId)
    {
        $instructor = Instructor::with([
            'disciplines',
            'studentTrainings' => function ($q) use ($disciplineId) {
                $q->where('student_training.discipline_id', $disciplineId)->whereNotNull('student_training.archived_at')->orderBy('started', 'DESC');
            },
            'studentTrainings.student',
            'studentTrainings.facility',
            'studentTrainings.discipline',
            'studentTrainings.training'
        ])->findOrFail($id);

        if (! in_array($disciplineId, $instructor->disciplines->lists('id')->all())) {
            return Redirect::route('facilities.index')->withDanger('Invalid Discipline');
        }

        return View::make('core::instructors.archived_students')->with([
            'instructor' => $instructor,
            'discipline' => $instructor->disciplines->keyBy('id')->get($disciplineId),
        ]);
    }

    /**
     * Archived record view, staff only
     */
    public function archived($id)
    {
        $instructor = Instructor::with([
            'teaching_trainings',
            'facilities'
        ])->findOrFail($id);

        return View::make('core::instructors.archived')->with([
            'instructor' => $instructor
        ]);
    }

    /**
     * Updating an archived record, minimal updates such as comments
     */
    public function archivedUpdate($id)
    {
        $i = Instructor::findOrFail($id);

        $i->comments = Input::get('comments');
        $i->save();

        return Redirect::route('instructors.archived', $id)->withSuccess(Lang::choice('core::terms.instructor', 1).' updated.');
    }

    /**
     * Generate a fake instructor record in json format
     */
    public function populate()
    {
        return Response::json($this->instructor->populate());
    }

    /**
     * Update instructor
     */
    public function update($id)
    {
        $instructor = Instructor::find($id);

        if ($this->instructor->fill(Input::all())->validate($instructor->user_id)) {
            // param ignores a given user ID (this one)

            if ($instructor->updateWithInput()) {
                return Redirect::route('instructors.edit', [$id])->with('success', Lang::choice('core::terms.instructor', 1).' updated.');
            }
        }
        
        // Flash a general error message if we have errors
        if (! empty($this->instructor->errors)) {
            Flash::danger('Error(s) found when updating ' . Lang::choice('core::terms.instructor', 1) . ', please fix them in the form below.');
        }

        return Redirect::back()->withInput()->withErrors($this->instructor->errors);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Restore an archived Instructor
     */
    /*public function restore($id)
    {
        $instructor = Instructor::withTrashed()->find($id);
        $instructor->activate();

        return Redirect::route('instructors.edit', $id)->withSuccess(Lang::choice('core::terms.instructor', 1).' activated.');
    }*/
}
