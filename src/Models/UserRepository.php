<?php namespace Hdmaster\Core\Models\UserRepository;

use Confide;
use Config;
use DB;
use Auth;
use Hash;
use Flash;
use Session;

/**
 * Class UserRepository
 *
 * This service abstracts some interactions that occurs between Confide and
 * the Database.
 */
class UserRepository
{
    private $isLocked;

    /**
     * Signup a new account with the given parameters
     * @param    array $input Array containing 'username', 'email' and 'password'.
     * @return  User User object that may or may not be saved successfully. Check the id to make sure.
     */
    public function signup($input)
    {
        $user = new \User;

        $user->username = array_get($input, 'username');
        $user->email    = array_get($input, 'email');
        $user->password = array_get($input, 'password');

        // The password confirmation will be removed from model
        // before saving. This field will be used in Ardent's
        // auto validation.
        $user->password_confirmation = array_get($input, 'password_confirmation');

        // Generate a random confirmation code
        $user->confirmation_code     = md5($user->username.time('U'));

        // Save if valid. Password field will be hashed before save
        $this->save($user);

        return $user;
    }

    public function isLocked()
    {
        return $this->isLocked;
    }

    /**
     * Attempts to login with the given credentials.
     * @param    array $input Array containing the credentials (email/username and password)
     * @return  boolean Success?
     */
    public function login($input)
    {
        if (! isset($input['password'])) {
            $input['password'] = null;
        }

        $identityColumns = ['email', 'username'];

        // attempt to login with username/email
        $logged = Confide::logAttempt($input, Config::get('confide.signup_confirm'), $identityColumns);

        if (! $logged) {
            return false;
        }

        $user = Auth::user();
        $user->setupSession();

        // check if account is locked
        // (change to $user->userable->isLocked soon as trait attribute StatusTrait is available)
        // (should return as array once accessed via trait)
        $locked = $user->userable->status;

        // check for locked account
        if (strpos($locked, 'lock') !== false) {
            // set class attribute to prevent default flash message (UserController) showing
            $this->isLocked = true;
            
            // clear all session vars set upon successful Confide login
            $user->clearSession();

            $instructions = 'Your account is locked.';

            // choose message to explain record lock
            if ($user->userable_type == 'Student') {
                // get active record from student_locks
                // this will need to change once StatusTrait is in place
                $res = DB::table('student_locks')
                        ->where('student_id', $user->userable_id)
                        ->where('lock_status', 'active')
                        ->first();

                if ($res) {
                    $instructions = $res->instructions . " Please contact Headmaster at " . Config::get('core.helpPhone');
                }
            }

            // show record lock message
            Flash::warning($instructions);

            return false;
        }

        // instructor logging in
        if ($user->userable_type == 'Instructor') {
            $programs = $user->userable->activeFacilities()->get();

            if ($programs->isEmpty()) {
                // clear all session vars set upon successful Confide login
                $user->clearSession();

                return false;
            }

            // only 1 facility and role
            // auto log them in to skip intermediate login discipline page
            elseif ($programs->count() == 1 && $user->roles()->count() == 1) {
                $program = $programs->first();
                $user->userable->setSession($program->pivot->facility_id, $program->pivot->discipline_id, $program->pivot->tm_license);
            }
        }
        
        // facility logging in
        elseif ($user->userable_type == 'Facility') {
            $disciplines = $user->userable->disciplines;

            // if more than 1 discipline, they must select login
            if ($disciplines->count() == 1) {
                $discipline = $disciplines->first();
                $user->userable->setSession($discipline);
            }
        }

        Flash::success('You have been logged in.');

        return true;
    }

    /**
     * Numeric tm_license login
     */
    public function numericLogin($input)
    {
        $username = $input['email'];
        $pwd      = $input['password'];

        // check if license matches for a facility or person
        if ($this->personLogin($username, $pwd) || $this->facilityLogin($username, $pwd)) {
            return true;
        }

        return false;
    }

