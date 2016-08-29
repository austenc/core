<?php namespace Hdmaster\Core\Controllers;

use Auth;
use View;
use Config;
use Redirect;
use Input;
use File;

use \Scanform;
use \PrintProfile;
use \Student;
use \Testattempt;
use \Testevent;
use \Skillattempt;
use Hdmaster\Core\Notifications\Flash;

class ScantronController extends BaseController
{

    private $scanform;

    public function __construct()
    {
        $user = Auth::user();
        $pp   = $user->printProfile()->first();
        
        // Utilize the print settings if we have them
        if ($pp) {
            $this->scanform = new Scanform($pp->scanform_v, $pp->scanform_h);
        } else {
            $this->scanform = new Scanform;
        }
    }

    /**
     * Render a pdf scanform pre-slugged for a single student
     * @param  $id event_id
     * @return Response
     */
    public function printMultiple($id)
    {
        $event = Testevent::with('testattempts', 'skillattempts')->find($id);

        if ($event) {
            // grab all knowledge and skill attempts from the event
            $attempts          = $event->testattempts;
            $skills            = $event->skillattempts;
            $studentIdsBySkill = $event->skillattempts->lists('student_id', 'id')->all(); // list of student_ids with skills, keyed by skillattempt_id
            $excludeSkills     = array();
            $first             = true;

            // Are there any attempts?
            if ($attempts->isEmpty() && $skills->isEmpty()) {
                Flash::warning('There are no scheduled tests to print.');
                return Redirect::back();
            }

            // Loop through all the knowledge attempts
            foreach ($attempts as $attempt) {
                // make sure each attempt is on its own page
                if ($first === false) {
                    $this->scanform->addPage();
                }

                // find the student
                $student = Student::find($attempt->student_id);

                // do they also have a skill attempt for this event?
                $skillId   = null;
                $skillTest = null;
                $skillKey  = array_search($student->id, $studentIdsBySkill);

                if ($skillKey) {
                    $skillId   = $skillKey;
                    $skillTest = $skills->find($skillId)->skilltest_id;

                    // add the skillattempt_id to an array of ones to exclude in the next loop
                    $excludeSkills[] = $skillKey;
                }

                // slug the knowledge attempt
                $this->scanform->slug($student, [
                    'knowledgeAttempt' => $attempt->id,
                    'knowledgeTest'    => $attempt->testform_id,
                    'skillAttempt'     => $skillId,
                    'skillTest'        => $skillTest,
                    'event'            => $event
                ]);

                // flag that it's not the first iteration now
                $first = false;
            }

            $i = 0;
            // Loop through all skill attempts
            foreach ($skills as $attempt) {
                $student = Student::find($attempt->student_id);
                
                // if not in the exclude array, add a (slugged) page for this skill attempt
                if (! in_array($attempt->id, $excludeSkills)) {
                    // add a page if it's past the first pdf page
                    if ($first === false) {
                        $this->scanform->addPage();
                    }

                    $this->scanform->slug($student, [
                        'skillAttempt' => $attempt->id,
                        'skillTest'    => $attempt->skilltest_id,
                        'event'        => $event
                    ]);

                    // we now have more than one page, set the flag
                    $first = false;
                }

                $i++;
            }

            // Mark them all as printed
            $event->testattempts()->update(['printed_by' => Auth::id()]);
            $event->skillattempts()->update(['printed_by' => Auth::id()]);

            // now output the pdf!
            return $this->scanform->render();
        } else {
            Flash::danger('Invalid event specified.');
            return Redirect::back();
        }
    }

