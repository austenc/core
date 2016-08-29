<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Session;
use Redirect;
use Config;
use \Sorter;
use \Certification;
use \Discipline;

class CertificationsController extends BaseController
{

    protected $certification;

    public function __construct(Certification $certification)
    {
        $this->certification = $certification;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return View::make('core::certifications.index')->with([
            'certs' => Certification::with('required_skills', 'required_exams')
                        ->orderBy('name', Sorter::order())
                        ->paginate(Config::get('paginate.default'))
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::certifications.create')->with([
            'disciplines' => Discipline::all()
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if ($this->certification->fill(Input::all())->validate()) {
            $id = $this->certification->addWithInput();

            if ($id) {
                return Redirect::route('certifications.edit', $id)->with('success', 'Certification Added.');
            }
        }

        Session::flash('danger', 'There was an error creating the Certification.');
        return Redirect::back()->withInput()->withErrors($this->certification->errors);
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
        $cert = Certification::with([
            'required_skills',
            'required_exams',
            'students'
        ])->findOrFail($id);

        $discipline = Discipline::with('exams', 'skills')->find($cert->discipline_id);

        return View::make('core::certifications.edit')->with([
            'cert'       => $cert,
            'discipline' => $discipline
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $cert = Certification::find($id);
        
        if ($this->certification->fill(Input::all())->validate()) {
            if ($cert->updateWithInput()) {
                return Redirect::route('certifications.edit', [$id])->with('success', 'Certification updated.');
            }
        }

        return Redirect::back()->withInput()->withErrors($this->certification->errors);
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
