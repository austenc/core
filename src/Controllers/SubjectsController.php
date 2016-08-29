<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Redirect;
use Session;
use Config;
use Flash;
use \Subject;
use \Exam;

class SubjectsController extends BaseController
{

    protected $subject;

    public function __construct(Subject $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return View::make('core::subjects.index')->with([
            'exams' => Exam::with('subjects')->get()
        ]);
    }

    public function create()
    {
        $exams = Exam::all();

        $viewParams = [
            'exams'   => $exams->lists('name', 'id')->all(),
            'selExam' => ''
        ];

        if (Input::get('exam')) {
            $viewParams['selExam'] = Input::get('exam');
        }

        if (Input::old('exam_id')) {
            $viewParams['selExam'] = Input::old('exam_id');
        }

        // selected exam?
        if (! empty($viewParams['selExam'])) {
            if (! in_array($viewParams['selExam'], $exams->lists('id')->all())) {
                Flash::danger('Unable to create Subject for unknown Exam.');
                return Redirect::route('subjects.create');
            }
        }

        return View::make('core::subjects.create')->with($viewParams);
    }

    public function store()
    {
        if ($this->subject->fill(Input::all())->validate()) {
            $id = $this->subject->addWithInput();

            if ($id) {
                return Redirect::route('subjects.edit', $id)->with('success', 'Subject Added.');
            }
        }

        Session::flash('danger', 'There was an error creating the Subject.');
        return Redirect::back()->withInput()->withErrors($this->subject->errors);
    }

    public function edit($id)
    {
        $subject  = Subject::with([
            'exam',
            'reportAs',
            'testitems'
        ])->findOrFail($id);
        
        $reportAs = Subject::where('id', '!=', $id)->where('exam_id', $subject->exam_id)->where('client', Config::get('core.client.abbrev'))->get()->lists('name', 'id')->all();

        return View::make('core::subjects.edit')->with([
            'subject'      => $subject,
            'reportAsOpts' => $reportAs
        ]);
    }

    public function update($id)
    {
        $subject = Subject::findOrFail($id);
        
        if ($this->subject->fill(Input::all())->validate()) {
            if ($subject->updateWithInput()) {
                return Redirect::route('subjects.edit', [$id])->with('success', 'Subject updated.');
            }
        }

        Session::flash('danger', 'There was an error updating the Subject.');
        return Redirect::back()->withInput()->withErrors($this->subject->errors);
    }
}
