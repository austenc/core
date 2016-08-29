<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Lang;
use Input;
use Config;
use Redirect;
use Response;
use Session;
use DB;
use Flash;
use \Sorter;
use \Actor;
use \Facility;
use \Discipline;

class ActorsController extends BaseController
{

    protected $actor;

    public function __construct(Actor $actor)
    {
        $this->actor = $actor;
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
        $params           = $this->actor->handleSearch();
        $params['filter'] = Session::get('actors.search.filter');

        return View::make('core::actors.index')->with($params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::actors.create')->with([
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
        $person = Actor::find($id);
        Auth::logout();
        Auth::loginUsingId($person->user_id);
        Auth::user()->setupSession();

        Flash::success('Logged in as '.Lang::choice('core::terms.actor', 1).' <strong>'.$person->full_name.'</strong>');
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
        if ($this->actor->validate()) {
            $person = $this->actor->doCreate('Actor');

            if ($person) {
                return Redirect::route('actors.edit', $person->id)->with('success', Lang::choice('core::terms.actor', 1).' Added.');
            }
        }

        Session::flash('danger', 'There was an error creating the '. Lang::choice('core::terms.actor', 1) .'.');
        return Redirect::back()->withInput()->withErrors($this->actor->errors);
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
        $actor = Actor::with(['facilities', 'disciplines', 'events'])->findOrFail($id);

        // Add Disciplines chocies
        // (when none available button is hidden in view)
        $avDisciplines = array_diff(Discipline::all()->lists('id')->all(), $actor->disciplines->lists('id')->all());

        return View::make('core::actors.edit')->with([
            'actor'          => $actor,
            'avDisciplines'  => $avDisciplines,
            'disciplineInfo' => $actor->getDisciplineInfo()
        ]);
    }

    /**
     * Archived record view, staff only
     */
    public function archived($id)
    {
        $actor = Actor::with('events')->withTrashed()->find($id);

        return View::make('core::actors.archived')->with([
            'actor' => $actor
        ]);
    }

    /**
     * Updating an archived record, minimal updates such as comments
     */
    public function archivedUpdate($id)
    {
        $a = Actor::withTrashed()->find($id);

        $a->comments = Input::get('comments');
        $a->save();

        return Redirect::route('actors.archived', $id)->withSuccess(Lang::choice('core::terms.actor', 1).' updated.');
    }

    /**
     * Generate a fake actor record in json format
     */
    public function populate()
    {
        return Response::json($this->actor->populate());
    }

    /**
     * Update actor
     */
    public function update($id)
    {
        $p = Actor::findOrFail($id);

        if ($this->actor->fill(Input::all())->validate($p->user_id)) {
            // 2nd param ignores a user id (this one) for unique username

            if ($p->doUpdate()) {
                Flash::success(Lang::choice('core::terms.actor', 1).' updated.');
                return Redirect::route('actors.edit', $id);
            }
        }

        Flash::danger('Error updating record.');
        return Redirect::back()->withInput()->withErrors($this->actor->errors);
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
