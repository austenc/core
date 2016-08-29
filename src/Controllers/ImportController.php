<?php namespace Hdmaster\Core\Controllers;

use View;
use Auth;
use Request;
use Flash;
use Input;
use Redirect;
use Exam;
use Skillexam;
use SkilltaskStep;
use Training;
use \Importer;
use Mail;

class ImportController extends BaseController
{

    public function index()
    {
        return View::make('core::import.index')->with([
            'exams'     => Exam::with('subjects')->get(),
            'skills'    => Skillexam::all(),
            'trainings' => Training::all()
        ]);
    }

    public function captureKnowledge($examId)
    {
        if (Request::isMethod('post')) {
            $import = new Importer;
            $import->importTestitems($examId);
        }

        return Redirect::route('import');
    }

    /**
     * Import skillexam tasks
     */
    public function captureSkillTasks($skillId)
    {
        if (Request::isMethod('post')) {
            $import = new Importer;
            $import->importSkillSteps($skillId);
        }

        return Redirect::route('import');
    }

    /**
     * Import skillexam steps
     * Sets review flag use user can adjust possible variable input fields
     */
    public function captureSkillSteps($skillId)
    {
        if (Request::isMethod('post')) {
            $import = new Importer;
            $import->importSkillSteps($skillId);
        }

        return Redirect::route('import');
    }

    /**
     * Import skillexam test data
     */
    public function captureSkillSetups($skillId)
    {
        if (Request::isMethod('post')) {
            $import = new Importer;
            $import->importSkillSetups($skillId);
        }

        return Redirect::route('import');
    }

    /**
     * Import training programs
     */
    public function captureFacilities($trainingId)
    {
        $import = new Importer;

        if (Request::isMethod('post')) {
            $numFac = $import->importFacilities($trainingId);

            if (is_numeric($numFac)) {
                Flash::success('Imported '.$numFac.' new '.Lang::choice('core::terms.facility_training', $numFac).'.');
            }
        }

        return Redirect::route('import');
    }

    /**
     * Import instructors per training (instructor teaches this training)
     */
    public function captureInstructors($trainingId)
    {
        $import = new Importer;

        if (Request::isMethod('post')) {
            $training = Training::find($trainingId);

            if (! is_null($training)) {
                $numIns = $import->importInstructors($trainingId);

                if (is_numeric($numIns)) {
                    Flash::success('Imported '.$numIns.' new '.Lang::choice('core::terms.instructor', $numIns).' with Training <strong>'.$training->name.'</strong>.');
                }
            }
        }

        return Redirect::route('import');
    }

    /**
     * Erases records from selected tables related to skills import
     */
    public function truncateSkills()
    {
        if (! Auth::user()->ability(['Admin', 'Staff'], [])) {
            return Redirect::to('/');
        }

        $tables = [
            'skillexams',
            'skillexam_exam_requirements',
            'skillexam_tasks',
            'skillexam_training_requirements',
            'skilltasks',
            'skilltask_enemies',
            'skilltask_responses',
            'skilltask_setups',
            'skilltask_steps',
            'skilltests',
            'skilltest_tasks',
            'step_inputs',
            'input_fields',
            'skillattempts'
        ];

        // get record count foreach table
        $records = [];
        foreach ($tables as $table) {
            $records[$table] = \DB::table($table)->count();
        }

        if (Request::isMethod('post')) {
            $i = 0;
            $truncate = Input::get('tables');

            foreach ($truncate as $table) {
                \DB::table($table)->truncate();
                $i++;
            }
            
            Flash::success('Truncated '.$i.' Skill tables');
            return Redirect::route('import');
        }

        return View::make('core::import.modals.truncate')->with([
            'tables'  => $tables,
            'records' => $records,
            'route' => 'truncate.skills'
        ]);
    }

    /**
     * Erases records from selected tables related to skills import
     */
    public function truncateKnowledge()
    {
        if (! Auth::user()->ability(['Admin', 'Staff'], [])) {
            return Redirect::to('/');
        }

        $tables = [
            'testitems',
            'testitem_vocab',
            'testforms',
            'testform_testitem',
            'vocabs',
            'exam_testitem',
            'enemies',
            'stats',
            'distractors'
        ];

        // get record count foreach table
        $records = [];
        foreach ($tables as $table) {
            $records[$table] = \DB::table($table)->count();
        }

        if (Request::isMethod('post')) {
            $i = 0;
            $truncate = Input::get('tables');

            foreach ($truncate as $table) {
                \DB::table($table)->truncate();
                $i++;
            }
            
            Flash::success('Truncated '.$i.' Knowledge tables');
            return Redirect::route('import');
        }

        return View::make('core::import.modals.truncate')->with([
            'tables'  => $tables,
            'records' => $records,
            'route' => 'truncate.testitems'
        ]);
    }

    /**
     * Erases records from selected tables related to facilities
     */
    public function truncateTrainings()
    {
        if (! Auth::user()->ability(['Admin', 'Staff'], [])) {
            return Redirect::to('/');
        }

        $tables = [
            'facilities',
            'facility_affiliated',
            'facility_instructor',
            'facility_test_team',
            'testevents',
            'testevent_exam',
            'testevent_skillexam',
            'pendingevents',
            'pendingevent_exam',
            'pendingevent_skillexam',
            'testattempts',
            'skillattempts',
            'student_training',
            'instructors',
            'instructor_student',
            'instructor_training',
            'student_training'
        ];

        // get record count foreach table
        $records = [];
        foreach ($tables as $table) {
            $records[$table] = \DB::table($table)->count();
        }

        if (Request::isMethod('post')) {
            $i = 0;
            $truncate = Input::get('tables');

            foreach ($truncate as $table) {
                \DB::table($table)->truncate();
                $i++;
            }
            
            Flash::success('Truncated '.$i.' Training tables');
            return Redirect::route('import');
        }
        

        return View::make('core::import.modals.truncate')->with([
            'tables'  => $tables,
            'records' => $records,
            'route'   => 'truncate.trainings'
        ]);
    }

    public function taskHelp()
    {
        return View::make('core::import.modals.task_help');
    }

    public function setupHelp()
    {
        return View::make('core::import.modals.setup_help');
    }

    public function stepHelp()
    {
        return View::make('core::import.modals.step_help');
    }

    public function instructorHelp()
    {
        return View::make('core::import.modals.instructor_help');
    }

    public function facilityHelp()
    {
        return View::make('core::import.modals.facility_help');
    }
}
