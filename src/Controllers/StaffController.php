<?php namespace Hdmaster\Core\Controllers;

use \Admin;
use \Role;
use View;
use Input;
use Redirect;
use Session;
use Lang;
use Route;

class StaffController extends BaseController
{

    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

    protected $type = [
        'type'      => 'Staff',
        'routeBase' => 'staff'
    ];

    /**
     * Display a listing of the resource.
     * GET /staff
     *
     * @return Response
     */
    public function index($type = 'Staff')
    {
        // Get all id's with this type
        $ids    = Role::where('name', $type)->first()->users()->lists('user_id')->all();
        $people = $type::whereIn('user_id', $ids)->get();

        return View::make('core::staff.index')->with(array_merge(
            $this->type,
            ['people' => $people]
        ));
    }

    /**
     * Show the form for creating a new resource.
     * GET /staff/create
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::staff.create')->with($this->type);
    }

    /**
     * Store a newly created resource in storage.
     * POST /staff
     *
     * @return Response
     */
    public function store()
    {
        $person = new Admin;

        // Validate the input
        if ($person->fill(Input::except(['password', 'password_confirmation']))->validate()) {
            $created = $person->doCreate($this->type['type']);

            if ($created) {
                return Redirect::route(strtolower($this->type['routeBase']).'.edit', $created->id)->with([
                    'success' => $this->type['type'].' created.'
                ]);
            }
        }

        Session::flash('danger', 'There was an error creating '.$this->type['type'].'.');
        return Redirect::back()->withInput()->withErrors($person->errors);
    }

    /**
     * Display the specified resource.
     * GET /staff/{id}
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
     * GET /staff/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $currType = $this->type['type'];
        $person = $currType::with('user')->find($id);

        return View::make('core::staff.edit')->with(array_merge(
            $this->type,
            ['person' => $person]
        ));
    }

    /**
     * Update the specified resource in storage.
     * PUT /staff/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $currType  = $this->type['type'];
        $routeBase = $this->type['routeBase'];
        $p         = $currType::find($id);

        // does it validate?
        if ($this->admin->fill(Input::all())->validate($p->user_id)) {
            // 2nd param ignores a user id (this one) for unique username

            // validates, update
            if ($p->doUpdate()) {
                // successful update
                return Redirect::route($routeBase . '.edit', [$id])->withSuccess($currType.' updated.');
            }
        }

        return Redirect::back()->withInput()->withErrors($this->admin->errors);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /staff/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
