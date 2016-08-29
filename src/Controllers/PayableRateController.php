<?php namespace Hdmaster\Core\Controllers;

use Auth;
use Discipline;
use PayableRate;
use Redirect;
use View;
use Validator;
use Input;
use Session;
use Hdmaster\Core\Notifications\Flash;

class PayableRateController extends BaseController
{

    protected $payableRate;

    public function __construct(PayableRate $payableRate)
    {
        $this->payableRate = $payableRate;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $disciplines = Discipline::all()->lists('abbrev', 'id')->all();

        return View::make('core::accounting.payrates_create')->with([
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
        if ($this->payableRate->fill(Input::all())->validate()) {
            if ($this->payableRate->addWithInput()) {
                return Redirect::route('accounting.payrates')->with('success', 'Billing Rate Added.');
            }
        }
        Session::flash('danger', 'There was an error adding the payable rate.');
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
        $payableRate = PayableRate::find($id);
        $disciplines = Discipline::all()->lists('abbrev', 'id')->all();

        return View::make('core::accounting.payrates_edit')->with([
            'payrate' => $payableRate,
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
        if ($this->payableRate->fill(Input::all())->validate()) {
            if ($this->payableRate->updateWithInput()) {
                return Redirect::route('accounting.payrates')->with('success', 'Payable Rate Updated');
            }
        }
        Session::flash('danger', 'There was a problem updating the payable rate.');
        return Redirect::back()->withInput()->withErrors($this->payableRate->errors);
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
