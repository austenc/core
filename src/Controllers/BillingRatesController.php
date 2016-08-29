<?php namespace Hdmaster\Core\Controllers;

use Auth;
use Billing;
use BillingRate;
use Discipline;
use Redirect;
use View;
use Validator;
use Input;
use Session;
use Hdmaster\Core\Notifications\Flash;

class BillingRatesController extends BaseController
{

    protected $billingRate;

    public function __construct(BillingRate $billingRate)
    {
        $this->billingRate = $billingRate;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $billingRate = BillingRate::all();
        $disciplines = Discipline::all();

        return View::make('core::accounting.billrates')->with([
            'billingRates' => $billingRate,
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
        $disciplines = Discipline::all()->lists('abbrev', 'id')->all();
        
        return View::make('core::accounting.billrates_create')->with([
            'disciplines' => $disciplines
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if ($this->billingRate->fill(Input::all())->validate()) {
            if ($this->billingRate->addWithInput()) {
                return Redirect::route('accounting.billrates')->with('success', 'Billing Rate Added.');
            }
        }
        Session::flash('danger', 'There was an error adding the billing rate.');
        return Redirect::back()->withInput()->withErrors($this->billingRate->errors);
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
        $billingRate = BillingRate::find($id);
        $disciplines = Discipline::all()->lists('abbrev', 'id')->all();

        return View::make('core::accounting.billrates_edit')->with([
            'billingRate' => $billingRate,
            'disciplines' => $disciplines
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update()
    {
        if ($this->billingRate->fill(Input::all())->validate()) {
            if ($this->billingRate->updateWithInput()) {
                return Redirect::route('accounting.billrates')->with('success', 'Billing Rate Updated.');
            }
        }
        Session::flash('danger', 'There was an error updating the billing rate.');
        return Redirect::back()->withInput()->withErrors($this->billingRate->errors);
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
