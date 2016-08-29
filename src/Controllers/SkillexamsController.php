<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Redirect;
use \Skillexam;
use \Training;
use \Exam;
use \Discipline;

class SkillexamsController extends BaseController
{

    protected $skillexam;

    public function __construct(Skillexam $skillexam)
    {
        $this->skillexam = $skillexam;
    }

    /**
     * Display a listing of the resource.
     * GET /skillexams
     *
     * @return Response
     */
    public function index()
    {
        return View::make('core::skills.exams.index')->with([
            'skillexams' => Skillexam::with([
                'required_trainings',
                'required_exams',
                'corequired_exams',
                'discipline'
            ])->orderBy('name', 'ASC')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * GET /skillexams/create
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::skills.exams.create')->with([
            'disciplines' => Discipline::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * POST /skillexams
     *
     * @return Response
     */
    public function store()
    {
        if ($this->skillexam->fill(Input::all())->validate()) {
            $id = $this->skillexam->addWithInput();

            if ($id) {
                return Redirect::route('skillexams.edit', $id)->with('success', 'Skill Exam added.');
            }
        }

        return Redirect::back()->withInput()
            ->withDanger('There was an error creating the Skill Exam.')
            ->withErrors($this->skillexam->errors);
    }

    /**
     * Display the specified resource.
     * GET /skillexams/{id}
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
     * GET /skillexams/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $skill = Skillexam::with([
            'required_trainings',
            'required_exams',
            'corequired_exams',
            'discipline',
            'tests'    => function ($query) {
                $query->orderBy('status');
            },
            'tests.tasks',
            'tasks'    => function ($query) {
                $query->orderBy('status');
                $query->orderBy('weight');
            },
            'tasks.steps',
            'tasks.setups'
        ])->findOrFail($id);

        return View::make('core::skills.exams.edit')->with([
            'skillexam'  => $skill,
            'discipline' => Discipline::with('exams', 'training')->find($skill->discipline_id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     * PUT /skillexams/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $skillexam = Skillexam::find($id);

        if ($skillexam->updateWithInput()) {
            return Redirect::route('skillexams.edit', $id)->with('success', 'Skill Exam updated.');
        }
        
        Session::flash('danger', 'There was an error updating Skill Exam.');
        return Redirect::back()->withInput();
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /skillexams/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
