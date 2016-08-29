<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Config;
use Session;
use Redirect;
use \Training;
use \Discipline;
use \Sorter;

class TrainingsController extends BaseController
{

    protected $training;

    public function __construct(Training $training)
    {
        $this->training = $training;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return View::make('core::trainings.index')->with([
            'trainings'    => $this->training
                            ->with('required_trainings')
                            ->orderBy(Input::get('sort', 'name'), Sorter::order())
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
        return View::make('core::trainings.create')->with([
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
        if ($this->training->fill(Input::all())->validate()) {
            if ($this->training->addWithInput()) {
                return Redirect::route('trainings.index')->with('success', 'Training Added.');
            }
        }

        Session::flash('danger', 'There was an error creating the Training.');
        return Redirect::back()->withInput()->withErrors($this->training->errors);
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
        $training   = Training::with('required_trainings')->findOrFail($id);
        $discipline = Discipline::with(['training' => function ($q) use ($id) {
            $q->whereNotIn('id', [$id]);
        }])->find($training->discipline_id);

        return View::make('core::trainings.edit')->with([
            'training'   => $training,
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
        $training = Training::findOrFail($id);
        
        if ($this->training->fill(Input::all())->validate()) {
            if ($training->updateWithInput()) {
                return Redirect::route('trainings.edit', [$id])->with('success', 'Training updated.');
            }
        }

        return Redirect::back()->withInput()->withErrors($this->training->errors);
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
