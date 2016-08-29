<?php namespace Hdmaster\Core\Composers;

use Session;

class FlashComposer
{

    public function compose($view)
    {
        $types         = ['warning', 'info', 'danger', 'success'];
        $flashMessages = [];
        $titles        = [];

        foreach ($types as $type) {
            $flashMessages[$type] = $this->getFlashMessages($type);
            $titles[$type] = Session::pull('flash_title._'.$type);
        }

        $view->with('flashMessages', $flashMessages);
        $view->with('flashTitles', $titles);
    }

    private function getFlashMessages($type)
    {
        if (Session::has($type) && Session::has('_'.$type)) {
            return array_merge((array) Session::pull($type), (array) Session::pull('_'.$type));
        } elseif (Session::has($type)) {
            return Session::pull($type);
        } elseif (Session::has('_'.$type)) {
            return Session::pull('_'.$type);
        } else {
            return null;
        }
    }
}
