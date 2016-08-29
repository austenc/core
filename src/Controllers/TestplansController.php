<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Redirect;
use Response;
use Config;
use Flash;
use Session;
use \Testplan;
use \Subject;
use \Exam;

class TestplansController extends BaseController
{

    protected $item;

    public function __construct(Testplan $plan)
    {
        $this->plan = $plan;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return View::make('core::testplans.index')->with('exams', Exam::with('testplans')->get());
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($examId)
    {
        $exam = Exam::find($examId);

        if (is_null($exam)) {
            Flash::danger('Unknown Exam.');
            return Redirect::route('testplans.index');
        }

        $subjects = Subject::with('testitems')
            ->where('exam_id', $examId)
            ->where('client', Config::get('core.client.abbrev'))
            ->get();
            
        if ($subjects->isEmpty()) {
            Flash::danger('Exam <strong>'.$exam->name.'</strong> must have defined subjects before creating a testplan.');
            return Redirect::route('testplans.index');
        }

        return View::make('core::testplans.create')->with([
            'subjects' => $subjects,
            'exam'     => $exam
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if ($this->plan->validate()) {
            $plan = Testplan::create(Input::except('subjects'));

            if ($plan->id) {
                // now attach subjects list
                $plan->items_by_subject = json_encode(Input::get('subjects'));
                if ($plan->save()) {
                    return Redirect::route('testplans.index')->with('success', 'Testplan Created.');
                }
            }
        }
        
        Session::flash('danger', 'There were error(s) creating the testplan. Please fix below.');
        return Redirect::back()->withInput()->withErrors($this->plan->errors);
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
        $plan = Testplan::find($id);

        return View::make('core::testplans.edit')->with([
            'plan'     => $plan,
            'subjects' => Subject::where('exam_id', $plan->exam_id)->where('client', Config::get('core.client.abbrev'))->get()
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
        if ($this->plan->validate()) {
            $plan     = Testplan::find($id);
            $subjects = Input::get('subjects');

            //dd($subjects);

            if ($plan->update(Input::except(['_token', '_method', 'subjects']))) {
                $plan->items_by_subject = json_encode($subjects);
                
                if ($plan->save()) {
                    return Redirect::route('testplans.edit', [$id])->with('success', 'Testplan Updated.');
                }
            }
        }
        
        Session::flash('danger', 'There were error(s) updating the testplan. Please fix below.');
        return Redirect::back()->withInput()->withErrors($this->plan->errors);
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
     * Displays results / calls new generation attempt via ajax
     */
    public function generate($id)
    {
        $plan = Testplan::find($id);

        return View::make('core::testplans.generating')
                ->with('attempt', 1)
                ->with('max_attempts', $plan->max_attempts)
                ->with('testplan_id', $id);
    }

    /**
     * Generating a testform
     */
    public function generating($id)
    {
        $this->plan     = Testplan::find($id);
        $newItems       = $this->plan->generateForm();

        if ($newItems) {
            // assume if there's a lists() function that it's not a response
            if (method_exists($newItems, 'lists')) {
                Session::flash('testplan_id', $id);
                Session::flash('items', $newItems->lists('id')->all());

                return Response::json([
                    'success' => true
                ]);
            } else {
                // got a json / error response most likely
                return $newItems;
            }
        } else {
            // errors, go back
            return Response::json([
                'errors'   => $this->plan->errors->get('generate'),
                'messages' => $this->plan->messages->get('generate'),
                'attempt'  => Input::get('attempt', 1)
            ]);
        }
    }
}
