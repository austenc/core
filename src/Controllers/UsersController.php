<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Input;
use Config;
use Redirect;
use App;
use Mail;
use Confide;
use Lang;
use Session;
use Request;
use Flash;
use DB;
use Response;
use Hash;
use Hdmaster\Core\Notifications\Notification;
use \User;
use \Role;
use \UserRepository;
use \Observer;
use \Proctor;
use \Actor;
use \Instructor;
use \Student;
use \Facility;
use \Discipline;

class UsersController extends BaseController
{

    /**
     * Lists the admin users
     * @return Response
     */
    public function index()
    {
        // We only want to get the admin users here for now
        $users = User::whereHas(
            'roles', function ($q) {
                $q->where('name', 'Admin');
            }
        )->paginate(Config::get('core.pagination.default'));

        return View::make('core::users.index')->with('users', $users);
    }

    /**
     * Toggle the sidebar state
     */
    public function toggleSidebar()
    {
        // if set, remove from session
        if (Session::has('user.sidebar_collapsed')) {
            Session::forget('user.sidebar_collapsed');
        } else {
            // otherwise add it to session
            Session::put('user.sidebar_collapsed', true);
        }
    }

    /**
     * Lists all notifications
     */
    public function notifications()
    {
        return View::make('core::users.notifications.index')
            ->withNotifications(Auth::user()->notifications()->paginate(Config::get('core.pagination.default')));
    }

    /**
     * A User viewing a single message
     * @return Response
     */
    public function singleNotification($id)
    {
        $notification = Notification::find($id);

        if (! $notification) {
            Flash::danger('Notification not found');
            return Redirect::to('/');
        }

        // mark this notification as read
        $notification->is_read = true;
        $notification->save();

        return View::make('core::users.notifications.detail')->withN($notification);
    }

    /**
     * Marks a given notification as unread (if userID matches)
     * @param  int $id 
     * @return Response
     */
    public function markNotificationUnread($id)
    {
        $n = Notification::find($id);

        // If logged in as same user
        if ($n->user_id == Auth::id()) {
            // mark unread
            $n->is_read = false;
            $n->save();
            return Redirect::route('notifications');
        }

        return Redirect::to('/');
    }

    /**
     * Deletes messages
     */
    protected function deleteNotifications($marked)
    {
        $marked = is_array($marked) ? $marked : [$marked];

        foreach ($marked as $id) {
            $this->deleteNotification($id, false);
        }

        Flash::success('Deleted notifications successfully.');
    }

    /**
     * Delete a single notification
     */
    protected function deleteNotification($id, $single = true)
    {
        $userId = Auth::id();

        if ($userId && is_numeric($id)) {
            Notification::where('user_id', $userId)->find($id)->delete();
        }

        if ($single === true) {
            Flash::success('Notification deleted.');
            return Redirect::route('notifications');
        }
    }

    /**
     * Update / process multiple notifications at once
     * @return Response
     */
    public function updateNotifications()
    {
        $marked = Input::get('notifications');

        // delete?
        if (array_key_exists("mark-trash", Input::all()) && ! empty($marked)) {
            $this->deleteNotifications($marked);
           
            return Redirect::route('notifications');
        }

        // mark any selected as unread
        $markUnread = Input::get('mark-unread');
        $markRead   = Input::get('mark-read');

        // figure out if we're marking all as read or un-read
        $toMark = null;

        if ($markUnread !== null) {
            $toMark = false;
        }

        if ($markRead !== null) {
            $toMark = true;
        }

        if ($marked && $toMark !== null) {
            $notifications = Notification::whereIn('id', $marked)->get();
            foreach ($notifications as $n) {
                $n->is_read = $toMark;
                $n->save();
            }
        }

        return Redirect::route('notifications');
    }

    /**
     * Displays the form for user editing self
     * 
     */
    public function edit($id)
    {
        return View::make('core::users.edit', ['user' => User::find($id)]);
    }

    /**
     * User updating their info
     */
    public function update($id)
    {
        $user                        = User::find($id);
        $password                    = Input::get('password');

        $user->email                 = Input::get('email');

        // Does it validate?
        if (! $user->validate($id)) {
            // NOT valid
            return Redirect::back()->withInput()->withErrors($user->errors());
        } else {
            // Valid update request
            // update email
           $user->save();

            // if password not empty, update it
            if (! empty($password)) {
                $input = array(
                    'token'                 => Input::get('token'),
                    'password'              => $password,
                    'password_confirmation' => Input::get('password_confirmation'),
                );

                // perform actual password reset
                $repo = App::make(UserRepository::class);
                $repo->resetPassword($input);
            }
            return Redirect::route('users.edit', [$id])->with('success', 'Updated successfully.');
        }
    }

    /**
     * Displays the form for account creation
     * @return  Illuminate\Http\Response
     */
    public function create()
    {
        //return View::make(Config::get('confide.signup_form'));
        return View::make('core::users.create');
    }

