<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Input;
use Config;
use Session;
use Redirect;
use \Testitem;
use \Vocab;
use \Stat;
use \Distractor;
use \Exam;
use \Testform;
use \Sorter;

class TestitemsController extends BaseController
{

    protected $item;

    public function __construct(Testitem $item)
    {
        $this->item = $item;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $res = $this->item->handleSearch();

        return View::make('core::testitems.index')->with([
            'items'  => $res['items'],
            'all'    => $res['count']['all'],
            'active' => $res['count']['active'],
            'draft'  => $res['count']['draft']
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('core::testitems.create')
            ->with('vocab', json_encode(Vocab::all()->lists('word')->all()))
            ->with('exams', Exam::with('subjects')->get())
            ->with('options', Config::get('core.knowledge.options'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        // Valid?
        if ($this->item->validate()) {
            // Create testitem
            $testitem = Testitem::create([
                'stem'        => Input::get('stem'),
                'auth_source' => Input::get('auth_source'),
                'user_id'     => Auth::user()->id,
                'comments'    => Input::get('comments')
            ]);

            // no answer since distractors don't exist yet
            $answer = 0;

            // Add distractors, only a few so a foreach is fine
            $distractors = Input::get('distractors');
            foreach ($distractors as $k=>$d) {
                if (! empty($d)) {
                    // create new distractor
                    $new = Distractor::create(['testitem_id' => $testitem->id, 'content' => $d]);

                    // set the answer for creating the testitem
                    if (Input::get('answer') == $k && ! empty($new)) {
                        $testitem->answer = $new->id;
                        $testitem->save();
                    }
                }
            }

            // Item created?
            if ($testitem) {
                $testitem->updateEnemies();
                $testitem->updateExamSubjects();
                $testitem->updateVocab();

                return Redirect::route('testitems.edit', $testitem->id)->withSuccess('Successfully created new Testitem.');
            }
        }

        Session::flash('danger', 'There were error(s) creating the item. Please fix below.');
        return Redirect::back()->withInput()->withErrors($this->item->errors);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $item = Testitem::with('subjects', 'testforms', 'stats')->find($id);

        return View::make('core::testitems.show')->with([
            'item'          => $item,
            'enemies'       => $item->enemyString,
            'exams'         => Exam::all(),
            'item_subjects' => $item->subjects->lists('id', 'exam_id')->all(),
            'options'       => Config::get('core.knowledge.options')
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
        $item = Testitem::with('subjects', 'testforms', 'stats')->find($id);

        // only the 'show' page on active testitems
        if ($item->status == 'active') {
            return Redirect::route('testitems.show', [$id]);
        }

        return View::make('core::testitems.edit')
            ->with('item', $item)
            ->with('enemies', $item->enemyString)
            ->with('vocab', json_encode(Vocab::all()->lists('word')->all()))
            ->with('exams', Exam::all())
            ->with('item_subjects', $item->subjects->lists('id', 'exam_id')->all()) // for populating dropdowns easily
            ->with('options', Config::get('core.knowledge.options'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        if ($this->item->validate()) {
            $item = Testitem::find($id);

            // Update testitem
            $item->stem        = Input::get('stem');
            $item->auth_source = Input::get('auth_source');
            $item->answer      = Input::get('answer');
            $item->comments    = Input::get('comments');
        
            // Update distractors
            $distractors = Input::get('distractors');
            for ($i=0; $i<count($item->distractors); $i++) {
                $distractor = $item->distractors->get($i);
                $distractor->content = $distractors[$i];
                $distractor->save();
            }

            $item->updateEnemies();
            $item->updateExamSubjects();
            $item->updateVocab();

            if ($item->save()) {
                return Redirect::route('testitems.edit', $id)->with('success', 'Item Updated.');
            }
        }

        Session::flash('danger', 'There were error(s) updating the item. Please fix below.');
        return Redirect::back()->withInput()->withErrors($this->item->errors);
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
     * Show the enemies modal for selecting enemies
     * @param  int 	$id
     * @return Response
     */
    public function enemies($id = null)
    {
        $enemies = [];
        if (is_numeric($id)) {
            $enemies    = Testitem::find($id)->enemies()->lists('id')->all();
            $otherItems = Testitem::where('id', '!=', $id)->get();
        } else {
            $otherItems = Testitem::all();
        }
        return View::make('core::testitems.modals.select_enemies')->with('items', $otherItems)->with('enemies', $enemies);
    }

    /**
     * Activate a testitem via POST
     * @param  int $id
     * @return Response
     */
    public function activate($id)
    {
        $item = Testitem::find($id);
        $item->status = 'active';
        $item->save();

        return Redirect::route('testitems.index')->withSuccess('Item #'.$id.' activated successfully.');
    }

    /**
     * Popup for swapping out a testitem on a testform with another item on the form
     */
    public function swap($id, $form_id)
    {
        $formItems = Testform::find($form_id)->testitems->lists('id')->all();

        $items = Testitem::select('stats.pvalue', 'testitems.id', 'testitems.stem', 'testitems.number', 'distractors.content')
            ->leftjoin('stats', 'testitems.id', '=', 'stats.testitem_id')
            ->join('distractors', 'testitems.answer', '=', 'distractors.id')
            ->whereNotIn('testitems.id', $formItems)
            ->where('testitems.status', '=', 'active')
            ->where('testitems.id', '!=', $id)
            ->orderBy('stats.pvalue', 'DESC')->get();

        return View::make('core::testitems.modals.swap')->with('items', $items)->with('oldId', $id);
    }
}
