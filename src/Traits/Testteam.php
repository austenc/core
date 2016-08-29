<?php namespace Hdmaster\Core\Traits;

use Illuminate\Database\Eloquent\Collection as Collection;
use \Facility;
use \Discipline;
use \Testevent;

trait Testteam
{

    /**
     * All Test site facilities this proctor may work at
     */
    public function facilities()
    {
        return $this->morphToMany(Facility::class, 'person', 'facility_person')
                    ->withTrashed()
                    ->withPivot('discipline_id', 'tm_license', 'old_license', 'active')
                    ->orderBy('discipline_id', 'ASC');
    }
    public function activeFacilities()
    {
        return $this->facilities()->where('facility_person.active', '=', true);
    }
    public function inactiveFacilities()
    {
        return $this->facilities()->where('facility_person.active', '=', false);
    }
    public function activeTestSites()
    {
        return $this->activeFacilities()->where('actions', 'LIKE', '%Testing%');
    }

    public function disciplines()
    {
        return $this->morphToMany(Discipline::class, 'person', 'facility_person')
                    ->groupBy('facility_person.discipline_id')
                    ->withPivot('tm_license', 'old_license', 'active');
    }

    /**
     * All Events
     */
    public function events()
    {
        // if observer, hasMany, else morphMany
        $class = $this->getMorphClass();
        if ($class == 'Observer') {
            return $this->hasMany(Testevent::class);
        } else {
            return $this->morphMany(Testevent::class, strtolower($class));
        }
    }

    public function futureEvents()
    {
        return $this->events()->where('test_date', '>=', date('Y-m-d'))->orderBy('test_date', 'ASC');
    }

    /**
     * Organize discipline info facilities, test events, ..
     */
    public function getDisciplineInfo()
    {
        $disciplineInfo = [];

        foreach ($this->disciplines as $disc) {

            // discipline test sites
            $disciplineInfo[$disc->id]['facilities'] = $this->facilities->filter(function ($f) use ($disc) {
                return $f->pivot->discipline_id == $disc->id;
            });

            // discipline test events
            $disciplineInfo[$disc->id]['events'] = $this->events->filter(function ($evt) use ($disc) {
                return $evt->discipline_id == $disc->id;
            });
        }

        return $disciplineInfo;
    }

    /**
     * Gets people of given type available for certain test dates
     * Ensures all people work under the requested discipline 
     * and have an active relation with the testsite
     */
    public function availableOnDates($disciplineId, $testDates, $testSiteId, $ignoreId='')
    {
        if (! method_exists($this, 'futureEvents')) {
            return [];
        }

        // returned array of available people for event
        $available = array();

        // get current class/personType requested
        $reflect   = new \ReflectionClass($this);
        $className = $reflect->getShortName();
        $type      = strtolower($className);
        $idType    = $type.'_id';

        // find all people of current type with discipline also working at facility
        // must also have active relation with testsite
        $people = \DB::table('facility_person')
                        ->where('facility_id', '=', $testSiteId)
                        ->where('discipline_id', '=', $disciplineId)
                        ->where('person_type', '=', $className)
                        ->where('active', '=', true)
                        ->get();

        // ensure dates is an array
        if (! is_array($testDates)) {
            $testDates = array($testDates);
        }

        // format all test dates to m/d/Y
        array_walk($testDates, function (&$val, $i) {
            $val = date('m/d/Y', strtotime($val));
        });

        foreach ($people as $person) {
            $potentialConflicts = array();

            // find this person with futureEvents
            // to check for conflicting already scheduled events
            $currPerson = $className::with('futureEvents')->find($person->person_id);

            // get all events for testteam person
            // (if observer get all events observing, proctoring, and acting)
            $checkEvents = ($className == 'Observer') ? $currPerson->allFutureEvents : $currPerson->futureEvents;

            // if person has scheduled events
            if (! $checkEvents->isEmpty()) {
                // each event the person is scheduled for
                foreach ($checkEvents as $evt) {
                    if (! empty($ignoreId) && $evt->{$idType} == $ignoreId) {
                        $currPerson->potentialConflicts = array();
                        $available[] = $currPerson;

                        continue 2;
                    }

                    // found potential soft/hard conflicting event
                    if (in_array($evt->test_date, $testDates)) {
                        $potentialConflicts[] = $evt->test_date.' @ '.$evt->start_time;
                    }
                }
            }

            // add potential conflicts to person record
            $currPerson->potentialConflicts = $potentialConflicts;

            // after checking every event this person is scheduled for and they dont have any hard conflicts
            // make them available!
            $available[] = $currPerson;
        }

        $collectAvailable = new Collection($available);

        return $collectAvailable->sortBy('last');
    }
}