    /**
     * Stores new account
     * @return  Illuminate\Http\Response
     */
    public function store()
    {
        $repo = App::make(UserRepository::class);
        $user = $repo->signup(Input::all());

        if ($user->id) {
            Mail::send(Config::get('confide.email_account_confirmation'), compact('user'), function ($message) use ($user) {
                $message
                    ->to($user->email, $user->username)
                    ->subject(Lang::get('confide::confide.email.account_confirmation.subject'));
            });

            // Just add admin role dfor now
            $role = Role::where('name', '=', 'Admin')->first();
            $user->attachRole($role);

            return Redirect::route('users.index')->with('success', 'New Admin account added.');
            // return Redirect::route('users.login')
            //     ->with( 'notice', Lang::get('confide::confide.alerts.account_created') );
        } else {
            $error = $user->errors()->all(':message');

            return Redirect::route('users.create')
                ->withInput(Input::except('password'))
                ->with('error', $error);
        }
    }

    /**
     * Displays the login form
     * @return  Illuminate\Http\Response
     */
    public function login()
    {
        if (Confide::user()) {
            return Redirect::to('/');
        } else {
            return View::make(Config::get('confide.login_form'));
        }
    }

    /**
     * Attempt to do login
     * @return  Illuminate\Http\Response
     */
    public function do_login()
    {
        $repo  = App::make(UserRepository::class);
        
        $input    = Input::all();
        $username = Input::get('email');
        $pwd      = Input::get('password');

        // login via license or username/email
        if ($repo->numericLogin($input)) {
            return $this->redirectIntended();
        } elseif ($repo->login($input)) {
            return $this->redirectIntended();
        } else {
            // failed login
            if ($repo->isThrottled($input)) {
                $err_msg = Lang::get('confide::confide.alerts.too_many_attempts');
            } elseif ($repo->existsButNotConfirmed($input)) {
                $err_msg = Lang::get('confide::confide.alerts.not_confirmed');
            } elseif ($repo->isLocked()) {
                $err_msg = null;
            } else {
                $err_msg = Lang::get('confide::confide.alerts.wrong_credentials');
            }

            return Redirect::route('users.login')->withInput(Input::except('password'))->with('error', $err_msg);
        }
    }

    /**
     * Redirect to the intended url only if not a 'loginas' route
     */
    protected function redirectIntended($fallback = '/')
    {
        $intended = Session::get('url.intended');

        if (strpos($intended, 'loginas') === false) {
            return Redirect::intended($fallback);
        } else {
            return Redirect::to('/');
        }
    }

    /**
     * Attempt to confirm account with code
     * @param    string  $code
     * @return  Illuminate\Http\Response
     */
    public function confirm($code)
    {
        if (Confide::confirm($code)) {
            $notice_msg = Lang::get('confide::confide.alerts.confirmation');
            return Redirect::route('users.login')
                ->with('notice', $notice_msg);
        } else {
            $error_msg = Lang::get('confide::confide.alerts.wrong_confirmation');
            return Redirect::route('users.login')
                ->with('error', $error_msg);
        }
    }

    /**
     * Generate a new fake email address with our temp email domain
     * All users with fake email domain are forced to change upon login
     */
    public function fakeEmail($type)
    {
        $user  = new User;
        $email = $user->getFakeEmail($type);
        return Response::json($email);
    }

    public function generatePassword()
    {
        $user = new User;
        $pwd  = $user->generatePassword();
        return Response::json($pwd);
    }

    /**
     * Displays the forgot password form
     * @return  Illuminate\Http\Response
     */
    public function forgot_password()
    {
        return View::make(Config::get('confide.forgot_password_form'));
    }

    /**
     * Attempt to send change password link to the given email
     * @return  Illuminate\Http\Response
     */
    public function do_forgot_password()
    {
        if (Confide::forgotPassword(Input::get('email'))) {
            $notice_msg = Lang::get('confide::confide.alerts.password_forgot');
            return Redirect::route('users.login')
                ->with('info', $notice_msg);
        } else {
            $error_msg = Lang::get('confide::confide.alerts.wrong_password_forgot');

            return Redirect::route('users.forgot')
                ->withInput()
                ->with('danger', $error_msg);
        }
    }

    /**
     * Force a user to change their email
     */
    public function changeEmail()
    {
        $user = Auth::user();

        if (Request::isMethod('post')) {
            if ($user->validateEmailChange()) {
                $user->email = Input::get('new_email');
                $user->save();

                Session::set('email_updated', true);

                return Redirect::to('/');
            }

            Flash::danger('Failed to update email.');
            return Redirect::back()->withInput()->withErrors($user->errors);
        }

        return View::make('core::users.change_email')->withUser($user);
    }

    /**
     * Shows the change password form with the given token
     * @return  Illuminate\Http\Response
     */
    public function reset_password($token)
    {
        return View::make(Config::get('confide.reset_password_form'))
                ->with('token', $token);
    }

