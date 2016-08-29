<?php namespace Hdmaster\Core\Composers;

use Auth;
use View;
use Lang;
use Session;
use \Pendingscore;

class NavigationComposer
{

    public function compose($view)
    {
        // Is a user logged in?
        if (Auth::check()) {

            // Grab the role name from the current session variable
            $currentRole   = Session::get('user.current_role');

            // If there's no current role, set one
            if (empty($currentRole)) {
                Auth::user()->setupSession();
                $currentRole = Session::get('user.current_role'); // refresh this variable
            }

            // try to get a navigation menu matching this user type
            $user          = Auth::user();
            $viewName      = 'core::navigation.' . strtolower($currentRole);
            $pendingScores = null;

            // if this user is an admin
            if ($user->ability(['Admin', 'Staff'], [])) {
                // grab the number of pending scores
                $pendingScores = Pendingscore::count();
            }

            // If there is no view matching this, default to empty and show nothing
            if (! View::exists($viewName)) {
                $viewName = '';
            }

            // Grab user's notifications
            $notifications       = $user->notifications();

            // Try to get the user's type according to language file (for easier overrides per-state)
            // Fallback to the role's name in the case there isn't a term defined
            $term     = 'terms.'.strtolower($currentRole);
            $typeFromLangfile = Lang::choice($term, 1);
            if ($typeFromLangfile && $typeFromLangfile !== $term) {
                $currentRole = ucwords($typeFromLangfile);
            }

            // Pass the user and user-specific view name to the navigation 
            $view->with([
                'user'                => $user,
                'userType'            => $currentRole,
                'userMenu'            => $viewName,
                'pendingScores'       => $pendingScores > 0 ? $pendingScores : null,
                'notifications'       => $notifications->limit(10)->get(),
                'unreadNotifications' => $notifications->unread()->count()
            ]);
        }
    }
}
