<?php namespace Hdmaster\Core\Controllers;

use Auth;
use Redirect;
use View;
use Input;
use Session;
use \Student;
use \Instructor;
use \Proctor;
use \Observer;
use \Actor;
use \Facility;
use \Agency;
use Hdmaster\Core\Notifications\Flash;

class AccountController extends BaseController
{

    protected $user;

    public function __construct()
    {
        // Get currently logged in user
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // Get role for currently logged in user
        $role = strtolower($this->user->userable_type);

        // Show appropriate account page
        switch ($role) {
            case 'student':
                return $this->student();
            break;
            case 'instructor':
                return $this->instructor();
            break;
            case 'facility':
                return $this->facility();
            break;
            case 'proctor':
                return $this->proctor();
            break;
            case 'observer':
                return $this->observer();
            break;
            case 'actor':
                return $this->actor();
            break;
            case 'agency':
                return $this->agency();
            break;

            case 'admin':
            case 'staff':
                return Redirect::route('users.edit', [$this->user->id]);
            break;

            default:
                Flash::warning('User type not supported');
                return Redirect::to('/');
        }
    }

    /**
     * Centralized POST route for all account updates
     * Doesnt update other associated roles
     * Double-agent updates are handled by PersonTrait boot()
     */
    public function update($type, $id)
    {
        $this->doUpdate($type, $id);

        return Redirect::route('account');
    }

    private function doUpdate($type, $id = null)
    {
        switch (strtolower($type)) {
            case 'student':
            case 'instructor':
            case 'proctor':
            case 'observer':
            case 'actor':
            case 'agency':
            case 'facility':
                // if any of the above cases match, call corresponding update function
                $updateMethod = $type.'Update';
                if (method_exists($this, $updateMethod)) {
                    return $this->$updateMethod(Auth::id());
                } else {
                    return false;
                }
            break;

            default:
                // someone typed in a bad url?
                return Redirect::back();
        }
    }

// ----------------------------------------------------------------------------

    /**
     * Show the student's profile
     */
    private function student()
    {
        return View::make('core::students.account')->with([
            'student'    => Auth::user()->userable
        ]);
    }

    private function studentUpdate($userId)
    {
        $s       = new Student; // need separate for validation
        $student = Student::where('user_id', $userId)->first();

        // validates and updates
        if ($s->fill(Input::all())->validate($student->user_id)) {
            Flash::success('Record updated.');
            return $student->updateSelf();
        }

        Flash::danger('Error updating record.');
        return Redirect::back()->withInput()->withErrors($s->errors);
    }

// ----------------------------------------------------------------------------

    /**
     * Show the agency's profile
     */
    private function agency()
    {
        $agency = Auth::user()->userable;

        return View::make('core::agency.account')->with([
            'agency'    => $agency
        ]);
    }

    private function agencyUpdate($userId)
    {
        $a      = new Agency;                    // need separate for validation
        $agency = Agency::where('user_id', $userId)->first();

        // validates and updates
        if ($a->fill(Input::all())->validate($agency->user_id)) {
            Flash::success('Record updated.');
            return $agency->updateSelf();
        }

        Flash::danger('Error updating record.');
        return Redirect::back()->withInput()->withErrors($a->errors);
    }

// ----------------------------------------------------------------------------

    /**
     * Show the proctor's profile
     */
    private function proctor()
    {
        return View::make('core::proctors.account')->with([
            'proctor'    => Auth::user()->userable
        ]);
    }

    private function proctorUpdate($userId)
    {
        $p       = new Proctor; // need separate for validation
        $proctor = Proctor::where('user_id', $userId)->first();

        // validates and updates
        if ($p->fill(Input::all())->validate($proctor->user_id)) {
            Flash::success('Record updated.');
            return $proctor->updateSelf();
        }

        Flash::danger('Error updating record.');
        return Redirect::back()->withInput()->withErrors($p->errors);
    }

// ----------------------------------------------------------------------------

    /**
     * Show the observer's profile
     */
    private function observer()
    {
        return View::make('core::observers.account')->with([
            'observer'    => Auth::user()->userable
        ]);
    }

    private function observerUpdate($userId)
    {
        $p        = new Observer; // need separate for validation
        $observer = Observer::where('user_id', $userId)->first();

        // validates and updates
        if ($p->fill(Input::all())->validate($observer->user_id)) {
            Flash::success('Record updated.');
            return $observer->updateSelf();
        }

        Flash::danger('Error updating record.');
        return Redirect::back()->withInput()->withErrors($p->errors);
    }

// ----------------------------------------------------------------------------

    /**
     * Show the actor's profile
     */
    private function actor()
    {
        return View::make('core::actors.account')->with([
            'actor'    => Auth::user()->userable
        ]);
    }

    private function actorUpdate($userId)
    {
        $p = new Actor; // need separate for validation
        $actor = Actor::where('user_id', $userId)->first();

        // validates and updates
        if ($p->fill(Input::all())->validate($actor->user_id)) {
            Flash::success('Record updated.');
            return $actor->updateSelf();
        }

        Flash::danger('Error updating record.');
        return Redirect::back()->withInput()->withErrors($p->errors);
    }

// ----------------------------------------------------------------------------

    /**
     * Show the instructor's profile
     */
    private function instructor()
    {
        $instructor = Auth::user()->userable;

        return View::make('core::instructors.account')->with([
            'instructor'    => $instructor,
            'trainings'        => $instructor->teaching_trainings()->get()->lists('name')->all(),
            'facilities'    => $instructor->facilities()->get()
        ]);
    }

    private function instructorUpdate($userId)
    {
        $i          = new Instructor; // need separate for validation
        $instructor = Instructor::where('user_id', $userId)->first();

        // validates and updates
        if ($i->fill(Input::all())->validate($instructor->user_id)) {
            Flash::success('Record updated.');
            return $instructor->updateSelf();
        }

        Flash::danger('Error updating record.');
        return Redirect::back()->withInput()->withErrors($i->errors);
    }

// ----------------------------------------------------------------------------

    /**
     * Show the facility's profile
     */
    private function facility()
    {
        $facilityId = Auth::user()->userable_id;

        $facility = Facility::with([
            'activeInstructors',
            'disciplines'
        ])->find($facilityId);

        return View::make('core::facilities.account')->with([
            'facility' => $facility
        ]);
    }

    private function facilityUpdate($userId)
    {
        $facility = Facility::where('user_id', $userId)->first();

        if ($facility->validateUpdate(Input::all())) {
            Flash::success('Record updated.');
            return $facility->updateSelf();
        }

        Flash::danger('Error updating record.');
        return Redirect::back()->withInput()->withErrors($facility->errors);
    }
}
