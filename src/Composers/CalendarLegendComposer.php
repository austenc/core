<?php namespace Hdmaster\Core\Composers;

use Config;
use Discipline;
use Illuminate\Support\Collection;

class CalendarLegendComposer
{

    /**
     * Tries to find an appropriate title based upon route name
     * @param  View $view
     * @return View
     */
    public function compose($view)
    {
        // Map the colors to discipline names
        $colors      = Config::get('core.events.calendarColors');
        $disciplines = Discipline::all();
        $colorLookup = [];

        // Map each discipline to a color
        foreach ($disciplines as $d) {

            // Grab a color if one set for this discipline
            if (array_key_exists($d->id, $colors['disciplines'])) {
                $colorLookup[$colors['disciplines'][$d->id]] = $d->name;
            }
        }

        // Add past events color
        $colorLookup[$colors['past']] = 'Past Events';

        // Grab the disciplines from DB and colors from config
        $view->with([
            'colors'      => $colorLookup
        ]);
    }
}
