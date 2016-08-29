<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Lang;
use Input;
use Config;
use Redirect;
use Session;
use Response;
use DB;
use Flash;
use \Observer;
use \Facility;
use \Discipline;
use \PayableRate;
use \Sorter;

class ObserversController extends BaseController
{

    protected $observer;

    public function __construct(Observer $observer)
    {
        $this->observer = $observer;
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
        $params           = $this->observer->handleSearch();
        $params['filter'] = Session::get('observers.search.filter');

        return View::make('core::observers.index')->with($params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::observers.create')->with([
            'disciplines' => Discipline::with('testSites')->get(),
            'payableRates'   => PayableRate::all()
        ]);
    }

    /**
     * Login as this person
     * @param  int 	$id
     * @return Response
     */
    public function loginas($id)
    {
        $person = Observer::find($id);
        Auth::logout();
        Auth::loginUsingId($person->user_id);
        Auth::user()->setupSession();

        Flash::success('Logged in as '.Lang::choice('core::terms.observer', 1).' <strong>'.$person->full_name.'</strong>');
        return Redirect::route('account');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if ($this->observer->validate()) {
            $person = $this->observer->doCreate('Observer');

            if ($person) {
                return Redirect::route('observers.edit', $person->id)->with('success', Lang::choice('core::terms.observer', 1).' Added.');
            }
        }

        Session::flash('danger', 'There was an error creating the '.Lang::choice('core::terms.observer', 1).'.');
        return Redirect::back()->withInput()->withErrors($this->observer->errors);
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
     */
    public function edit($id)
    {
        $observer = Observer::with(['facilities', 'disciplines', 'events'])->findOrFail($id);

        // Add Disciplines chocies
        // (when none available button is hidden in view)
        $avDisciplines = array_diff(Discipline::all()->lists('id')->all(), $observer->disciplines->lists('id')->all());

        return View::make('core::observers.edit')->with([
            'observer'       => $observer,
            'avDisciplines'  => $avDisciplines,
            'disciplineInfo' => $observer->getDisciplineInfo(),
            'payableRates'   => PayableRate::all()
        ]);
    }

    /**
     * Archived record view, staff only
     */
    public function archived($id)
    {
        $observer = Observer::withTrashed()->with('facilities', 'events')->find($id);

        return View::make('core::observers.archived')->with([
            'observer' => $observer
        ]);
    }

    /**
     * Updating an archived record, minimal updates such as comments
     */
    public function archivedUpdate($id)
    {
        $o = Observer::withTrashed()->find($id);

        $o->comments = Input::get('comments');
        $o->save();

        return Redirect::route('observers.archived', $id)->withSuccess(Lang::choice('core::terms.observer', 1).' updated.');
    }

    /**
     * Generate a fake observer record in json format
     */
    public function populate()
    {
        return Response::json($this->observer->populate());
    }

    /**
     * Update observer
     */
    public function update($id)
    {
        $obs = Observer::findOrFail($id);

        if ($this->observer->fill(Input::all())->validate($obs->user_id)) {
            // 2nd param ignores a user id (this one) for unique username

            if ($obs->updateWithInput()) {
                Flash::success(Lang::choice('core::terms.observer', 1).' updated.');
                return Redirect::route('observers.edit', $id);
            }
        }

        Flash::danger('Error updating record.');
        return Redirect::back()->withInput()->withErrors($this->observer->errors);
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
