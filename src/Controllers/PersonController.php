<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Lang;
use Input;
use Redirect;
use DB;
use URL;
use Response;
use Request;
use Session;
use Facility;
use Discipline;
use Role;

class PersonController extends BaseController
{

    private $classLookup = [
        'Skill' => 'Skilltest',
        'Task'  => 'Skilltask'
    ];

    public function __construct()
    {
    }

    /**
     * Activate/Deactivate a Person at Training/Testing Facility
     */
    public function toggle($type, $id, $disciplineId, $facilityId, $status)
    {
        $type            = strtolower($type);
        $togglableTypes  = ['observers', 'proctors', 'actors', 'instructors'];
        $togglableStatus = ['activate', 'deactivate'];
        $singular        = str_singular($type);

        // check status is accepted
        if (! in_array($status, $togglableStatus)) {
            return Redirect::back()->withDanger('Invalid toggle status.');
        }

        // check person type is accepted
        if (! in_array($type, $togglableTypes)) {
            return Redirect::back()->withDanger('Invalid person type.');
        }

        // activate
        if ($status == 'activate') {
            $res = DB::table('facility_person')
                        ->where('facility_id', '=', $facilityId)
                        ->where('discipline_id', '=', $disciplineId)
                        ->where('person_id', '=', $id)
                        ->where('person_type', '=', ucfirst($singular))
                        ->update(['active' => true]);
        }
        // deactivate
        else {
            $res = DB::table('facility_person')
                        ->where('facility_id', '=', $facilityId)
                        ->where('discipline_id', '=', $disciplineId)
                        ->where('person_id', '=', $id)
                        ->where('person_type', '=', ucfirst($singular))
                        ->update(['active' => false]);
        }

        $prevUrl = URL::previous();
        $type    = (strpos($prevUrl, $type) !== false) ? Lang::choice('core::terms.facility', 1) : Lang::choice('core::terms.'.$singular, 1);

        if ($res) {
            return Redirect::back()->withSuccess('Successfully '.$status.'d '.$type.'.');
        }

        return Redirect::back()->withDanger('Failed to '.$status.' '.$type.'.');
    }

    /**
     * Restore a Person record, bring back from archived status
     */
    public function restore($type, $id)
    {
        $restorableTypes = ['students', 'observers', 'proctors', 'actors', 'instructors'];

        // check person type
        if (! in_array($type, $restorableTypes)) {
            return Redirect::back()->withDanger('Invalid person type.');
        }

        // find person we intend to restore
        // only look within trashed users (i.e. soft-deleted)
        $singular = ucfirst(str_singular($type));
        $person = $singular::findOrFail($id);

        $person->activate();

        return Redirect::route($type.'.edit', $id)->withSuccess('Successfully restored '.Lang::choice('core::terms.' . str_singular($type), 1).'.');
    }

    /**
     * All archives are routed through this function
     * Archive (soft-delete) a record. Could be a person, skilltest, skilltask, etc...
     */
    public function archive($type, $id)
    {
        // what can be archived
        $archivableTypes  = ['observers', 'proctors', 'actors', 'instructors', 'students', 'facilities'];
        $archivableContentTypes = ['skills', 'tasks'];

        // allow archive?
        if (! in_array($type, $archivableTypes) && ! in_array($type, $archivableContentTypes)) {
            return Redirect::to('/');
        }

        // archiving test content
        // (skills and tasks need to become Skilltest... etc)
        if (in_array($type, $archivableContentTypes)) {
            $singular = $this->classLookup[ucfirst(str_singular($type))];
        }
        // archiving person
        else {
            $singular = ucfirst(str_singular($type));
        }

        // find model and archive
        $model = $singular::findOrFail($id);
        $model->archive();

        if (in_array($type, $archivableContentTypes)) {
            return Redirect::route($type.'.edit', $id)->withSuccess('Successfully archived '. Lang::choice('core::terms.' . str_singular($type), 1) .'.');
        }

        return Redirect::route($type.'.archived', $id)->withSuccess('Successfully archived '. Lang::choice('core::terms.' . str_singular($type), 1) .'.');
    }