    /**
     * Check for a person matching numeric license
     *
     * Instructor - must have program/discipline selected
     * Observer/Proctor/Actor - skips intermediate page
     * 
     * Students never login with a numeric license
     */
    private function personLogin($username, $pwd)
    {
        // get all records matching license
        $res = DB::table('facility_person')->whereNested(function ($q) use ($username) {
            $q->where('tm_license', $username)->orWhere('old_license', $username);
        })->where('active', true)->get();

        if ($res) {
            foreach ($res as $license) {
                // get person matching license supplied
                $personType = $license->person_type;
                $person     = $personType::find($license->person_id);

                // check password
                if (Hash::check($pwd, $person->user->password)) {
                    // check if record is locked
                    if ($person->isLocked) {
                        // prevent default message in UserController showing
                        $this->isLocked = true;

                        // default flash message
                        // locked student records never come thru this method (students dont use numeric logins)
                        Flash::danger('Your account is locked');

                        return false;
                    }

                    // update users.userable_type and users.userable_id
                    // to current requested Role matching tm_license in facility_person table
                    $person->user->userable_type = $license->person_type;
                    $person->user->userable_id   = $license->person_id;
                    $person->user->save();

                    Auth::loginUsingId($person->user->id);
                    Auth::user()->setupSession();

                    // set necessary discipline/program filters
                    // (this filter will only apply to instructors, test team users will see all events unfiltered)
                    $person->setSession($license->facility_id, $license->discipline_id, $license->tm_license);

                    // flash success message
                    Flash::success('Successfully logged in.');

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check for a facility matching numeric license
     */
    private function facilityLogin($username, $pwd)
    {
        // get all records matching license
        $res = DB::table('facility_discipline')->whereNested(function ($q) use ($username) {
            $q->where('tm_license', $username)->orWhere('old_license', $username);
        })->where('active', true)->get();

        if ($res) {
            foreach ($res as $license) {
                $facility = \Facility::find($license->facility_id);

                // check password
                if (Hash::check($pwd, $facility->user->password)) {
                    // correct password!
                    // check if record is locked
                    if ($facility->isLocked) {
                        // prevent default message in UserController showing
                        $this->isLocked = true;

                        // default flash message
                        // locked student records never come thru this method
                        Flash::danger('Your account is locked');

                        return false;
                    }
                    
                    Auth::loginUsingId($facility->user->id);
                    Auth::user()->setupSession();
                    
                    $currDiscipline = \Discipline::find($license->discipline_id);
                    $facility->setSession($currDiscipline);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if the credentials has been throttled by too
     * much failed login attempts
     *
     * @param  array $credentials Array containing the credentials (email/username and password)
     * @return  boolean Is throttled
     */
    public function isThrottled($input)
    {
        return Confide::isThrottled($input);
    }

    /**
     * Checks if the given credentials correponds to a user that exists but
     * is not confirmed
     * @param  array $credentials Array containing the credentials (email/username and password)
     * @return  boolean Exists and is not confirmed?
     */
    public function existsButNotConfirmed($input)
    {
        $user = Confide::getUserByEmailOrUsername($input);

        if ($user) {
            return ! $user->confirmed;
        }
    }

    /**
     * Resets a password of a user. The $input['token'] will tell which user.
     * @param    array  $input Array containing 'token', 'password' and 'password_confirmation' keys.
     * @return  boolean Success
     */
    public function resetPassword($input)
    {
        $result = false;
        $user   = Confide::userByResetPasswordToken($input['token']);

        if ($user) {
            $user->password              = $input['password'];
            $user->password_confirmation = $input['password_confirmation'];
            $result = $this->save($user);
        }

        return $result;
    }

    /**
     * Simply saves the given instance
     * @param    User $instance
     * @return  boolean           Success
     */
    public function save(\User $instance)
    {
        return $instance->save();
    }
}
