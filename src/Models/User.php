<?php namespace Hdmaster\Core\Models\User;

use Zizaco\Confide\ConfideUser;
use Zizaco\Confide\ConfideUserInterface;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Hdmaster\Core\Notifications\Notification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
use Flash;
use Validator;
use Config;
use DB;
use Lang;
use Session;
use Input;
use Confide;
use \PrintProfile;
use \Role;
use \Observer;
use \Actor;
use \Proctor;
use \Student;
use \Instructor;

class User extends \Eloquent implements ConfideUserInterface
{
    use ConfideUser, EntrustUserTrait, SoftDeletes {
        ConfideUser::save insteadof EntrustUserTrait;
        SoftDeletes::restore insteadof EntrustUserTrait;
    }

    protected $morphClass = 'User';
    protected $with       = 'notifications';
    protected $dates      = ['deleted_at'];
    
    public static $rules = [
        // 'password'              => 'min:4|confirmed',
        // 'password_confirmation' => 'min:4'
    ];

    public static $changeEmailRules = [
        'new_email'     => 'required|email|unique:users,email',
        'confirm_email' => 'required|email|same:new_email'
    ];

    /**
     * A user has many notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('is_read')->orderBy('sent_at', 'DESC');
    }
    
    public function userable()
    {
        return $this->morphTo();
    }

    public function printProfile()
    {
        return $this->hasOne(PrintProfile::class);
    }

    public function printedKnowledge()
    {
        return $this->hasMany(Testattempt::class, 'printed_by', 'user_id');
    }
    public function printedSkill()
    {
        return $this->hasMany(Skillattempt::class, 'printed_by', 'user_id');
    }

    /**
     * Reset this user's password
     */
    public function resetPassword()
    {
        $result = false;
        $pwd    = Input::get('password');

        if (! empty($pwd)) {
            $input = [
                'password'              => $pwd,
                'password_confirmation' => Input::get('password_confirmation'),
            ];

            if ($this->exists) {
                $this->password              = $input['password'];
                $this->password_confirmation = $input['password_confirmation'];
                $result = $this->save();
            }
        }

        return $result;
    }


    /**
     * Add a notification for the user
     * @return Notification
     */
    public function notify()
    {
        $notification = new Notification;
        $notification->user()->associate($this);

        $user = Auth::user();

        if ($user && $user->ability(['Admin', 'Staff'], [])) {
            $name = method_exists($this->userable, 'getFullNameAttribute') ? $this->userable->full_name : '';

            // Get the short name of this class (without the namespace) through reflection
            $reflect = new \ReflectionClass($this->userable);
            $type = $reflect->getShortName();

            if ($type == 'Instructor') {
                $type = Lang::choice('core::terms.instructor', 1);
            }

            Flash::info('Sent Notification to '.ucwords($type).' <strong>'.$name.'</strong>');
        }

        return $notification;
    }

    /**
     * Checks a users current logged in role
     * Different from hasRole() (entrust function) which checks if a user HAS a role not necessarily logged in
     */
    public function isRole($role)
    {
        return $this->hasRole($role) && $this->userable_type == $role;
    }

    /**
     * Checks if current logged in user has temp email (temp.hdmaster.com)
     * If temp email detected, force them to change
     */
    public function hasFakeEmail()
    {
        $params = explode('@', $this->email);
        return isset($params[1]) && $params[1] == Config::get('core.tempEmailSuffix');
    }

    /**
     * Generate random password
     */
    public function generatePassword()
    {
        $length       = 8;
        $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
       
        return $randomString;
    }

    /**
     * Generates a new fake email address, using the temp email suffix
     */
    public function getFakeEmail($type='user')
    {
        $tempSuffix = Config::get('core.tempEmailSuffix');
        $i          = 0;
        $att        = 300;

        while ($i < $att) {
            $tempEmail = $type.rand(1, 10000).'@'.$tempSuffix;

            if ($this->isEmailUnique($tempEmail)) {
                return $tempEmail;
            }

            $i++;
        }

        return '';
    }

    /**
     * Checks if a given email is in use
     */
    public function isEmailUnique($email, $ignoreUserId='')
    {
        $base = DB::table('users')->where('email', $email);

        if (! empty($ignoreUserId)) {
            $base = $base->where('id', '!=', $ignoreUserId);
        }

        return $base->count() == 0;
    }

