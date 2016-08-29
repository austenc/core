<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Lang;
use Input;
use Config;
use Redirect;
use Session;
use Response;
use Flash;
use Formatter;
use Request;
use \Sorter;
use Illuminate\Database\Eloquent\Collection;
use \Student;
use \User;
use \Facility;
use \Proctor;
use \Training;
use \Actor;
use \Observer;
use \Instructor;
use \StudentTraining;
use \Discipline;
use DB;
use Log;

use PragmaRX\ZipCode\Zipcode;

class FacilitiesController extends BaseController
{

    protected $facility;

    public function __construct(Facility $facility)
    {
        $this->facility = $facility;
        $this->beforeFilter('check-archived', ['only' => 'edit']);
        $this->beforeFilter('check-active', ['only' => 'archived']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $params                  = $this->facility->handleSearch();
        $params['searchTypes']   = Session::get('facilities.search.types');
        $params['searchQueries'] = Session::get('facilities.search.queries');
        $params['filter']        = Session::get('facilities.search.filter');
    
        return View::make('core::facilities.index')->with($params)->with(['disciplines' => Discipline::all()]);
    }

    public function search()
    {
        // get search parameters and save them to the session
        $query = Input::get('search');
        $type  = Input::get('search_type');

        // set the search discipline
        Session::set('facilities.search.discipline', Input::get('search-discipline'));

        // unless we are adding a search to this via the form, reset the params on each search
        if (! Input::get('add_search')) {
            Session::forget('facilities.search.types');
            Session::forget('facilities.search.queries');
        }

        // Add search query and type to session to track it
        if (! empty($query) && ! empty($type)) {
            // Push type and search terms to session
            Session::push('facilities.search.types', $type);
            Session::push('facilities.search.queries', $query);
        }

        return Redirect::route('facilities.index');
    }

    /**
     * Clear all seach terms and filters
     */
    public function searchClear()
    {
        Session::forget('facilities.search.types');
        Session::forget('facilities.search.queries');
        Session::forget('facilities.search.filter');
        Flash::info('Search cleared.');

        return Redirect::route('facilities.index');
    }

    /**
     * Remove a single search term
     */
    public function searchDelete($index)
    {
        Session::forget('facilities.search.types.'.$index);
        Session::forget('facilities.search.queries.'.$index);
        Flash::info('Search type removed.');

        return Redirect::route('facilities.index');
    }

    public function loginas($id)
    {
        $facility = Facility::find($id);
        
        Auth::logout();
        Auth::loginUsingId($facility->user_id);
        Auth::user()->setupSession();

        Flash::success('Logged in as '.Lang::choice('core::terms.facility', 1).' <strong>'.$facility->name.'</strong>');
        return Redirect::route('account');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // site types
        $siteTypes = array_combine($this->facility->siteTypes, $this->facility->siteTypes);
        array_unshift($siteTypes, "Select Site Type");

        $allDisciplines = Discipline::all();

        // get available parents for each discipline
        $avParents = [];
        foreach ($allDisciplines as $d) {
            $av = Facility::whereHas('allDisciplines', function ($q) use ($d) {
                $q->where('facility_discipline.discipline_id', $d->id);
            })
            ->where('status', 'LIKE', '%active%')
            ->where('actions', 'LIKE', '%Training%')
            ->orderBy('name', 'ASC')->get()->lists('name', 'id')->all();

            $avParents[$d->id] = [0 => "Select Parent"] + $av;
        }

        return View::make('core::facilities.create')->with([
            'siteTypes'   => $siteTypes,
            'avActions'   => $this->facility->avActions,
            'avParents'      => $avParents,
            'disciplines' => $allDisciplines
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if ($this->facility->validateCreate(Input::all())) {
            $facility_id = $this->facility->addWithInput();
            
            if ($facility_id) {
                return Redirect::route('facilities.edit', $facility_id)->with('success', Lang::choice('core::terms.facility', 1).' Added.');
            }
        }

        Session::flash('danger', 'There was an error creating the '.Lang::choice('core::terms.facility', 1).'.');
        return Redirect::back()->withInput()->withErrors($this->facility->errors);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show all past Events at a Facility
     */
    public function getPastEvents($id)
    {
        $facility = Facility::with([
            'events' => function($q) {
                $q->where('test_date', '<', date('Y-m-d'));
            },
            'events.exams', 
            'events.skills', 
            'events.proctor', 
            'events.observer'
        ])->find($id);

        if (is_null($facility)) {
            return Redirect::to('/');
        }

        return View::make('core::facilities.past_events')->with([
            'facility' => $facility
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        // normal lookup within active region
        $facility = Facility::with([
            'instructors',
            'instructors.teaching_trainings',
            'proctors',
            'actors',
            'observers',
            'affiliated.allDisciplines',
            'allDisciplines',
            'allDisciplines.exams',
            'allDisciplines.skills',
            'events' => function($q) {
                $q->where('test_date', '>=', date('Y-m-d'));
            },
            'events.exams',
            'events.skills',
            'events.proctor',
            'events.observer'
        ])->find($id);

        // site types
        $siteTypes = array_combine($this->facility->siteTypes, $this->facility->siteTypes);
        array_unshift($siteTypes, "Select Site Type");

        // training parent/child 
        $avParents     = [];
        $childPrograms = [];

        $disciplineStudents    = [];
        $disciplineInstructors = [];
        $disciplineAffiliates  = [];
        $disciplineTestTeam    = [];


        // foreach active discipline
        //   get available parents for each discipline
        //   init affiliate per discipline
        foreach ($facility->allDisciplines as $discipline) {
            // init students per discipline collection
            $disciplineStudents[$discipline->id] = new Collection;
            // init affiliates per discipline collection
            $disciplineAffiliates[$discipline->id] = new Collection;
            // init instructors
            $disciplineInstructors[$discipline->id] = $facility->instructors->filter(function ($i) use ($discipline) {
                return $i->pivot->discipline_id == $discipline->id;
            });
            // init test team
            $testTeam = new Collection;
            if (! $facility->observers->isEmpty()) {
                foreach ($facility->observers as $o) {
                    $testTeam->push($o);
                }
            }
            if (! $facility->proctors->isEmpty()) {
                foreach ($facility->proctors as $p) {
                    $testTeam->push($p);
                }
            }
            if (! $facility->actors->isEmpty()) {
                foreach ($facility->actors as $a) {
                    $testTeam->push($a);
                }
            }

            // filter test team users to only current discipline			
            $disciplineTestTeam[$discipline->id] = $testTeam->sortBy('last')->filter(function ($team) use ($discipline) {
                return $team->pivot->discipline_id == $discipline->id;
            });


            // programs that wont be allow as discipline parent
            // prevent: self-loop, child-loop, grandchild-loop
            $unavailableIds = array($facility->id);

            // get list of all other facilities that use current facility as parent
            // (these list be unavailable as parents)
            $children = Facility::whereHas('allDisciplines', function ($q) use ($discipline, $facility) {
                $q->where('facility_discipline.parent_id', $facility->id)
                  ->where('facility_discipline.discipline_id', $discipline->id);
            })->get();

            // check for grandchildren
            if (! $children->isEmpty()) {
                // add in child programs
                $unavailableIds = array_merge($unavailableIds, $children->lists('id')->all());

                foreach ($children as $child) {
                    // get grandchildren
                    $grands = DB::table('facility_discipline')
                        ->where('discipline_id', $discipline->id)
                        ->where('parent_id', $child->id)
                        ->lists('facility_id')->all();

                    // add in grandchild programs
                    $unavailableIds = array_merge($unavailableIds, $grands);
                }
            }

            // only child programs (this gets shown to user on view)
            // not showing grandchild programs
            $childPrograms[$discipline->id] = $children;

            // potential available parents per discipline
            $av = Facility::whereHas('allDisciplines', function ($q) use ($discipline) {
                $q->where('facility_discipline.discipline_id', $discipline->id);
            })
            ->where('actions', 'LIKE', '%Training%')
            ->whereNotIn('id', $unavailableIds)
            ->orderBy('name', 'ASC')
            ->get()
            ->lists('name', 'id')->all();

            // save available parents per discipline for dropdown selection UI
            $avParents[$discipline->id] = [0 => "Select Parent"] + $av;
        }

        // all students that trained here
        // active only (going to use a new page for archived students)
        $studentTrainings = StudentTraining::with('student', 'training', 'discipline')
                                ->where('facility_id', $id)
                                ->whereNull('archived_at')
                                ->orderBy('started', 'DESC')
                                ->get();

        // organize students by discipline
        foreach ($studentTrainings as $tr) {
            if (! array_key_exists($tr->discipline_id, $disciplineStudents)) {
                $disciplineStudents[$tr->discipline_id] = new Collection;
            }
            
            $disciplineStudents[$tr->discipline_id]->push($tr);
        }

        // organize affiliates by discipline
        foreach ($facility->affiliated as $aff) {
            $disciplineAffiliates[$aff->pivot->discipline_id]->push($aff);
        }

        // read-only for Agency
        if (Auth::user()->isRole('Agency')) {
            $this->disableFields();
        }

        return View::make('core::facilities.edit')->with([
            'facility'      => $facility,
            'avActions'     => $this->facility->avActions,
            'siteTypes'     => $siteTypes,
            'avParents'     => $avParents,
            'students'      => $disciplineStudents,
            'instructors'   => $disciplineInstructors,
            'testteam'      => $disciplineTestTeam,
            'affiliates'    => $disciplineAffiliates,
            'childPrograms' => $childPrograms
        ]);
    }

    /**
     * Shows a list of Training Programs that the current Test Site may be attached to
     *   (allows closed testevent access when scheduling)
     */
    public function attachAffiliate($id, $disciplineId)
    {
        $facility   = Facility::with([
            'disciplines',
            'affiliated' => function ($query) use ($disciplineId) {
                $query->where('facility_affiliated.discipline_id', $disciplineId);
            }
        ])->findOrFail($id);

        // check facility has discipline
        if (! in_array($disciplineId, $facility->disciplines->lists('id')->all())) {
            Flash::danger('Invalid Discipline');
            return Redirect::route('facilities.index');
        }

        // exclude current facility and already affiliated
        $excludeIds   = $facility->affiliated->lists('id')->all();
        $excludeIds[] = (int) $id;

        // can only connect to Training approved Facility
        // that also has requested discipline active
        $potential = Facility::whereHas('disciplines', function ($q) use ($disciplineId) {
            $q->where('facility_discipline.discipline_id', $disciplineId);
        })->whereNotIn('id', $excludeIds)->orderBy('name')->get();

        return View::make('core::facilities.modals.add_affiliate')->with([
            'facility'       => $facility,
            'discipline'     => $facility->disciplines->keyBy('id')->get($disciplineId),
            'affiliatedOpts' => $potential->lists('name', 'id')->all()
        ]);
    }

    /**
     * Create a new TestSite/TrainingProgram affiliate relation
     */
    public function storeAffiliate()
    {
        $facilityId     = Input::get('facility_id');
        $affiliateId    = Input::get('affiliate_id');
        $disciplineId   = Input::get('discipline_id');

        $facility = Facility::with([
            'disciplines',
            'affiliated' => function ($q) use ($disciplineId) {
                $q->where('facility_affiliated.discipline_id', $disciplineId);
            }
        ])->findOrFail($facilityId);

        $discipline = Discipline::findOrFail($disciplineId);

        // tab bookmark
        $currTab = '#tab-facility-discipline-'.strtolower($discipline->abbrev);

        // do some validation
        //   facility has discipline?
        //   affiliate relation already exists?
        if (! in_array($disciplineId, $facility->disciplines->lists('id')->all()) || in_array($affiliateId, $facility->affiliated->lists('id')->all()) || $affiliateId == 0) {
            return Redirect::route('facilities.edit', $facilityId);
        }

        // create relation
        $facility->affiliated()->attach($affiliateId, [
            'discipline_id' => $disciplineId
        ]);

        Flash::success('Successfully attached Affiliate Program for '.$discipline->name);
        return Redirect::route('facilities.edit', [$facilityId, $currTab]);
    }

    /**
     * Remove TestSite + TrainingProgram affiliate relation
     */
    public function removeAffiliate($id, $disciplineId, $affiliateId)
    {
        $facility   = Facility::with('disciplines')->findOrFail($id);
        $discipline = Discipline::findOrFail($disciplineId);

        if (! in_array($discipline->id, $facility->disciplines->lists('id')->all())) {
            return Redirect::route('facilities.edit', $id);
        }

        // tab bookmark
        $currTab = '#tab-facility-discipline-'.strtolower($discipline->abbrev);

        // remove from db
        DB::table('facility_affiliated')
            ->where('facility_id', $id)
            ->where('discipline_id', $disciplineId)
            ->where('affiliated_id', $affiliateId)
            ->delete();

        Flash::success('Successfully removed Affiliate Program from '.$discipline->name);
        return Redirect::route('facilities.edit', [$id, $currTab]);
    }

    /**
     * Separate page to show/search thru all archived students for a single discipline
     */
    public function archivedStudents($id, $disciplineId)
    {
        $facility = Facility::with('allDisciplines')->findOrFail($id);

        if (! in_array($disciplineId, $facility->allDisciplines->lists('id')->all())) {
            return Redirect::route('facilities.index')->withDanger('Invalid Discipline');
        }

        // all students trained at facility under discipline and also archived
        $students = StudentTraining::with('student', 'training')
                                ->where('facility_id', $id)
                                ->where('discipline_id', $disciplineId)
                                ->whereNotNull('archived_at')
                                ->orderBy('started', 'DESC')
                                ->get();

        return View::make('core::facilities.archived_students')->with([
            'facility'   => $facility,
            'discipline' => $facility->allDisciplines->keyBy('id')->get($disciplineId),
            'students'   => $students
        ]);
    }

    /**
     * Intermediate route when logging in as a facility with multiple disciplines
     * Choose a single discipline
     */
    public function login()
    {
        $facility = Facility::with('disciplines')->find(Auth::user()->userable_id);

        // Check if the number of deactivated disciplines is eqaul to the number of dicispines. If so, disallow facility login.
        if ($facility->disciplines->count() < 1) {
            Session::put('nodiscipline', 'none');
            return Redirect::to('/logout');
        }

        $facility = Auth::user()->userable;

        // program only does 1 discipline, select for them
        if ($facility->disciplines->count() == 1) {
            $discipline = $facility->disciplines->first();
            $facility->setSession($discipline);

            return Redirect::to('/')->withSuccess('Viewing '.$discipline->name.' records only.');
        }

        return View::make('core::facilities.login')->with([
            'facility' => $facility
        ]);
    }

    /**
     * Remove an existing discipline
     * All faculty/testteam connections will also be deactivated
     */
    public function deactivateDiscipline($id, $disciplineId)
    {
        $facility = Facility::with('disciplines')->findOrFail($id);

        if (! in_array($disciplineId, $facility->disciplines->lists('id')->all())) {
            return Redirect::route('facilities.edit', $id)->withDanger('Unable to find Discipline to deactivate.');
        }

        // deactivate facility discipline
        DB::table('facility_discipline')->where('facility_id', $facility->id)->where('discipline_id', $disciplineId)->update(['active' => false]);

        // deactivate all faculty/testteam relations
        DB::table('facility_person')->where('facility_id', $facility->id)->where('discipline_id', $disciplineId)->update(['active' => false]);

        return Redirect::route('facilities.edit', $facility->id)->withSuccess('Successfully deactivated Discipline.');
    }

    /**
     * When a user selects a discipline to login
     * POST route facilities.login
     */
    public function updateLogin()
    {
        if (Input::get('discipline_id')) {
            $discipline = Discipline::find(Input::get('discipline_id'));
            Auth::user()->userable->setSession($discipline);
        
            return Redirect::to('/')->withSuccess('Successfully logged in and viewing '.$discipline->name.' records only.');
        }

        return Redirect::route('facilities.login');
    }

    /**
     * Show specialized view for archived facility records
     * Admin/Staff only checked by permissions filter
     */
    public function archived($id)
    {
        $facility = Facility::withTrashed()->with([
            'allInstructors',
            'allInstructors.teaching_trainings',
            'allProctors',
            'allActors',
            'allObservers',
            'user' => function ($q) {
                $q->withTrashed();
            },
            'allStudents',
            'allStudents.trainings' => function ($query) use ($id) {
                $query->where('student_training.facility_id', $id);
            },
            'allAffiliated',
            'events' => function ($query) {
                $query->orderBy('test_date', 'DESC');
            },
            'events.exams',
            'events.skills',
            'events.proctor',
            'events.observer'
        ])->find($id);

        return View::make('core::facilities.archived')->with([
            'facility' => $facility
        ]);
    }

    /**
     * A minimal update for an archived record
     * Usually only comments updated
     * Staff/Admin only
     */
    public function archivedUpdate($id)
    {
        $f = Facility::withTrashed()->findOrFail($id);

        $f->comments = Input::get('comments');
        $f->save();

        return Redirect::route('facilities.archived', $id)->withSuccess(Lang::choice('core::terms.facility', 1).' updated.');
    }

    /**
     * Activate a facility
     * Previously soft-deleted facility and user record is reactivated
     * If facility doesn't have a user record for any reason, create them one
     */
    public function activate($id)
    {
        $facility = Facility::withTrashed()->findOrFail($id);
        $facility->activate();

        return Redirect::route('facilities.edit', $id)->withSuccess(Lang::choice('core::terms.facility', 1).' activated.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $f = Facility::find($id);

        if ($f->validateUpdate(Input::all())) {
            if ($f->updateWithInput()) {
                return Redirect::route('facilities.edit', $id)->with('success', Lang::choice('core::terms.facility', 1).' updated.');
            }
        }

        Session::flash('danger', 'There was an error updating the '.Lang::choice('core::terms.facility', 1).'.');
        return Redirect::back()->withInput()->withErrors($f->errors);
    }

    /**
     * Gets all instructors that work at this facility
     * doing discipline and training at facility
     * optional trainingID
     * only includes ACTIVE instructors
     * 
     * OLD
     * If trainingId, only pull instructors at facility teaching this specific training type
     * No trainingId, pull all instructors at facility
     */
    public function instructors($id, $disciplineId, $trainingId='')
    {
        $instructors = [];

        $facility = Facility::with([
            'activeInstructors' => function ($query) use ($disciplineId) {
                $query->wherePivot('discipline_id', $disciplineId)->orderBy('last', 'ASC');
            },
            'activeInstructors.teaching_trainings'
        ])->find($id);

        foreach ($facility->activeInstructors as $i) {
            // does this facility instructor do this training?
            if (! empty($trainingId) && ! in_array($trainingId, $i->teaching_trainings->lists('id')->all())) {
                continue;
            }

            $instructors[] = $i;
        }

        return Response::json($instructors);
    }

    /**
     * Get json of instructors at a facility
     * Used for Reports, fetching all instructors under a Training Program (Facility)
     * 
     * Use relation 'instructors', as opposed to 'activeInstructors'
     * because Deb (or any user) may want to run reports on a instructor/facility relation that was active at one point but no longer is
     */
    public function instructorsJson($id)
    {
        $facility    = Facility::with('instructors')->find($id);
        $instructors = [];

        // Does this facility have instructors?
        if ($facility->instructors) {
            $instructors = $facility->instructors->lists('fullName', 'id')->all();
        }

        return Response::json($instructors);
    }

    /**
    * Get list of instructors not already working for the facility under the selected discipline
    */
    /*public function getInstructors($id, $disciplineId)
    {
        $instructors = DB::select("SELECT * FROM `instructors` WHERE `id` NOT IN (SELECT `person_id` FROM `facility_person` WHERE `discipline_id` = " . $disciplineId . " AND `facility_id` = " . $id . " AND `person_type` = 'Instructor')");
        //$instructors = DB::table('')

        return Response::json($instructors);
    }*/

    /**
     * Get directions using google maps for this facility
     */
    public function directions($id)
    {
        $f = Facility::findOrFail($id);

        return View::make('core::facilities.directions')->with([
            'f' => $f
        ]);
    }

    /**
     * Generate a fake facility record in json format
     */
    public function populate()
    {
        return Response::json($this->facility->populate());
    }

    /**
     * Add a new Person/Facility relation
     */
    public function addPerson($id, $disciplineId='', $personType='')
    {
        $avPeople = [];

        // types of people allowed to be added to Facility
        $types = [
            'actor'      => 'Actor',
            'instructor' => 'Instructor',
            'observer'   => 'Observer',
            'proctor'    => 'Proctor'
        ];

        $facility = Facility::with('disciplines')->findOrFail($id);

        // person type set?
        // via direct navigate
        if (! empty($personType)) {
            if (! array_key_exists($personType, $types)) {
                return Redirect::route('facilities.edit', $id)->withDanger('Failed to add Person.');
            }
        }

        // discipline set?
        // via direct navigate
        if (! empty($disciplineId)) {
            $discipline = Discipline::find($disciplineId);

            if (is_null($discipline) || ! in_array($disciplineId, $facility->disciplines->lists('id')->all())) {
                return Redirect::route('facilities.edit', $id)->withDanger('Failed to add Person.');
            }
        }

        // if both discipline and person type are listed
        // we have enough information to populate dropdowns
        if ($disciplineId && $personType) {
            $avPeople = json_decode($this->getPeople($id, $disciplineId, $personType));
        }

        // store new person
        if (Request::isMethod('post')) {
            $disciplineId = Input::get('discipline_id');
            $personIds    = Input::get('person_id');
            $personType   = ucfirst(Input::get('person_type'));

            foreach ($personIds as $personId) {
                // add relation with a new unique tm license
                \DB::table('facility_person')->insert([
                    'facility_id'   => $id,
                    'discipline_id' => $disciplineId,
                    'person_id'     => $personId,
                    'person_type'   => $personType,
                    'tm_license'    => $facility->generateTestmasterLicense(),
                    'active'        => true
                ]);
            }

            $msg = 'Successfully added '.count($personIds).' '.(count($personIds) == 1 ? $personType : $personType.'s');
            return Redirect::route('facilities.edit', $id)->withSuccess($msg);
        }

        return View::make('core::facilities.add_person')->with([
            'facility'      => $facility,
            'disciplines'   => $facility->disciplines->lists('name', 'id')->all(),
            'personTypes'   => $types,
            'selPersonType' => $personType,
            'selDiscipline' => $disciplineId,
            'avPeople'      => $avPeople
        ]);
    }

    /**
     * Add New or reactivate existing Discipline
     */
    public function addDiscipline($id)
    {
        $facility = Facility::with('disciplines')->findOrFail($id);

        $avDiscIds     = array_diff(Discipline::all()->lists('id')->all(), $facility->disciplines->lists('id')->all());
        $avDisciplines = Discipline::whereIn('id', $avDiscIds)->get();

        return View::make('core::facilities.modals.add_discipline')->with([
            'facility'    => $facility,
            'disciplines' => $avDisciplines
        ]);
    }

    public function storeDiscipline($id)
    {
        if (! Input::get('discipline_id') || Input::get('discipline_id') == 0) {
            return Redirect::back()->withDanger('Missing discipline.');
        }

        $disciplineId = Input::get('discipline_id');
        $facility     = Facility::with('deactiveDisciplines')->findOrFail($id);

        // does facility already have this discipline just inactive?
        if (in_array($disciplineId, $facility->deactiveDisciplines->lists('id')->all())) {
            // reactivate
            DB::table('facility_discipline')
                ->where('facility_id', $facility->id)
                ->where('discipline_id', $disciplineId)
                ->update(['active' => true]);

            return Redirect::route('facilities.edit', $facility->id)->withSuccess("Successfully reactivated Discipline.");
        }

        $facility->disciplines()->attach($disciplineId, [
            'tm_license' => $facility->generateTestmasterLicense(),
            'active'     => true
        ]);

        return Redirect::route('facilities.edit', $facility->id)->withSuccess("Successfully added Discipline.");
    }

    /**
     * Get all people of a certain type that could potentially be added to Facility
     * 
     * People that are already working at Facility under Discipline will be excluded
     * People that have an existing relation at Facility (deactive) will also be excluded
     */
    public function getPeople($id, $disciplineId, $personType)
    {
        $relation = $personType.'s';
        $model    = ucfirst($personType);

        // ensure we have a valid person type
        if (! in_array($personType, ['actor', 'proctor', 'instructor', 'observer'])) {
            return json_encode([]);
        }

        // get eloquent facility
        $facility = Facility::with([
            // only people working within current discipline
            $relation => function ($q) use ($disciplineId) {
                $q->where('discipline_id', $disciplineId);
            }
        ])->findOrFail($id);

        // get all people of type
        $potential = $model::orderBy('last', 'ASC')->get();

        // remove all that already have a relation to facility
        // (these need to be activated, not newly created)
        $potential = $potential->filter(function ($obj) use ($facility, $relation) {
            return ! in_array($obj->id, $facility->$relation->lists('id')->all());
        });

        return json_encode($potential);
    }
}
