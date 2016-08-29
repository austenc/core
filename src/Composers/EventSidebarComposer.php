<?php namespace Hdmaster\Core\Composers;

use \Facility;

class EventSidebarComposer
{

    public function compose($view)
    {
        $view->with(['cities' => $this->prep(Facility::lists('city', 'city')->all())]);

        // testing facilities only
        $facilities = Facility::all()->filter(function ($facility) {
            if ($facility->actions && in_array('Testing', $facility->actions)) {
                return true;
            }
        });

        $view->with(['facilityNames' => $this->prep($facilities->lists('name', 'id')->all())]);
    }

    private function prep($array)
    {
        $unique = array_unique($array);
        asort($unique);
        
        return array(null => 'Show All') + $unique;
    }
}