    /**
     * Validation for a user
     * @param  array
     * @return boolean
     */
    public function validate($ignore_user_id=null)
    {
        $rules = static::$rules;

        if (is_numeric($ignore_user_id)) {
            $rules['username'] = 'unique:users,username,'.$ignore_user_id;
            $rules['email']    = 'required|email|unique:users,email,'.$ignore_user_id;

            // on update, is there pwd?
            $pwd = Input::get('password');
            if (! empty($pwd)) {
                $rules['password'] = 'min:8|confirmed';
                $rules['password_confirmation'] = 'min:8';
                $this->password = $pwd;
                $this->password_confirmation = Input::get('password_confirmation');
            } else {
                // updating but password left empty
                unset($rules['password']);
                unset($rules['password_confirmation']);
            }
        }

        // Create a validation Instance
        $v = Validator::make($this->attributes, $rules);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }

    public function validateEmailChange()
    {
        $rules = static::$changeEmailRules;

        // Create a validation Instance
        $v = Validator::make(Input::all(), $rules, ['unique' => 'This email has already been taken.']);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }

    /** 
     * Returns a unique username given a first and last name
     * @param  [type] $last  [description]
     * @param  [type] $first [description]
     * @return string
     */
    public function unique_username($last, $first='')
    {
        // if firstname passed, grab the first initial
        $initial = strlen($first) > 1 ? $first[0] : '';

        $username = strtolower($initial.$last);
        $username = substr(preg_replace("/[^A-Za-z0-9]/", '', $username), 0, 20);
        
        $existing = DB::table('users')->whereRaw('username REGEXP ?', array('^('.$username.'){1}[0-9]*'))->count();
        
        if (empty($existing)) {
            // there are no users with this username, return it
            return $username;
        } else {
            $username .= $existing + 1;
            return $username;
        }
    }


    /**
     * Create a new user
     */
    public function createNew($newPwd)
    {
        $first    = Input::get('first');
        $last     = Input::get('last');
        $username = $this->unique_username($last, $first);

        // Create a new user
        $this->email                 = Input::get('email');
        $this->username              = $username;

        // set password as what was passed in
        $this->password              = $newPwd;
        $this->password_confirmation = $newPwd;

        // To require accounts to be confirmed via email remove this line and change Confide config file
        $this->confirmed = 1;
        return $this->save() !== false ? $this : false;
    }

    /**
     * Adds roles (or a role) to this user
     */
    public function addRoles($roles)
    {
        // Associate role(s)
        if (is_array($roles)) {
            // add all roles
            foreach ($roles as $r) {
                $this->addSingleRole($r);
            }
        } elseif (is_string($roles)) {
            // add this single role
            $this->addSingleRole($roles);
        }
    }

    private function addSingleRole($role)
    {
        $role = Role::where('name', '=', $role)->first();
        $this->attachRole($role);
    }

    /**
     * Sets up session variables for current row
     */
    public function setupSession()
    {
        // Use the userable type, unless it's somehow blank
        $current = $this->userable_type;
        $roles   = $this->roles();

        // use whatever is in the userable_type field as the default role on login
        $role = Role::where('name', $current)->first();

        // if there are no actual assigned roles, let's assign this one
        if ($roles->count() === 0 && ! empty($role)) {

            // Grab our person record if we have a matching model
            if (class_exists($role->name)) {
                // grab model and associated record
                $model = new $role->name;
                $person = $model->where('user_id', '=', $this->id)->first();

                // update user side with type and model id
                $this->userable_type = $role->name;
                $this->userable_id   = $person->id;
                $this->save();

                $this->attachRole($role);
            }
        }

        if ($role) {
            Session::put('user.current_role', $role->name);
            Session::put('user.current_role_id', $role->id);
        }
    }

    /**
     * Clears all login session vars
     * Similar to UsersController::logout but without the redirect
     */
    public function clearSession()
    {
        Confide::logout();

        Session::forget('testing');
        Session::forget('disable_menu');

        // forget all search filters
        Session::forget('students.search');
        Session::forget('actors.search');
        Session::forget('facilities.search');
        Session::forget('instructors.search');
        Session::forget('observers.search');
        Session::forget('proctors.search');

        // forget discipline/program filters
        Session::forget('discipline');
    }
}
