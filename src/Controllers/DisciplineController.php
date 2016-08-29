<?php namespace Hdmaster\Core\Controllers;

use View;
use Flash;
use Log;
use Input;
use Redirect;
use Session;
use Response;
use Auth;
use Illuminate\Database\Eloquent\Collection as Collection;
use \Discipline;
use \Training;
use \Skillexam;
use \Exam;

class DisciplineController extends BaseController
{

    protected $discipline;

    public function __construct(Discipline $discipline)
    {
        $this->discipline = $discipline;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $disciplines = Discipline::with('exams', 'skills', 'training')->get();

        return View::make('core::discipline.index')->with([
            'disciplines' => $disciplines
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::discipline.create')->with(['exams' => Exam::all()])
            ->with(['skillexams' => Skillexam::all()])
            ->with(['trainings' => Training::all()]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if ($this->discipline->fill(Input::all())->validate()) {
            $id = $this->discipline->addWithInput();

            if ($id) {
                return Redirect::route('discipline.edit', $id)->with('success', 'Discipline Added.');
            }

            Session::flash('danger', 'The Discipline name already exists. Please use another');
            return Redirect::back()->withInput()->withErrors($this->discipline->errors);
        }
        Session::flash('danger', 'There was an error creating the Discipline');
        return Redirect::back()->withInput()->withErrors($this->discipline->errors);
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
        $discipline = Discipline::with([
            'exams',
            'skills',
            'training',
            'facilities',
            'instructors'
        ])->find($id);

        return View::make('core::discipline.edit')->with([
            'discipline' => $discipline,
            'exams'      => Exam::all(),
            'skillexams' => Skillexam::all(),
            'trainings'  => Training::all()
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
        $disc = Discipline::find($id);
        if ($disc->fill(Input::all())->validateEdit()) {
            if ($disc->updateWithInput()) {
                return Redirect::route('discipline.edit', [$id])->with('success', 'Discipline updated');
            }
        }
        return Redirect::back()->withInput()->withErrors($this->discipline->errors);
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

    /**
     * Get all training programs doing a certain discipline
     * Optional get param filter to restrict returned programs to only those having instructors
     */
    public function getTrainingFacilities($id)
    {
        $discipline = Discipline::with([
            'trainingPrograms',
            'agencyTrainingPrograms',
            'trainingPrograms.activeInstructors' => function ($query) use ($id) {
                $query->wherePivot('discipline_id', $id);
            }
        ])->find($id);

        $facilities = [];

        if ($discipline) {
            // return only special agency_only facilities
            if (Auth::user()->isRole('Agency')) {
                return Response::json($discipline->agencyTrainingPrograms);
            }

            foreach ($discipline->trainingPrograms as $f) {
                if (Input::get('filter')) {
                    if (! $f->activeInstructors->isEmpty()) {
                        $facilities[] = $f;
                    }
                } else {
                    $facilities[] = $f;
                }
            }
        }

        return Response::json($facilities);
    }
}
