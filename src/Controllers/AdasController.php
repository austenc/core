<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Redirect;
use Hdmaster\Core\Notifications\Flash;
use \Ada;

class AdasController extends BaseController
{

    protected $ada;
    protected $testTypes = [
        'knowledge' => 'Knowledge',
        'skill'     => 'Skill',
        'both'      => 'Both'
    ];

    public function __construct(Ada $ada)
    {
        $this->ada = $ada;
    }

    /**
     * Display a listing of the resource.
     * GET /adas
     *
     * @return Response
     */
    public function index()
    {
        return View::make('core::adas.index')->withAdas(Ada::orderBy('name')->get());
    }

    /**
     * Show the form for creating a new resource.
     * GET /adas/create
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::adas.create')->with([
            'testTypes' => $this->testTypes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * POST /adas
     *
     * @return Response
     */
    public function store()
    {
        // Does it validate?
        if ($this->ada->fill(Input::all())->validate()) {
            $paperOnly  = Input::get('paper_only');
            $extendTime = Input::get('extend_time');

            // it does, try to insert a new record
            $created = Ada::create([
                'name'        => Input::get('name'),
                'abbrev'      => Input::get('abbrev'),
                'test_type'   => Input::get('test_type'),
                'paper_only'  => $paperOnly !== null, //if paper only is not null, it's true / checked, else false
                'extend_time' => Input::get('extend_time')
            ]);

            // if it worked, flash success / redirect
            if ($created) {
                Flash::success('New ADA type created.');
                return Redirect::route('adas.index');
            }
        }

        Flash::danger('There was an error creating the ADA.');
        return Redirect::back()->withInput()->withErrors($this->ada->errors);
    }

    /**
     * Display the specified resource.
     * GET /adas/{id}
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
     * GET /adas/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        return View::make('core::adas.edit')->with([
            'testTypes' => $this->testTypes,
            'ada'       => Ada::find($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     * PUT /adas/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $ada = Ada::find($id);

        // Does it validate?
        if ($this->ada->fill(Input::all())->validate()) {
            $paperOnly  = Input::get('paper_only');
            $extendTime = Input::get('extend_time');

            // it does, try to insert a new record
            $updated = $ada->update([
                'name'        => Input::get('name'),
                'abbrev'      => Input::get('abbrev'),
                'test_type'   => Input::get('test_type'),
                'paper_only'  => $paperOnly !== null, //if paper only is not null, it's true / checked, else false
                'extend_time' => Input::get('extend_time')
            ]);

            // if it worked, flash success / redirect
            if ($updated) {
                Flash::success('ADA Updated.');
                return Redirect::route('adas.index');
            }
        }

        Flash::danger('There was an error updating the ADA.');
        return Redirect::back()->withInput()->withErrors($this->ada->errors);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /adas/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
