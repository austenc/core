<?php namespace Hdmaster\Core\Controllers;

use \Sorter;
use View;
use Input;
use Redirect;
use Request;
use Session;
use Response;
use \SkilltaskStep;
use \Skilltask;
use \InputField;
use \BBCode;

class StepsController extends BaseController
{

    protected $step;
    protected $input;

    public function __construct(SkilltaskStep $step, InputField $input)
    {
        $this->step = $step;
        $this->input = $input;
    }

    public function index()
    {
        $steps = SkilltaskStep::with(['task', 'inputs']);

        // only steps with input
        if (Input::get('inputs')) {
            $steps->has('inputs');
        }

        // only steps flagged for review
        if (Input::get('review')) {
            $steps = $steps->where('vinput_review', true);
        }

        // outcome search
        $steps = $steps->where('expected_outcome', 'like', '%'.Input::get('search', null).'%')->get();

        return View::make('core::skills.steps.index')->with([
            'steps'      => $steps,
            'totalSteps' => SkilltaskStep::all()->count(),
            'reviewable' => SkilltaskStep::where('vinput_review', true)->count(),
            'inputSteps' => SkilltaskStep::has('inputs')->count()
        ]);
    }

    /**
     * Edit a skill step
     */
    public function edit($id)
    {
        $step = SkilltaskStep::with(['task', 'inputs'])->find($id);

        if (is_null($step)) {
            return Redirect::route('steps.index')->withDanger('Unknown Step.');
        }

        // format options
        $opts = [];
        foreach ($step->inputs as $input) {
            $data = explode('|', $input->value);

            foreach ($data as $i => $value) {
                if (empty($value)) {
                    continue;
                }

                $vals = explode(',', $value);

                $opts[$input->id]['val'][] = isset($vals[0]) ? $vals[0] : '';
                $opts[$input->id]['text'][] = isset($vals[1]) ? $vals[1] : '';
            }
        }

        return View::make('core::skills.steps.edit')->with([
            'step'    => $step,
            'options' => $opts
        ]);
    }

    /**
     * Update a skill step
     */
    public function update($id)
    {
        $step = SkilltaskStep::find($id);
        
        if ($this->step->fill(Input::all())->validate()) {
            if ($step->updateWithInput()) {
                return Redirect::route('steps.edit', $id)->with('success', 'Step updated.');
            }
        }

        return Redirect::back()->withInput()->withErrors($this->step->errors);
    }

    /**
     * Removes a step from a task 
     */
    public function remove($id)
    {
        if (Request::ajax()) {
            $step = SkilltaskStep::with('inputs')->find($id);
            $taskId = $step->skilltask_id;

            // remove any extra step inputs
            if ($step->inputs->count() > 0) {
                foreach ($step->inputs as $input) {
                    // remove step/input relation
                    $step->inputs()->detach($input->id);
                    // delete the input record
                    InputField::destroy($input->id);
                }
            }

            // remove the step
            SkilltaskStep::destroy($id);

            // adjust ordinals
            $steps = SkilltaskStep::where('skilltask_id', '=', $taskId)
                    ->orderBy('ordinal', 'ASC')
                    ->lists('id', 'ordinal')->all();

            $steps = array_values($steps);
            array_unshift($steps, 'fake');
            unset($steps[0]);

            foreach ($steps as $ordinal=>$id) {
                SkilltaskStep::where('id', '=', $id)->update(['ordinal' => $ordinal]);
            }

            return Response::json(true);
        }

        return Response::json(false);
    }

    /**
     * Removes variable input flag, ie seen and approved by staff following item import
     */
    public function unflag($id)
    {
        $step = SkilltaskStep::find($id);

        if (is_null($step)) {
            return Redirect::route('steps.index')->withDanger('Unknown Step.');
        }

        $step->vinput_review = false;
        $step->save();

        return Redirect::route('steps.edit', $id)->withSuccess('Variable input flag removed.');
    }

    /**
     * Create new input for step, complete view rather than inside popup
     * Used when creating step input from steps.input
     */
    public function addInput($stepId)
    {
        $step = SkilltaskStep::with('task')->find($stepId);
        return View::make('core::skills.steps.add_input')->withStep($step);
    }

    /**
     * Store new input for a step
     */
    public function storeInput($stepId)
    {
        if ($this->input->fill(Input::all())->validate()) {
            $inputId = $this->input->addWithInput();

            if ($inputId) {
                if (Input::get('task_id')) {
                    return Redirect::route('tasks.edit', Input::get('task_id'))->with('success', 'Step Input added.');
                } else {
                    return Redirect::route('steps.edit', $stepId)->with('success', 'Step Input added.');
                }
            }
        }

        Session::flash('danger', 'There was an error creating the Step Input.');
        return Redirect::back()->withInput()->withErrors($this->input->errors);
    }


    /**
     * Show modal popup to update existing input in step
     */
    public function editInput($stepId, $inputId)
    {
        $step  = SkilltaskStep::find($stepId);
        $input = InputField::find($inputId);
        return View::make('core::skills.steps.edit_input')->withStep($step)->withInput($input);
    }

    /**
     * Saves step input from Update submit from input edit
     */
    public function updateInput($stepId, $inputId)
    {
        $input = InputField::find($inputId);

        if ($this->input->fill(Input::all())->validate()) {
            if ($input->updateWithInput()) {
                return Redirect::route('steps.input.edit', [$stepId, $input->id])->with('success', 'Input updated.');
            }
        }

        Session::flash('danger', 'There was an error updating the Step Input.');
        return Redirect::back()->withInput()->withErrors($this->input->errors);
    }

    /**
     * Update just the expected outcome field for a step input
     * Used for AJAX "Move Input" action, moves bbcode tag to textarea cursor position
     */
    public function updateOutcome($stepId)
    {
        if (Request::ajax()) {
            $outcome = Input::get('expected_outcome');

            $step = SkilltaskStep::find($stepId);
            $step->expected_outcome = $outcome;
            $step->save();

            return Response::json(['message' => 'Step #'.$step->ordinal.' updated.']);
        }

        return Response::json(false);
    }

    /**
     * Delete a skill task input field
     */
    public function deleteInput($stepId, $inputId)
    {
        if (Request::ajax()) {
            // remove step input relation
            $step = SkilltaskStep::find($stepId);
            $step->inputs()->detach($inputId);

            // delete input db record
            InputField::destroy($inputId);

            // adjust step outcome
            $inputBB = '[input id="'.$inputId.'"]';
            $step->expected_outcome = str_replace($inputBB, '', $step->expected_outcome);
            $step->save();

            return Response::json(['message' => 'Step #'.$step->ordinal.' input deleted.']);
        }

        return Response::json(false);
    }


    /**
     * Preview a step outcome as paper version
     *   - renders all bbcode with ________  or multi-choice circle
     */
    public function previewPaper($id)
    {
        $step = SkilltaskStep::find($id);

        $parsedOutcome = BBCode::parseInput($step->expected_outcome, $id, 'paper');

        return View::make('core::skills.tasks.modals.preview')
            ->withStep($step)
            ->withOutcome($parsedOutcome)
            ->withVersion('paper');
    }

    /**
     * Preview a step outcome as web version
     *   - renders all bbcode as input fields
     */
    public function previewWeb($id)
    {
        $step = SkilltaskStep::find($id);

        $parsedOutcome = BBCode::parseInput($step->expected_outcome, $id, 'web');

        return View::make('core::skills.tasks.modals.preview')
            ->withStep($step)
            ->withOutcome($parsedOutcome)
            ->withVersion('web');
    }
}
