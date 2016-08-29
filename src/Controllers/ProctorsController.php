<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Lang;
use Input;
use Redirect;
use Session;
use Response;
use DB;
use Flash;
use \Proctor;
use \Facility;
use \Discipline;

class ProctorsController extends BaseController
{

    protected $proctor;

    public function __construct(Proctor $proctor)
    {
        $this->proctor = $proctor;
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
        $params           = $this->proctor->handleSearch();
        $params['filter'] = Session::get('proctors.search.filter');

        return View::make('core::proctors.index')->with($params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::proctors.create')->with([
            'disciplines' => Discipline::with('testSites')->get()
        ]);
    }

    /**
     * Login as this person
     * @param  int 	$id
     * @return Response
     */
    public function loginas($id)
    {
        $person = Proctor::find($id);
        Auth::logout();
        Auth::loginUsingId($person->user_id);
        Auth::user()->setupSession();

        Flash::success('Logged in as '.Lang::choice('core::terms.proctor', 1).' <strong>'.$person->full_name.'</strong>');
        return Redirect::route('account');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        // Validate the input
        if ($this->proctor->validate()) {
            $person = $this->proctor->doCreate('Proctor');

            if ($person) {
                return Redirect::route('proctors.edit', $person->id)->with('success', Lang::choice('core::terms.proctor', 1).' Added.');
            }
        }

        Session::flash('danger', 'There was an error creating the user.');
        return Redirect::back()->withInput()->withErrors($this->proctor->errors);
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $proctor = Proctor::with(['facilities', 'disciplines', 'events'])->findOrFail($id);

        // Add Disciplines chocies
        // (when none available button is hidden in view)
        $avDisciplines = array_diff(Discipline::all()->lists('id')->all(), $proctor->disciplines->lists('id')->all());

        return View::make('core::proctors.edit')->with([
            'proctor'        => $proctor,
            'avDisciplines'  => $avDisciplines,
            'disciplineInfo' => $proctor->getDisciplineInfo()
        ]);
    }

    /**
     * Archived record view, staff only
     */
    public function archived($id)
    {
        $proctor = Proctor::with('facilities')->findOrFail($id);

        return View::make('core::proctors.archived')->with([
            'proctor' => $proctor
        ]);
    }

    /**
     * Updating an archived record, minimal updates such as comments
     */
    public function archivedUpdate($id)
    {
        $p = Proctor::withTrashed()->find($id);

        $p->comments = Input::get('comments');
        $p->save();

        return Redirect::route('proctors.archived', $id)->withSuccess(Lang::choice('core::terms.proctor', 1).' updated.');
    }

    /**
     * Generate a fake proctor record in json format
     */
    public function populate()
    {
        return Response::json($this->proctor->populate());
    }

    /**
     * Update proctor
     */
    public function update($id)
    {
        $p = Proctor::find($id);

        if ($this->proctor->fill(Input::all())->validate($p->user_id)) {
            if ($p->doUpdate()) {
                Flash::success(Lang::choice('core::terms.proctor', 1).' updated.');
                return Redirect::route('proctors.edit', $id);
            }
        }

        Flash::danger('Error updating record.');
        return Redirect::back()->withInput()->withErrors($this->proctor->errors);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
