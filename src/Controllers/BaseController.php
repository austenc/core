<?php namespace Hdmaster\Core\Controllers;

use Event;
use View;
use Session;

class BaseController extends \Controller
{

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if (! is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

    /**
     * Spoof a one-time flash message to disable form fields
     * We do this because Session::flash() is expected to be used with a 
     * Redirect afterward and if you make a view instead, the 'disable' 
     * stuff will last for 2 requests
     */
    protected function disableFields()
    {
        // flash the 'disableFields' key
        Session::flash('disableFields', true);

        // spoof laravel into thinking we're already on the next request
        Session::push('flash.old', 'disableFields');
    }
}
