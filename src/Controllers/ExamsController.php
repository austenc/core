<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Config;
use Redirect;
use Session;
use URL;
use \Sorter;
use \Subject;
use \Exam;
use \Training;
use \Skillexam;
use \Discipline;

class ExamsController extends BaseController
{

    protected $exam;
    protected $subject;

    public function __construct(Exam $exam, Subject $subject)
    {
        $this->exam = $exam;
        $this->subject = $subject;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $exams = Exam::with([
                'required_trainings',
                'required_exams',
                'required_skills',
                'corequired_skills',
                'active_testforms',
                'discipline'
            ])
            ->orderBy(Input::get('sort', 'name'), Sorter::order())
            ->paginate(Config::get('paginate.default'));

        return View::make('core::exams.index')->with([
            'exams' => $exams
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::exams.create')->with([
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
        if ($this->exam->fill(Input::all())->validate()) {
            $exam_id = $this->exam->addWithInput();
            if ($exam_id) {
                return Redirect::route('exams.edit', $exam_id)->with('success', 'Exam Added.');
            }
        }

        Session::flash('danger', 'There was an error creating the Exam.');
        return Redirect::back()->withInput()->withErrors($this->exam->errors);
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
        $exam = Exam::with([
            'testforms',
            'subjects',
            'discipline',
            'required_trainings',
            'required_exams',
            'required_skills',
            'corequired_skills',
            'testforms' => function ($query) {
                $query->orderBy('status');
            },
            'testforms.testitems'
        ])->findOrFail($id);

        // used to get other possible requirements within same discipline
        $discipline = Discipline::with([
            'training',
            'skills',
            'exams' => function ($q) use ($id) {
                $q->whereNotIn('id', [$id]);
            }
        ])->find($exam->discipline->id);

        return View::make('core::exams.edit')->with([
            'exam'        => $exam,
            'discipline'  => $discipline
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
        $exam = Exam::find($id);
        
        if ($this->exam->fill(Input::all())->validate()) {
            if ($exam->updateWithInput()) {
                return Redirect::route('exams.edit', [$id])->with('success', 'Exam updated.');
            }
        }

        Session::flash('danger', 'There was an error updating the Exam.');
        return Redirect::back()->withInput()->withErrors($this->exam->errors);
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
     * Remove (soft-delete) a subject from this Exam
     */
    public function removeSubject($id, $subjectId)
    {
        $exam    = Exam::with('subjects')->findOrFail($id);
        $subject = Subject::findOrFail($subjectId);

        // check subject exists within exam
        if (! in_array($subjectId, $exam->subjects->lists('id')->all())) {
            return Redirect::route('exams.index')->withError('Unable to delete Subject from Exam.');
        }

        $subject->delete();

        $url = URL::route('exams.edit', $id) . '#tab-exam-subjects';
        return Redirect::to($url)->withSuccess('Successfully removed Subject.');
    }
}
