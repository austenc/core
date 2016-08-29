<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Redirect;
use Session;
use \Agency;

class AgenciesController extends BaseController
{

    protected $agency;

    public function __construct(Agency $agency)
    {
        $this->agency = $agency;
    }

    /**
     * Display a listing of the resource.
     * GET /agencies
     *
     * @return Response
     */
    public function index()
    {
        return View::make('core::agency.index')->with([
            'agencies'    => Agency::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * GET /agencies/create
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::agency.create');
    }

    /**
     * Store a newly created resource in storage.
     * POST /agencies
     *
     * @return Response
     */
    public function store()
    {
        if ($this->agency->fill(Input::all())->validate()) {
            $agency_id = $this->agency->addWithInput();

            if ($agency_id) {
                return Redirect::route('agencies.edit', $agency_id)->with('success', 'New Agency user added.');
            }
        }

        Session::flash('danger', 'There was an error creating the user.');
        return Redirect::back()->withInput()->withErrors($this->agency->errors);
    }

    /**
     * Display the specified resource.
     * GET /agencies/{id}
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
     * GET /agencies/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        return View::make('core::agency.edit')->with([
            'agency' => Agency::find($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     * PUT /agencies/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $a = Agency::find($id);

        if ($this->agency->fill(Input::all())->validate($a->user_id)) {
            // param ignores a given user ID (this one)

            if ($a->updateWithInput()) {
                return Redirect::route('agencies.edit', $id)->with('success', 'Agency User updated.');
            }
        }
        
        return Redirect::back()->withInput()->withErrors($this->agency->errors);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /agencies/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
