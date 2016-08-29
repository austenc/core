<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Session;
use Redirect;
use Config;
use \Testform;
use \Testplan;
use \Testitem;
use \Testattempt;
use \Flash;

class TestformsController extends BaseController
{

    protected $form;

    public function __construct(Testform $form)
    {
        $this->form = $form;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $res = $this->form->handleSearch();

        return View::make('core::testforms.index')->with([
            'testforms' => $res['forms'],
            'all'       => $res['count']['all'],
            'active'    => $res['count']['active'],
            'draft'     => $res['count']['draft'],
            'archived'  => $res['count']['archived'],
            'status'    => Input::get('status')
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $items   = Session::get('items');
        $testplan_id = Session::get('testplan_id');

        if ($items && $testplan_id) {
            // find all the items
            $newItems = Testitem::whereIn('id', $items)->get();

            // get testplan
            $plan = Testplan::find($testplan_id);

            return View::make('core::testforms.create')
                    ->with('plan', $plan)
                    ->with('items', $newItems);
        }

        return Redirect::route('testplans.index');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        // Do some crude validation / specify some sensible defaults
        $items  = Input::get('testitems');
        $planId = Input::get('testplan_id');
        $name   = Input::get('name');
        $source = Input::get('scramble_source');

        // make sure we have things to make the new testform with
        if ($items && $planId) {
            $plan = Testplan::with('exam')->find($planId);

            // finally make sure we can get info about the testplan
            if ($plan) {
                // set default name if none provided
                $name = empty($name) ? $plan->exam->name.' Testplan '.date('Y-m-d') : $name;

                // create the new testform
                $form = Testform::create([
                    'exam_id'         => $plan->exam_id,
                    'testplan_id'         => $plan->id,
                    'legacy_id'       => 0,
                    'name'            => $name,
                    'client'          => Config::get('core.client.abbrev'),
                    'minimum'         => $plan->minimum_score,
                    'status'          => 'draft',
                    'scramble_source' => $source
                ]);
                
                if ($form) {
                    // make array of stuff to sync
                    $toSync = [];
                    foreach ($items as $k => $item) {
                        $toSync[$item] = ['ordinal' => $k+1];
                    }

                    // sync the testitems to the testform
                    $form->testitems()->sync($toSync);

                    // success!
                    return Redirect::route('testforms.edit', [$form->id])->with('success', 'New testform saved successfully.');
                }
            }
        }
        

        Session::flash('danger', 'There were error(s) saving the testform.');
        return Redirect::back()->withInput()->withErrors($this->form->errors);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $form = Testform::with([
            'testitems' => function ($query) {
                $query->orderBy('ordinal');
            },
            'testitems.theAnswer'
        ])->find($id);

        return View::make('core::testforms.show', [
            'form' => $form
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $form = Testform::with([
            'testitems' => function ($query) {
                $query->orderBy('ordinal');
            },
            'testitems.theAnswer'
        ])->find($id);

        // can only edit draft
        if ($form->status != 'draft') {
            return Redirect::route('testforms.show', $id);
        }

        return View::make('core::testforms.edit', ['form' => $form]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        if ($this->form->validate()) {
            // update the testform
            $this->form = Testform::find($id);
            $this->form->name = Input::get('name');
            $this->form->oral = Input::get('is_oral', 0);

            if ($this->form->save()) {

                // now sync the items tied to the form
                $items = Input::get('testitems');

                $toSync = [];
                foreach ($items as $k => $item) {
                    $toSync[$item] = ['ordinal' => $k+1];
                }

                $this->form->testitems()->sync($toSync);

                return Redirect::route('testforms.edit', [$id])->with('success', 'Testform Updated.');
            }
        }

        Session::flash('danger', 'There were error(s) updating the testform. ');
        return Redirect::back()->withInput()->withErrors($this->form->errors);
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
     * Activates a testform via POST
     * @param  int $id 
     * @return Response
     */
    public function activate($id)
    {
        $form = Testform::findOrFail($id);
        $form->status = 'active';
        $form->save();

        return Redirect::route('testforms.index')->withSuccess('Testform #'.$id.' activated successfully.');
    }

    /**
     * Activates a testform via POST
     * @param  int $id 
     * @return Response
     */
    public function archive($id)
    {
        $form = Testform::findOrFail($id);

        // Tests using this form that are still in the pipeline
        $used = Testattempt::where('testform_id', $id)
            ->whereIn('status', ['assigned', 'pending', 'started', 'unscored'])
            ->count();

        // This testform is still being used in the testing/scoring pipeline
        if ($used > 0) {
            Flash::warning('This testform cannot be archived. Tests in the scoring pipeline are still using it. Try again once those tests are finished.');
            return Redirect::route('testforms.show', $id);
        } else {
            $form->status = 'archived';
            $form->save();

            Flash::success('Testform #' . $id . ' archived.');
            return Redirect::route('testforms.index');
        }
    }

    /**
     * Clone (as scrambled) another test form
     */
    public function scrambled($id)
    {
        $form = Testform::with([
            'testitems' => function ($query) {
                $query->orderBy(\DB::raw('RAND()'));
            },
            'testitems.theAnswer'
        ])->find($id);

        return View::make('core::testforms.clone', ['form' => $form]);
    }
}