    /**
     * Add a new Facility/Person connection
     * Assigns a unique 6 digit number for each relation
     * Should replace "instructors.program.add" route for instructor/facility
     * Discipline param available to bypass ajax lookup to use with codeception tests
     */
    public function addFacility($type, $id, $disciplineId='')
    {
        // ensure accepted type
        if (! in_array($type, ['observers', 'proctors', 'actors', 'instructors'])) {
            return Redirect::to('/');
        }

        // type is plural, make singular
        $singular = ucfirst(str_singular($type));

        // find associated model
        $person = $singular::with(['activeFacilities' => function ($q) use ($disciplineId) {
            if (! empty($disciplineId)) {
                $q->where('facility_person.discipline_id', $disciplineId);
            }
        }])->findOrFail($id);

        // get all disciplines
        $allDisciplines = Discipline::all();

        // init dropdowns
        // always show all disciplines
        $disciplineDD = array(0 => 'Select Discipline') + $allDisciplines->lists('name', 'id')->all();
        $facilityDD   = array(0 => 'Select '.Lang::choice('core::terms.facility', 1));

        // discipline from failed form submit
        if (Input::old('discipline_id') && Input::old('discipline_id') != 0) {
            $disciplineId = Input::old('discipline_id');
        }

        // Discipline set
        if (! empty($disciplineId)) {
            // check requested discipline actually exists
            if (! in_array($disciplineId, $allDisciplines->lists('id')->all())) {
                return Redirect::route($type.'.edit', $id)->withWarning('Requested Discipline does not exist.');
            }

            // get requested discipline with facilities
            $oldDiscipline = Discipline::with('facilities')->find($disciplineId);

            // filtering for testing or training?
            $filterAction = in_array($type, ['observers', 'proctors', 'actors']) ? '%Testing%' : '%Training%';

            // get all facilities working under requested discipline
            // filter out any facilities this user is already active at 
            // (inactive is OK to include)
            $facilities = Facility::whereHas('disciplines', function ($d) use ($disciplineId) {
                $d->where('facility_discipline.discipline_id', $disciplineId);
            })->where('actions', 'LIKE', $filterAction)
              ->whereNotIn('id', $person->activeFacilities->lists('id')->all())
              ->orderBy('name')
              ->get();

            $facilityDD += $facilities->lists('name', 'id')->all();
        }

        return View::make('core::person.add_facility')->with([
            'type'          => $type,
            'singular'        => $singular,
            'person'        => $person,
            'disciplines'   => $disciplineDD,
            'facilities'    => $facilityDD,
            'selDiscipline' => $disciplineId
        ]);
    }

    /**
     * Store a new Person/Facility connection
     */
    public function storeFacility($type, $id)
    {
        // must have both discipline and facility selected
        $disciplineId = Input::get('discipline_id');
        $facilityId   = Input::get('facility_id');

        if (empty($disciplineId) || empty($facilityId)) {
            return Redirect::back()->withInput()->withDanger('Discipline and '.Lang::choice('core::terms.facility', 1).' must both be selected.');
        }

        // ensure accepted type
        if (! in_array($type, ['observers', 'proctors', 'actors', 'instructors'])) {
            return Redirect::to('/');
        }

        // determine facility term
        $facilityTerm = in_array($type, ['observers', 'proctors', 'actors']) ? Lang::choice('core::terms.facility_testing', 1) : Lang::choice('core::terms.facility_training', 1);

        // type is plural, make singular
        $singular = ucfirst(str_singular($type));

        // find associated model
        $person = $singular::with([
            'inactiveFacilities' => function ($q) use ($disciplineId) {
                $q->where('facility_person.discipline_id', $disciplineId);
            },
            'facilities' => function ($q) use ($disciplineId) {
                $q->where('facility_person.discipline_id', $disciplineId);
            }
        ])->findOrFail($id);

        // brand new relation person+facility+discipline
        if (! in_array($facilityId, $person->facilities->lists('id')->all())) {
            DB::table('facility_person')->insert([
                'facility_id'   => $facilityId,
                'discipline_id' => $disciplineId,
                'person_id'     => $id,
                'person_type'   => $singular,
                'tm_license'    => $person->generateTestmasterLicense(),
                'active'        => true
            ]);

            return Redirect::route($type.'.edit', $id)->withSuccess('Successfully added new ' . $facilityTerm . '.');
        }
        // reactivate existing relation person+facility+discipline
        elseif (in_array($facilityId, $person->inactiveFacilities->lists('id')->all())) {
            DB::table('facility_person')
                ->where('facility_id', $facilityId)
                ->where('discipline_id', $disciplineId)
                ->where('person_id', $id)
                ->where('person_type', $singular)
                ->update(['active' => true]);

            return Redirect::route($type.'.edit', $id)->withSuccess('Successfully reactivated existing ' . $facilityTerm . '.');
        }
        
        return Redirect::route($type.'.edit', $id)->withSuccess('Failed to attach ' . $facilityTerm . '.');
    }

    /**
     * Get list of all facilities that a specific person is not already connected to (active or not)
     * Returned Json, used on person.facility.add route
     */
    public function availableFacilities($type, $id, $disciplineId)
    {
        // ensure accepted type
        if (! in_array($type, ['observers', 'proctors', 'actors', 'instructors'])) {
            return Response::json([]);
        }

        // check discipline exists
        $allDisciplines = Discipline::all();
        if (! in_array($disciplineId, $allDisciplines->lists('id')->all())) {
            return Response::json([]);
        }

        // type is plural, make singular
        $singular = ucfirst(str_singular($type));

        // filtering for testing or training?
        $filterAction = in_array($type, ['observers', 'proctors', 'actors']) ? '%Testing%' : '%Training%';

        // find associated model
        $person = $singular::with([
            'activeFacilities' => function ($q) use ($disciplineId) {
                $q->where('facility_person.discipline_id', $disciplineId);
            }
        ])->findOrFail($id);

        // get all facilities working under requested discipline
        // filter out any facilities this user is already active at 
        // (inactive is OK to include)
        $facilities = Facility::whereHas('disciplines', function ($d) use ($disciplineId) {
            $d->where('facility_discipline.discipline_id', $disciplineId);
        })->where('actions', 'LIKE', $filterAction)
          ->whereNotIn('id', $person->activeFacilities->lists('id')->all())
          ->orderBy('name')
          ->get();

        return Response::json($facilities);
    }
}