    /**
     * Attempt change password of the user
     * @return  Illuminate\Http\Response
     */
    public function do_reset_password()
    {
        $repo = App::make(UserRepository::class);
        $input = array(
            'token'                 =>Input::get('token'),
            'password'              =>Input::get('password'),
            'password_confirmation' =>Input::get('password_confirmation'),
        );

        // By passing an array with the token, password and confirmation
        if ($repo->resetPassword($input)) {
            $notice_msg = Lang::get('confide::confide.alerts.password_reset');
            return Redirect::route('users.login')
                ->with('info', $notice_msg);
        } else {
            $error_msg = Lang::get('confide::confide.alerts.wrong_password_reset');
            return Redirect::route('users.reset_password', $input['token'])
                ->withInput()
                ->with('danger', $error_msg);
        }
    }

    /**
     * Log the user out of the application.
     * @return  Illuminate\Http\Response
     */
    public function logout($timeout = false)
    {
        $nodisc = Session::has('nodiscipline');
        Confide::logout();
        Session::flush();
        Session::regenerate();

        // Show a warning if they were redirected here on timeout
        if ($timeout !== false) {
            Flash::info('You have been logged out due to inactivity.');
        } elseif ($nodisc) {
            // Show reason for not being logged in when a facility doesn't have 
            Flash::info('There is no active disciplines for this '.Lang::choice('core::terms.facility', 1).'. You cannot login without having an active discipline.');
        } else {
            Flash::info('You have been logged out.');
        }

        return Redirect::route('login');
    }

    /**
     * Allow a currently logged in user to change roles
     */
    public function changeRole()
    {
        // change the user's role to another role (assuming they actually have it)
        $user    = Auth::user();
        $newRole = Input::get('user_choose_role');
        $role    = Role::find($newRole);
        $roles   = $user->roles()->lists('name', 'role_id')->all();


        // if they actually have this role to swap to
        if (array_key_exists($newRole, $roles)) {
            // Update the userable_type and userable_id fields to keep current
            $model  = new $roles[$newRole];
            $person = $model->where('user_id', '=', Auth::id())->first();

            if ($person) {
                $user->userable_type = $roles[$newRole];
                $user->userable_id   = $person->id;
                $user->save();
            }

            // Set the session variables to the new role
            Session::put('user.current_role', $roles[$newRole]);
            Session::put('user.current_role_id', $newRole);

            // remember we have already selected discipline/program for this login
            // used in App::before filter to not trigger role popup on instructors.login page if redirected there
            Session::set('discipline.selected', true);

            Flash::success('Successfully swapped role to '.$role->name);
        }

        return Redirect::to('/');
    }

    /**
     * Display a page to add a role for this user
     */
    public function addRole($userId)
    {
        $user  = User::with('roles')->findOrFail($userId);
        $roles = Role::lists('name', 'name')->all();

        // Remove the current roles this user has
        $current = $user->roles->lists('name')->all();
        $roles = array_except($roles, array_merge($current, ['Student', 'Admin', 'Agency', 'Staff', 'Facility']));

        return View::make('core::users.add_role', [
            'user'  => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Store a new role (unless it already exists) for a user
     */
    public function storeRole()
    {
        $newRole = Input::get('new_role');
        $userId  = Input::get('user_id');

        if ($newRole && is_numeric($userId)) {
            // get the user and a list of their roles
            $user = User::with('userable', 'roles')->find($userId);

            // only add it if they don't have this role already
            if (! $user->hasRole($newRole)) {
                // does this user_id have a matching record in the new table? if so, we'll just update that record
                $table    = strtolower(str_plural($newRole));
                $existing = DB::table($table)->where('user_id', '=', $userId)->first();

                // record exists, just update its info
                if ($existing) {
                    // clone their info (or update it if it exists)
                    $model = $existing;
                } else {
                    // create a new record with the above information
                    $model = new $newRole;
                }

                // Set the information from the other model
                $model->first     = $user->userable->first;
                $model->middle    = $user->userable->middle;
                $model->last      = $user->userable->last;
                $model->birthdate = $user->userable->birthdate;
                $model->gender    = $user->userable->gender;
                $model->address   = $user->userable->address;
                $model->city      = $user->userable->city;
                $model->state     = $user->userable->state;
                $model->zip       = $user->userable->zip;
                $model->user_id   = $user->userable->user_id;
                
                // save it!
                $saved = $model->save();

                $role = Role::where('name', '=', $newRole)->first();

                // add the new role in assigned_roles
                if ($role) {
                    $user->attachRole($role);
                }

                if ($saved) {
                    Flash::success('New role added for '.$user->username);
                    // redirect to new role's page
                    return Redirect::route($table.'.edit', [$model->id]);
                }
            }
        }

        // fallback if submitted form info wasn't there
        Flash::danger('You must select a role to add to the user.');
        return Redirect::back();
    }

    // Redirects to the edit page for a person given a type and a user id
    public function editPersonByUser($type, $userId)
    {
        if (class_exists($type)) {
            $model  = new $type;
            $person = $model->where('user_id', '=', $userId)->first();
            $plural = str_plural(strtolower($type));

            if ($person) {
                return Redirect::route($plural . '.edit', $person->id);
            }
        }

        Flash::danger('Could not find record');
        return Redirect::back();
    }
}