    /**
     * Render a pdf scanform pre-slugged for a single student
     * @param  $id student_id
     * @param  $event event_id
     * @return Response
     */
    public function printSingle($id, $event)
    {
        $student   = Student::find($id);
        $theEvent  = Testevent::find($event);
        $attempt   = Testattempt::where('student_id', '=', $id)->where('testevent_id', '=', $event)->first();
        $skill     = Skillattempt::where('student_id', '=', $id)->where('testevent_id', '=', $event)->first();

        $attemptId     = $attempt === null ? null : $attempt->id;
        $skillId       = $skill === null ? null : $skill->id;
        $knowledgeTest = $attempt === null ? null : $attempt->testform_id;
        $skillTest     = $skill === null ? null : $skill->skilltest_id;

        if ($attempt) {
            // mark as printed
            $attempt->printed_by = Auth::id();
            $attempt->save();
        }
        if ($skill) {
            // mark as printed
            $skill->printed_by = Auth::id();
            $skill->save();
        }

        if ($student) {
            // student, knowledge attempt, skill attempt
            return $this->scanform->pdf($student, [
                'knowledgeAttempt' => $attemptId,
                'knowledgeTest'    => $knowledgeTest,
                'skillAttempt'     => $skillId,
                'skillTest'        => $skillTest,
                'event'            => $theEvent,
                'title'            => 'Print Scanform | Testmaster'
            ]);
        } else {
            Flash::danger('Invalid testattempt');
            return Redirect::back();
        }
    }

    public function adjust()
    {
        $vOff = 0;
        $hOff = 0;

        // Does this user have default offsets for scanforms?
        $user = Auth::user();

        if ($user->printProfile()->first()) {
            $p = $user->printProfile->first();
            $vOff = $p->scanform_v;
            $hOff = $p->scanform_h;
        }

        
        // return the view with appropriate info
        return View::make('core::scantron.adjust')->with([
            'vOff' => round($vOff, 3),
            'hOff' => round($hOff, 3)
        ]);
    }

    public function example($v = null, $h = null)
    {
        // Create a 'bogus' new student
        $student = new Student;
        $student->id     = 999999999;
        $student->first  = 'Johnny';
        $student->last   = 'Tester';
        $student->middle = 'Quest';

        $event = new Testevent;
        $event->id = 50;

        // Setup the scanform pdf
        $s = new Scanform($v, $h);
        return $s->pdf($student, [
            'knowledgeAttempt' => 1025,
            'knowledgeTest'    => 42,
            'skillAttempt'     => 1056767,
            'skillTest'        => 79,
            'event'            => $event
        ]);
    }

    /**
     * Saves a set of vertical and horizontal offsets to a print profile for a user
     * @return Response;
     */
    public function saveOffsets()
    {
        $user                = Auth::user();
        $profile             = new PrintProfile;
        $profile->scanform_v = Input::get('voff');
        $profile->scanform_h = Input::get('hoff');
        $printTest           = Input::get('print_test');

        if ($user) {
            $p = $user->printProfile()->first();
            if ($p) {
                $p->scanform_v = $profile->scanform_v;
                $p->scanform_h = $profile->scanform_h;
                $saved = $p->save();
            } else {
                $saved = $user->printProfile()->save($profile);
            }
        }

        if ($saved) {
            // if doing a print test, we'll redirect to a pdf here
            if ($printTest) {
                // get the user's print profile and pass the info in
                $pp = $user->printProfile()->first();
                return Redirect::route('scantron.example', [$pp->scanform_v, $pp->scanform_h]);
            }
    
            Flash::success('Print profile saved.');
        } else {
            Flash::danger('Could not save print profile. Please contact developers.');
        }

        return Redirect::back();
    }
    /**
     * PDF version a scantron form. (INCOMPLETE) -- from a web form, uses snappy
     * GET /scantron/pdf
     *
     * @return Response
     */
    // public function webPdf()
    // {
    // 	$css = null;

    // 	// get css from file
    // 	try
    // 	{
    // 		$css = File::get(public_path().'/css/style.min.css');
    // 	}
    // 	catch (Illuminate\Filesystem\FileNotFoundException $e)
    // 	{
    // 		return Redirect::to('/')->withWarning('CSS file doesn\'t exist.');
    // 	}


    // 	$pdf  = PDF::loadView('core::scantron.pdf', [
    // 		'person'  => Student::find(1),
    // 		'answers' => str_split('abccdaaecbdaedbceabaecedag'),
    // 		'style'   => $css
    // 	]);
    // 	$pdf->setOption('margin-top', '32mm');
    // 	$pdf->setOption('margin-right', '7mm');
    // 	$pdf->setOption('margin-bottom', '14mm');
    // 	$pdf->setOption('margin-left', '17.5mm');
    // 	return $pdf->stream();
    // }
}
