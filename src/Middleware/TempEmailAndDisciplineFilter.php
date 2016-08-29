<?php

namespace Hdmaster\Core\Middleware;

use Closure;
use Session;
use \Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;

class TempEmailAndDisciplineFilter
{
    // roles that will cause redirect
    // intermediate login page select instructor/facility only 
    protected $redirectRoles = ['Facility', 'Instructor'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Do stuff before here
        $user = Auth::user();

        // if user is logged in and not on the logout page
        // perform additional checks: discipline select, temp email, ..
        if ($user && ! Request::is('logout')) {

            // did they update their temp email?
            $emailChanged = Session::pull('email_updated');
            if ($emailChanged) {
                Flash::success('Email has been updated.');
            }

            // check discipline filter is set
            // if redirectable user type and discipline not yet choosen and they arent on the login or email-change pages
            if (in_array($user->userable_type, $this->redirectRoles) && ! Session::has('discipline.id') && ! Request::is('*/login') && ! Request::is('email/change')) {
                // Facility
                if ($user->userable_type == 'Facility') {
                    return Redirect::route('facilities.login');
                }

                // Instructor
                elseif ($user->userable_type == 'Instructor') {
                    if (! Request::is('*/role/swap') && ! Request::is('users/change')) {
                        $params = [];

                        // prevent role select popup
                        if (Session::has('discipline.selected')) {
                            $params['role'] = true;
                        }
                        
                        return Redirect::route('instructors.login', $params);
                    }
                }
            } elseif ($user->hasFakeEmail() && ! Request::is('email/change')) {
                return Redirect::route('email.change');
            }
        }

        return $next($request);
    }
}
