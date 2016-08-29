<?php namespace Hdmaster\Core\Composers;

use Route;
use Session;

class LayoutComposer
{

    /**
     * Tries to find an appropriate title based upon route name
     * @param  View $view
     * @return View
     */
    public function compose($view)
    {
        // Get the name of this route
        $title = Route::currentRouteName();

        // get rid of any dots or underscores in the route name
        $title = str_replace('.', ' ', $title);
        $title = str_replace('_', ' ', $title);

        // If the word 'Index' is at the end, get rid of it
        $title = preg_replace('/ index$/i', '', $title);

        // add the 'title' variable to the view
        $view->with([
            'title'     => ucwords($title),
            'bodyClass' => Session::has('user.sidebar_collapsed') ? 'sidebar-collapse' : ''
        ]);
    }
}
