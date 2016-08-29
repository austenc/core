<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Redirect;
use DB;
use Auth;
use Response;
use Lang;
use Config;
use Session;
use Hdmaster\Core\Notifications\Flash;
use Carbon\Carbon;
use \Testevent;
use \Student;
use \Instructor;
use \Facility;
use \Certification;
use \Testattempt;
use \Skillattempt;
use \Testitem;
use \StudentTraining;
use \Discipline;
use \Subject;
use \Skilltask;
use Illuminate\Support\Collection;

class ReportsController extends BaseController
{

    protected $facility, $instructor, $instructorProgram, $children, $grandchildren;
    protected $allDisciplines, $skill, $exam;
    protected $fromDate, $toDate;
    protected $reportParams;
    protected $error;

    public function __construct()
    {
        $this->allDisciplines = Discipline::with('exams', 'skills')->get();
        $this->fromDate       = 0;
        $this->toDate         = Carbon::now()->subDay();
        $this->facility       = '';
        $this->instructor     = '';
        $this->error          = '';
    }

    /**
     * Display a listing of the resource.
     * GET /reports
     *
     * @return Response
     */
    public function index()
    {
        if (Auth::user()->isRole('Instructor')) {
            return $this->instructorIndex();
        }

        if (Auth::user()->isRole('Facility')) {
            return $this->facilityIndex();
        }

        $reportTypes = [
            'pass_fail'        => 'Pass / Fail',
            'retake_summary'   => 'Retake Summary',
            'site_summary'     => 'Test Site Summary',
            'knowledge_detail' => 'Knowledge Detail',
            'skills_detail'    => 'Skills Detail'
            // 'scheduled_exams'  => 'Scheduled Exams',
            // 'training_summary' => 'Training Summary',
        ];

        return View::make('core::reports.index')->with([
            'reportTypes' => $reportTypes,
            'disciplines' => Discipline::all()->lists('name', 'id')->all()
        ]);
    }

    /**
     * Logged in Facility running report
     */
    private function instructorIndex()
    {
        $reportTypes = [
            'pass_fail'        => 'Pass / Fail',
            'retake_summary'   => 'Retake Summary',
            'knowledge_detail' => 'Knowledge Detail',
            'skills_detail'    => 'Skills Detail'
        ];

        return View::make('core::reports.instructors.index')->with([
            'instructor'  => Auth::user()->userable,
            'reportTypes' => $reportTypes
        ]);
    }

    /**
     * Logged in Facility running report
     */
    private function facilityIndex()
    {
        $facility = Auth::user()->userable;

        $reportTypes = [
            'pass_fail'        => 'Pass / Fail',
            'retake_summary'   => 'Retake Summary',
            'knowledge_detail' => 'Knowledge Detail',
            'skills_detail'    => 'Skills Detail'
        ];

        // get all child training programs
        $childIds = DB::table('facility_discipline')
                        ->where('discipline_id', Session::get('discipline.id'))
                        ->where('parent_id', $facility->id)
                        ->lists('facility_id');

        $children = Facility::with(['disciplines' => function ($q) {
            $q->where('facility_discipline.discipline_id', Session::get('discipline.id'));
        }])->whereIn('id', $childIds)->get();

        // get all instructors working at this facility under discipline
        $instructors = $facility->instructors->filter(function ($i) {
            return $i->pivot->discipline_id == Session::get('discipline.id');
        });

        return View::make('core::reports.facilities.index')->with([
            'instructors' => $instructors,
            'facility'    => $facility,
            'children'      => $children,
            'reportTypes' => $reportTypes,
            'disciplines' => $this->allDisciplines->lists('name', 'id')->all()
        ]);
    }

    /**
     * Reports index, click FIND on license
     * Returns all Facilities and/or Instructors using this license
     */
    public function findLicense($disciplineId, $license = null)
    {
        $valid        = [];
        $linkToRecord = Auth::user()->ability(['Admin', 'Staff'], []);
        $reportType   = Input::get('report_type');

        // get all disciplines
        $allDisciplines = Discipline::with('exams', 'skills')->get();

        // ensure discipline exists
        if (! in_array($disciplineId, $allDisciplines->lists('id')->all())) {
            return Response::json(['Discipline doesn\'t exist.'], 400);
        }

        // empty license cases
        if (empty($license) || ! $license) {
            switch ($reportType) {
                case 'pass_fail':
                    return Response::json(['License field is required for Pass / Fail Report.'], 400);
                case 'site_summary':
                case 'retake_summary':
                    return Response::json(['all' => [
                        'type'   => \Lang::choice('core::terms.facility', 1),
                        'set'    => 'all_facilities',
                        'id'     => 'all',
                        'link'   => $linkToRecord,
                        'name'   => 'All ' . \Lang::choice('core::terms.facility', 2),
                        'status' => 'ACTIVE'
                    ]]);
                default:
                    break;
            }
        }

        // get requested discipline
        $discipline = $allDisciplines->keyBy('id')->get($disciplineId);

        // find any facility using this TM License for Discipline
        // we can use first() here since tm_license is unique
        $res = DB::table('facility_discipline')->where('tm_license', $license)->where('discipline_id', $disciplineId)->first();

        // found facility
        // return facility and all associated instructors under this discipline
        if ($res) {
            $facility = Facility::with([
                'instructors' => function ($q) use ($disciplineId) {
                    $q->where('facility_person.discipline_id', $disciplineId);
                },
                'instructors.disciplines'
            ])->withTrashed()->find($res->facility_id);

            // add facility to final possible list
            $valid[$license] = [
                'type'       => \Lang::choice('core::terms.facility_training', 1),
                'set'        => 'facility',
                'id'         => $facility->id,
                'status'     => $res->active,
                'link'       => $linkToRecord,
                'license'    => $license,
                'instructor' => '',
                'program'    => $facility->name
            ];

            // add children facilities
            // check for children programs
            $childRes = DB::table('facility_discipline')->where('discipline_id', $disciplineId)->where('parent_id', $facility->id)->get();

            if ($childRes) {
                $childIds = [];

                foreach ($childRes as $res) {
                    $child      = Facility::find($res->facility_id);
                    $childIds[] = $child->id;

                    $valid[$res->tm_license] = [
                        'type'       => 'Child ' .\Lang::choice('core::terms.facility_training', 1),
                        'set'        => 'facility',
                        'id'         => $child->id,
                        'status'     => $res->active,
                        'link'       => $linkToRecord,
                        'license'    => $res->tm_license,
                        'instructor' => '',
                        'program'    => $child->name
                    ];
                }

                // check for facility grandchildren
                $grandRes = DB::table('facility_discipline')->where('discipline_id', $disciplineId)->whereIn('parent_id', $childIds)->get();
                
                if ($grandRes) {
                    foreach ($grandRes as $res) {
                        $grand = Facility::find($res->facility_id);

                        $valid[$res->tm_license] = [
                            'type'       => 'Grandchild ' .\Lang::choice('core::terms.facility_training', 1),
                            'set'        => 'facility',
                            'id'         => $grand->id,
                            'status'     => $res->active,
                            'link'       => $linkToRecord,
                            'license'    => $res->tm_license,
                            'instructor' => '',
                            'program'    => $grand->name,
                        ];
                    }
                }
            }

            // add each instructor at facility for discipline
            foreach ($facility->instructors as $instructor) {
                $valid[$instructor->pivot->tm_license] = [
                    'type'       => \Lang::choice('core::terms.instructor', 1),
                    'set'        => 'instructor',
                    'id'         => $instructor->id,
                    'status'     => $instructor->pivot->tm_license,
                    'link'       => $linkToRecord,
                    'license'    => $instructor->pivot->tm_license,
                    'instructor' => $instructor->commaName,
                    'program'    => $facility->name
                ];
            }
        }

        // no facility under this license
        // check instructors
        else {
            // tm_license has unique constraint
            // able to use first() here again 
            $res = \DB::table('facility_person')
                    ->where('tm_license', $license)
                    ->where('discipline_id', $disciplineId)
                    ->where('person_type', 'Instructor')
                    ->first();

            if ($res) {
                $instructor = Instructor::withTrashed()->with([
                    'facilities' => function ($q) use ($discipline) {
                        $q->where('facility_person.discipline_id', $discipline->id);
                    }
                ])->find($res->person_id);

                $instructorProgram = Facility::withTrashed()->find($res->facility_id);

                // add instructor
                $valid[$res->tm_license] = [
                    'type'       => \Lang::choice('core::terms.instructor', 1),
                    'set'        => 'instructor',
                    'id'         => $instructor->id,
                    'status'     => $res->active,
                    'link'       => $linkToRecord,
                    'name'       => $instructor->commaName,
                    'license'    => $res->tm_license,
                    'instructor' => $instructor->commaName,
                    'program'    => $instructorProgram->name
                ];

                // each program this instructor has EVER worked at (active and inactive)
                foreach ($instructor->facilities as $program) {
                    if (! array_key_exists($program->pivot->tm_license, $valid)) {
                        $valid[$program->pivot->tm_license] = [
                            'type'       => \Lang::choice('core::terms.instructor', 1),
                            'set'        => 'instructor',
                            'id'         => $instructor->id,
                            'status'     => $program->pivot->active,
                            'link'       => $linkToRecord,
                            'license'    => $program->pivot->tm_license,
                            'instructor' => $instructor->commaName,
                            'program'    => $program->name
                        ];
                    }
                }
            }
        }

        // no matching instructor/program
        if (empty($valid)) {
            return Response::json(['Unknown License'], 400);
        }

        // sort by type so training program will come out on top of instructors
        usort($valid, function ($a, $b) {
            return $a['type'] - $b['type'];
        });
        $valid = array_reverse($valid);

        return Response::json($valid);
    }

    /**
     * Handles submission of reports form and calls appropriate method
     * @return Response
     */
    public function generate()
    {
        $reportType   = Input::get('report_type');
        $from         = Input::get('from');
        $to           = Input::get('to');
        $disciplineId = Input::get('discipline');
        $info         = explode(',', Input::get('info'));
        $id           = $info[1];
        $type         = $info[0];
        $license      = array_key_exists(2, $info) ? $info[2] : '0';

        $from = empty($from) ? null : date('Y-m-d', strtotime($from));
        $to   = empty($to) ? null : date('Y-m-d', strtotime($to));
        
        if (! $reportType) {
            Flash::danger('Please select a <strong>Report Type</strong>.');
            return Redirect::route('reports.index')->withInput();
        }

        if (empty($from)) {
            Flash::danger('Please enter a <strong>From Date</strong> to generate report.');
            return Redirect::route('reports.index')->withInput();
        }

        // all route params
        $routeParams = [$disciplineId, $type, $id, $license, $from, $to];

        switch ($reportType) {
            case 'site_summary':
                return Redirect::route('reports.site_summary', $routeParams);
            break;

            case 'scheduled_exams':
                return Redirect::route('reports.scheduled_exams', $routeParams);
            break;

            case 'retake_summary':
                return Redirect::route('reports.retake_summary', $routeParams);
            break;

            case 'skills_detail':
                return Redirect::route('reports.skills_detail', $routeParams);
            break;

            case 'knowledge_detail':
                return Redirect::route('reports.knowledge_detail', $routeParams);
            break;

            case 'pass_fail':
                // if admin / agency / staff show a page to select facility and instructor
                /*if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
                {
                    return Redirect::route('reports.select_facility', [$from, $to]);
                }*/

                // Otherwise just show the regular pass/fail report
                return Redirect::route('reports.pass_fail', $routeParams);
            break;

            default:
                Flash::warning('This report type is not yet supported.', 'Warning');
                return Redirect::route('reports.index');

        }
    }

    /**
     * Counts of all students that took skillexams at the requested facility
     */
    public function skillsDetail($disciplineId, $type, $id, $license, $from = null, $to = null)
    {
        $reportType = 'Skill Detail';

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 180);

        // setup class vars along with general validation
        $this->setupReport($reportType, $disciplineId, $type, $id, $license, $from, $to);
        if ($this->error) {
            return Redirect::route('reports.index')->withDanger($this->error);
        }

        // ensure requested discipline has available skill exams
        if (! $this->skill) {
            return Redirect::route('reports.index')->withDanger('Skill Detail report not supported for '.$this->discipline->name.'.', 'Warning');
        }

        // check if date is within legacy region
        //   (if necessary data for this report was missing, show warning)
        $this->checkForLegacyWarning();

        // get all appropriate training program ids
        $programIds = $this->getReportProgramIds($reportType);

        // get all tasks (for quick lookup while inside loop)
        $allTasks = Skilltask::with(['skillexams', 'steps', 'parent'])->get()->keyBy('id');

        // get all skillattempts for current discipline within date range
        $attempts = Skillattempt::with([
                        'studentTraining' => function ($q) {
                            $q->where('student_training.discipline_id', $this->discipline->id);
                            $q->where('student_training.status', 'passed');
                            $q->whereIn('student_training.training_id', $this->discipline->training->lists('id')->all());
                        },
                        'responses',
                        'responses.task'
                    ])
                    ->has('studentTraining')
                    ->where('skillexam_id', $this->skill->id)
                    ->where('skillattempts.end_time', '>=', $this->fromDate)
                    ->where('skillattempts.end_time', '<=', $this->toDate)
                    ->whereIn('status', ['passed', 'failed'])
                    ->orderBy('skillattempts.end_time')
                    ->get();

        
        // total arrays
        $requestedTotals = [];    // only requested program/instructor
        $statewideTotals = [];  // entire state (used to calculate variance)
        $stepTotals      = [];
        $reset           = ['total' => 0, 'passed' => 0];  // reset array

        foreach ($attempts as $attempt) {
            // current student matches to requested instructor or program
            $match = false;

            // If there is no instructor, or this attempt doesn't have a training associated, skip this attempt
            if (empty($attempt->studentTraining)) {
                continue;
            }

            // instructor
            //   (student took this skill test on a training given by searched for instructor)
            //   (AND also current facility attached to instructor)
            if (! empty($this->instructor) && $attempt->studentTraining->instructor_id == $this->instructor->id && in_array($attempt->studentTraining->facility_id, $programIds)) {
                $match = true;
            } elseif (! empty($this->facility) && in_array($attempt->studentTraining->facility_id, $programIds)) {
                // training program
                $match = true;
            }

            // each task response in attempt
            foreach ($attempt->responses as $res) {
                // get current skilltask
                $currTask = $allTasks->get($res->skilltask_id);

                // task not found? skip it
                //  must likely 0..
                if (is_null($currTask)) {
                    continue;
                }

                // make sure we are reporting this task
                // per chad 4/21: not reporting any archived tasks
                if ($currTask->status == 'archived') {
                    continue;
                }

                // parent?
                // if task has parent report under parent instead
                if ($currTask->parent_id) {
                    $reportTaskId = $currTask->parent->parent_id ?: $currTask->parent->id;
                } else {
                    $reportTaskId = $currTask->id;
                }

                // initialize totals arrays
                if (! array_key_exists($reportTaskId, $requestedTotals)) {
                    $requestedTotals[$reportTaskId] = $reset;
                }
                if (! array_key_exists($reportTaskId, $statewideTotals)) {
                    $statewideTotals[$reportTaskId] = $reset;
                }

                // requested program or instructor?
                // increment single program/instructor totals
                if ($match === true) {
                    $requestedTotals[$reportTaskId]['total']++;

                    if ($res->status == 'passed') {
                        $requestedTotals[$reportTaskId]['passed']++;
                    }
                }

                // increment statewide totals 
                $statewideTotals[$reportTaskId]['total']++;
                if ($res->status == 'passed') {
                    $statewideTotals[$reportTaskId]['passed']++;
                }

                // does task have step responses?
                //  (legacy data is missing this)
                //  (matches only; individual step variance is not needed)
                if ($match === true && $res->response) {
                    $stepAnswers = $res->decodedResponse;

                    foreach ($stepAnswers as $stepId => $stepInfo) {
                        // init step counts
                        if (! array_key_exists($stepId, $stepTotals)) {
                            $stepTotals[$stepId]['total']  = 0;
                            $stepTotals[$stepId]['passed'] = 0;
                        }

                        // skill step was completed?
                        if ($stepInfo['completed'] === true) {
                            $stepTotals[$stepId]['passed']++;
                        }
                            
                        $stepTotals[$stepId]['total']++;
                    }
                }
            }
        }

        // calculate passed percent for each total type
        $statewideTotals = array_map(function ($q) {
            $q['passedPercent'] = $q['total'] > 0 ? round(($q['passed'] / $q['total'] * 100), 2) : '';
            return $q;
        }, $statewideTotals);

        $requestedTotals = array_map(function ($q) {
            $q['passedPercent'] = $q['total'] > 0 ? round(($q['passed'] / $q['total'] * 100), 2) : '';
            return $q;
        }, $requestedTotals);

        $stepTotals = array_map(function ($q) {
            $q['passedPercent'] = $q['total'] > 0 ? round(($q['passed'] / $q['total'] * 100), 2) : '';
            return $q;
        }, $stepTotals);


        // filter requestedTotals down to only those with a count (i.e. at least 1 tested this task)
        $requestedTotals = array_filter($requestedTotals, function ($taskCount) {
            return $taskCount['total'] > 0;
        });

        // get all skill tasks that have counts
        $reportTaskIds = array_keys($requestedTotals);

        // filter down to only skill tasks we had counts for (no child tasks, etc)
        $allTasks = $allTasks->filter(function ($task) use ($reportTaskIds) {
            return in_array($task->id, $reportTaskIds);
        });

        return View::make('core::reports.skills_detail', [
            'tasks'         => $allTasks,
            'requestTotals' => $requestedTotals,
            'stateTotals'   => $statewideTotals,
            'stepTotals'    => $stepTotals,
            'info'          => $this->reportParams
        ]);
    }

    /**
     * Get all appropriate program ids for given generate report
     */
    private function getReportProgramIds($type='')
    {
        // Instructor
        if ($this->instructor) {
            $programIds = array($this->instructorProgram->id);
        }
        // Training Program (with child/grandchild program)
        else {
            if ($type == 'all_facilities') {
                // used to determine all training programs!
                //   (cant use filter by facilities.actions here)
                //   (we need all facilities that have EVER been a training program)
                $programIds = StudentTraining::all()->lists('facility_id')->all();
                $programIds = array_unique($programIds);
            } elseif ($type == 'all_testing_facilities') {
                // get all facilities that ever tested
                $knowSiteIds  = Testattempt::all()->lists('facility_id')->all();
                $skillSiteIds = Skillattempt::all()->lists('facility_id')->all();
                $programIds   = array_merge($knowSiteIds, $skillSiteIds);    // array_merge is ok to use here
                $programIds   = array_unique($programIds);
            }
            // single specific program
            // or multi if current facility has children
            else {
                $programIds = array($this->facility->id);

                // add children facility ids
                if ($this->children && ! $this->children->isEmpty()) {
                    $programIds = array_merge($programIds, $this->children->lists('id')->all());
                }

                // add grandchildren facility ids
                if ($this->grandchildren && ! $this->grandchildren->isEmpty()) {
                    $programIds = array_merge($programIds, $this->grandchildren->lists('id')->all());
                }
            }
        }

        return $programIds;
    }

    /**
     * Info on knowledge exams
     * # of times items were missed from each vocab
     */
    public function knowledgeDetail($disciplineId, $type, $id, $license, $from = null, $to = null)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        // THIS REPORT TYPE REQUIRES A SINGLE TRAINING PROGRAM OR INSTRUCTOR
        // ALL FACILITIES NOT AVAILABLE
        $reportType = 'Knowledge Detail';

        // setup class vars along with general validation
        $this->setupReport($reportType, $disciplineId, $type, $id, $license, $from, $to);
        if ($this->error) {
            return Redirect::route('reports.index')->withDanger($this->error);
        }

        if (! $this->exam) {
            return Redirect::route('reports.index')->withDanger('Knowledge Detail report not supported for '.$this->discipline->name.'.', 'Warning');
        }

        // check if date is within legacy region
        // (if necessary data for this report was missing, show warning)
        $this->checkForLegacyWarning();

        // get all appropriate training program ids
        $programIds = $this->getReportProgramIds($reportType);

        // get all testitems with info
        // used for map lookup
        $testitems = Testitem::with([
            'vocab',
            'theAnswer',
            'subjects' => function ($q) {
                $q->where('exam_testitem.exam_id', $this->exam->id);
                $q->where('exam_testitem.client', Config::get('core.client.abbrev'));
            },
            'subjects.reportAs'
        ])->has('subjects')->get()->keyBy('id');

        // get all knowledge attempts for current discipline within date range
        $attempts = Testattempt::with([
            'studentTraining' => function ($q) {
                $q->where('student_training.discipline_id', $this->discipline->id);
                $q->where('student_training.status', 'passed');
                $q->whereIn('student_training.training_id', $this->discipline->training->lists('id')->all());
            }
        ])
        ->has('studentTraining')
        ->where('exam_id', $this->exam->id)
        ->where('testattempts.end_time', '>=', $this->fromDate)
        ->where('testattempts.end_time', '<=', $this->toDate)
        ->whereIn('status', ['passed', 'failed'])
        ->orderBy('testattempts.end_time')
        ->get();


        // hold info on missed testitems with vocab
        // second section of report
        $totals      = [];
        $vocabTotals = [];

        // init subject totals
        foreach ($this->exam->subjects as $subject) {
            // skip any subjects being reported under different subject
            if ($subject->report_as) {
                continue;
            }

            $totals[$subject->id]['passed'] = 0;
            $totals[$subject->id]['total']  = 0;
        }

        // all knowledge test attempts
        foreach ($attempts as $attempt) {
            // instructor
            if ($this->instructor && ($attempt->studentTraining->instructor_id != $this->instructor->id || ! in_array($attempt->studentTraining->facility_id, $programIds))) {
                continue;
            }
            // training program
            elseif ($this->facility && ! in_array($attempt->studentTraining->facility_id, $programIds)) {
                continue;
            }


            // testattempt has answers?
            if ($attempt->answers) {
                $answers = (array) $attempt->answers;

                foreach ($answers as $itemId => $distractorId) {
                    $currItem = $testitems->get($itemId);

                    // couldnt find testitem or testitem has no subjects?
                    // goto next testitem
                    if (! $currItem || $currItem->subjects->isEmpty()) {
                        continue;
                    }

                    // a single testitem has 1 subject (per exam+client)
                    $subject = $currItem->subjects->first();

                    // reporting as self or other subject?
                    $reportSubjectId = $subject->report_as ? $subject->report_as : $subject->id;

                    // correct answer?
                    $correctAnswer = false;
                    if ($currItem->theAnswer->id == $distractorId) {
                        $totals[$reportSubjectId]['passed']++;
                        $correctAnswer = true;
                    }

                    // vocab
                    foreach ($currItem->vocab as $vocab) {
                        $vocabTotals[$vocab->id]['name'] = $vocab->word;

                        // totals
                        if (array_key_exists('total', $vocabTotals[$vocab->id])) {
                            $vocabTotals[$vocab->id]['total']++;
                        } else {
                            $vocabTotals[$vocab->id]['total'] = 1;
                        }

                        // failed testitem?
                        if (! $correctAnswer) {
                            if (array_key_exists('failed', $vocabTotals[$vocab->id])) {
                                $vocabTotals[$vocab->id]['failed']++;
                            } else {
                                $vocabTotals[$vocab->id]['failed'] = 1;
                            }
                        }
                    }
                
                    $totals[$reportSubjectId]['total']++;
                }
            }
        } // end FOREACH testattempt


        // percentages
        $finalized = [];
        foreach ($totals as $subjectId => $total) {
            $total['percentPass'] = 0;

            if ($total['total'] > 0) {
                $total['percentPass'] = round(($total['passed'] / $total['total']), 2) * 100;
            }

            $finalized[$subjectId] = $total;
        }

        // finalize the vocab reports 
        // i.e. combine vocab indexes that have same % missed
        $finalVocab = [];
        foreach ($vocabTotals as $v) {
            // num times missed divided by # times attempted = % missed
            // combine all with same % missed under same index
            $percentMissed = isset($v['failed']) && $v['total'] > 0 ? round(($v['failed'] / $v['total']), 2) * 100 : 0;

            $finalVocab[$percentMissed]['names'][] = $v['name'];
        }

        // sort final vocab by key
        ksort($finalVocab);
        $finalVocab = array_reverse($finalVocab, true);

        $subjects = Subject::where('client', Config::get('core.client.abbrev'))->where('exam_id', $this->exam->id)->whereNull('report_as')->get();

        return View::make('core::reports.knowledge_detail', [
            'info'     => $this->reportParams,
            'subjects' => $subjects,
            'totals'   => $finalized,
            'vocab'    => $finalVocab
        ]);
    }

    /**
     * Show scheduled exams / types for each test site, broken down by student
     * @param  mixed $from - optional date
     * @param  mixed $to   - optional date
     * @return Response      
     */
    public function scheduledExams($disciplineId, $type, $id, $license, $from = null, $to = null)
    {
        $fromDate = empty($from) ? 0 : new Carbon($from);
        $toDate   = empty($to) ? Carbon::now()->subDay() : new Carbon($to);

        // Get all test dates, grouped by facility ID
        // we join here to easily order the facilities alphabetically by name
        $events  = Testevent::with([
            'students',
            'proctor',
            'testattempts',
            'skillattempts',
            'facility'
        ])
        ->select('testevents.*', 'facilities.name')
        ->join('facilities', 'testevents.facility_id', '=', 'facilities.id')
        ->where('testevents.test_date', '>=', $fromDate)
        ->where('testevents.test_date', '<=', $toDate)
        ->orderBy('facilities.name')
        ->get();

        // Get nice dates for display, based on parameters
        $from = empty($from) ? null : $fromDate->toFormattedDateString();
        $to   = empty($to) ? null : $toDate->toFormattedDateString();

        return View::make('core::reports.scheduled_exams')->with([
            'events' => $events,
            'from'   => $from,
            'to'     => $to
        ]);
    }

    /**
     * Show a test site summary for tests passed / failed / noshow
     * @param  mixed $from
     * @param  mixed $to  
     * @return Response      
     */
    public function siteSummary($disciplineId, $type, $id, $license, $from = null, $to = null)
    {
        // all_facilities or facility
        if (! in_array($type, ['all_facilities', 'facility'])) {
            return Redirect::route('reports.index')->withDanger('Test Site Summary can only be generated for Test Sites');
        }

        $reportType = 'Test Site Summary';
        $type       = ($type == 'all_facilities') ? 'all_testing_facilities' : 'facility';

        // setup class vars along with general validation
        $this->setupReport($reportType, $disciplineId, $type, $id, $license, $from, $to);
        if ($this->error) {
            return Redirect::route('reports.index')->withDanger($this->error);
        }

        $siteIds = $this->getReportProgramIds($type);

        // Get all test dates, grouped by facility ID
        // we join here to easily order the facilities alphabetically by name
        $events  = Testevent::with('testattempts', 'skillattempts')
            ->select('testevents.*', 'facilities.name')
            ->join('facilities', 'testevents.facility_id', '=', 'facilities.id')
            ->where('testevents.discipline_id', $this->discipline->id)
            ->where('testevents.test_date', '>=', $this->fromDate)
            ->where('testevents.test_date', '<=', $this->toDate)
            ->whereIn('testevents.facility_id', $siteIds)
            ->orderBy('facilities.name')
            ->get();

        // no events?
        // test sites $siteIds never tested any students
        if ($events->isEmpty()) {
            return Redirect::route('reports.index')->withDanger('No Test Attempt history found for '.$license);
        }

        $data = $facilities = [];
        $totals['knowledge'] = $totals['skill'] = [
            'passed' => 0,
            'failed' => 0,
            'noshow' => 0
        ];

        foreach ($events as $e) {
            $knowledge = $skill = [
                'passed' => 0,
                'failed' => 0,
                'noshow' => 0
            ];

            foreach ($e->testattempts as $attempt) {
                if ($attempt->status == 'passed') {
                    $knowledge['passed']++;
                } elseif ($attempt->status == 'failed') {
                    $knowledge['failed']++;
                } else {
                    $knowledge['noshow']++;
                }
            }

            foreach ($e->skillattempts as $attempt) {
                if ($attempt->status == 'passed') {
                    $skill['passed']++;
                } elseif ($attempt->status == 'failed') {
                    $skill['failed']++;
                } elseif ($attempt->status != 'archived' && $attempt->status != 'started') {
                    $skill['noshow']++;
                }
            }

            // if we already have some records for this day, combine events by test date
            $currentKnowledge  = $knowledge;
            $currentSkill      = $skill;
            $existingKnowledge = array_get($facilities, $e->facility->id . '.dates.' . $e->test_date . '.knowledge');
            $existingSkill     = array_get($facilities, $e->facility->id . '.dates.' . $e->test_date . '.skill');

            // if we have existing array element(s), merge them
            if (is_array($existingKnowledge)) {
                foreach ($existingKnowledge as $k => $v) {
                    if (array_key_exists($k, $currentKnowledge)) {
                        $currentKnowledge[$k] += $v;
                    }
                }
            }

            if (is_array($existingSkill)) {
                foreach ($existingSkill as $k => $v) {
                    if (array_key_exists($k, $currentSkill)) {
                        $currentSkill[$k] += $v;
                    }
                }
            }

            $facilities[$e->facility_id]['dates'][$e->test_date] = [
                'knowledge' => $currentKnowledge,
                'skill'     => $currentSkill
            ];

            // If there's no subtotals field for this facility, initialize it
            if (! array_key_exists('subtotals', $facilities[$e->facility_id])) {
                $facilities[$e->facility_id]['subtotals']['knowledge'] = $facilities[$e->facility_id]['subtotals']['skill'] = [
                    'passed' => 0,
                    'failed' => 0,
                    'noshow' => 0
                ];
            }

            foreach (array_keys($knowledge) as $status) {
                // update subtotals for each status, for each test type
                $facilities[$e->facility_id]['subtotals']['knowledge'][$status] += $knowledge[$status];
                $facilities[$e->facility_id]['subtotals']['skill'][$status]     += $skill[$status];

                // update totals
                $totals['knowledge'][$status] += $knowledge[$status];
                $totals['skill'][$status]     += $skill[$status];
            }
        } // end foreach

        // sort by date
        $sorted = [];
        foreach ($facilities as $i => $facility) {
            uksort($facility['dates'], function ($a, $b) {
                if ($a == $b) {
                    return 0;
                }

                return (strtotime($a) > strtotime($b)) ? -1 : 1;
            });

            $sorted[$i]['dates']     = $facility['dates'];
            $sorted[$i]['subtotals'] = $facility['subtotals'];
        }

        // add the totals to the data
        $data['facilities'] = $sorted;
        $data['totals']     = $totals;

        // build facility totals
        $facilityTotals = $knowledgeTotals = $skillTotals = [];
        foreach ($facilities as $id => $f) {
            $facilityTotals['knowledge'][$id] = array_sum($f['subtotals']['knowledge']);
            $facilityTotals['skill'][$id] = array_sum($f['subtotals']['skill']);

            $knowledgeTotals['passed'][$id] = $f['subtotals']['knowledge']['passed'];
            $knowledgeTotals['failed'][$id] = $f['subtotals']['knowledge']['failed'];
            $knowledgeTotals['noshow'][$id] = $f['subtotals']['knowledge']['noshow'];

            $skillTotals['passed'][$id] = $f['subtotals']['skill']['passed'];
            $skillTotals['failed'][$id] = $f['subtotals']['skill']['failed'];
            $skillTotals['noshow'][$id] = $f['subtotals']['skill']['noshow'];
        }

        return View::make('core::reports.site_summary')->with([
            'data'            => $data,
            'info'            => $this->reportParams,
            'facilities'      => Facility::orderBy('name')->lists('name', 'id')->all(),
            'facilityTotals'  => $facilityTotals,
            'knowledgeTotals' => $knowledgeTotals,
            'skillTotals'     => $skillTotals,
            'licenses'        => DB::table('facility_discipline')->where('discipline_id', $this->discipline->id)->lists('tm_license', 'facility_id')->all()
        ]);
    }

    /**
     * Retake Summary Report
     *  Always a list of instructors
     *  1st attempt, 2nd attempt, 3rd attempt ...
     */
    public function retakeSummary($disciplineId, $type, $id, $license = null, $from = null, $to = null)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 180);

        $reportType = 'Retake Summary';

        // finalized collection to return
        $retakeInfo = new Collection;

        $reset = [
            'first'  => 0,
            'second' => 0,
            'third'  => 0
        ];

        // setup class vars along with general validation
        $this->setupReport($reportType, $disciplineId, $type, $id, $license, $from, $to);
        if ($this->error) {
            return Redirect::route('reports.index')->withDanger($this->error);
        }

        // retake info per instructor
        $instructorLicenses = [];
        $programIds         = [];
        $programLookup      = [];

        // Training Program
        // get all instructor ids within program under current discipline
        if ($this->facility) {
            $programIds[] = $this->facility->id;
            
            foreach ($this->facility->instructors as $instructor) {
                $instructorLicenses[$instructor->pivot->tm_license] = $instructor->id;
            }
        }
        // Instructor
        elseif ($this->instructor) {
            $instructorLicenses[$this->instructor->tm_license] = $this->instructor->id;

            $programIds[] = $this->instructorProgram->id;
        }
        // All Training Programs
        //  (all that ever trained)
        else {
            $historyTrainingProgramIds = StudentTraining::all()->lists('facility_id')->all();

            if (! empty($historyTrainingProgramIds)) {
                $historyTrainingProgramIds = array_unique($historyTrainingProgramIds);
            }

            // get training programs along with discipline instructors
            $allTrainingPrograms = Facility::with([
                'instructors' => function ($query) use ($disciplineId) {
                    $query->where('facility_person.discipline_id', $disciplineId);
                },
                'allDisciplines' => function ($query) use ($disciplineId) {
                    $query->where('facility_discipline.discipline_id', $disciplineId);
                }
            ])->whereIn('id', $historyTrainingProgramIds)->get();

            foreach ($allTrainingPrograms as $trProgram) {
                foreach ($trProgram->instructors as $instructor) {
                    $instructorLicenses[$instructor->pivot->tm_license] = $instructor->id;

                    // necessary for All Training Programs quick lookup via license
                    $programLookup[$instructor->pivot->tm_license] = $trProgram;
                }
            }
            
            $programIds = $allTrainingPrograms->lists('id')->all();
        }


        // query for ALL instructors here rather than doing it inside loop
        // start query that will pull instructor + student info
        $instructors = Instructor::with([
            'allStudents',
            'allStudents.allPassedStudentTrainings' => function ($q) use ($instructorLicenses, $programIds) {
                $q->where('student_training.discipline_id', $this->discipline->id);
                $q->whereIn('student_training.instructor_id', $instructorLicenses);

                if (! empty($programIds)) {
                    $q->whereIn('student_training.facility_id', $programIds);
                }

                $q->whereIn('student_training.training_id', $this->discipline->training->lists('id')->all());
                $q->orderBy('student_training.ended', 'ASC');
            }
        ]);

        // if knowledge exam exists for current discipline
        if ($this->exam) {
            $instructors->with([
                'allStudents.attempts' => function ($q) {
                    $q->whereIn('testattempts.status', ['passed', 'failed']);
                    $q->where('testattempts.end_time', '>=', $this->fromDate);
                    $q->where('testattempts.end_time', '<=', $this->toDate);
                    $q->where('testattempts.exam_id', $this->exam->id);
                    $q->orderBy('end_time');    // order so we can determine first/second/third
                }
            ]);
        }

        // if skill exam exists for current discipline
        if ($this->skill) {
            $instructors->with([
                'allStudents.skillAttempts' => function ($q) {
                    $q->whereIn('skillattempts.status', ['passed', 'failed']);
                    $q->where('skillattempts.end_time', '>=', $this->fromDate);
                    $q->where('skillattempts.end_time', '<=', $this->toDate);
                    $q->where('skillattempts.skillexam_id', $this->skill->id);
                    $q->orderBy('end_time');
                }
            ]);
        }


        $fromTime = strtotime($this->fromDate->toDateTimeString());
        $toTime   = strtotime($this->toDate->toDateTimeString());

        $instructors = $instructors->get()->keyBy('id');

        foreach ($instructorLicenses as $license => $instructorId) {
            $instructor = $instructors->get($instructorId);

            // setup test history data array
            // reset test count data
            foreach (['knowledge', 'skill'] as $testType) {
                // total # of passed tests per attempt
                $attempts[$testType]['passed'] = $reset;
                // total # of students that attempted first, second, third attempt
                $attempts[$testType]['total']  = $reset;
            }

            // filter students for this instructor or program
            //  (must have passed training with $item and knowledge/skill attempts within date range)
            $students = $instructor->allStudents->filter(function ($st) {
                // no training, always false
                if ($st->allPassedStudentTrainings->isEmpty()) {
                    return false;
                }

                // no skills? must have knowledge attempt(s)
                if (! $this->skill) {
                    return ! $st->attempts->isEmpty();
                }

                // no knowledge? must have skills attempt(s)
                if (! $this->exam) {
                    return ! $st->skillAttempts->isEmpty();
                }

                // both knowledge and skill
                return ! $st->skillAttempts->isEmpty() || ! $st->attempts->isEmpty();
            });


            // each student trained at this program/instructor
            foreach ($students as $student) {
                // combine skill/knowledge test attempts
                // sortBy defaults to ascending, which is what we want
                $allAttempts = $student->testHistory(false);

                // filter passed trainings down to only those within date range
                //  (fromDate will always be set now, required to generate reports)
                $passedTrainingWithinDateRange = $student->allPassedStudentTrainings->filter(function ($training) use ($student, $fromTime, $toTime) {
                    return $fromTime <= strtotime($training->ended) && strtotime($training->ended) <= $toTime;
                })->sortBy('ended');

                // find marker training dates
                //  (used to reset every first, second, third..)
                $markerTrainingDates = $this->getMarkerTrainingDates($student, $allAttempts, $passedTrainingWithinDateRange);

                // ensure we have marker training
                //  (show flash warning for admins)
                if (empty($markerTrainingDates)) {
                    if (Auth::user()->isRole('Admin')) {
                        Flash::warning('Unable to find Marker Training Dates for Student '. $student->commaName);
                    }

                    continue;
                }

                // cycle thru all training we are interested in
                foreach ($markerTrainingDates as $markerDate) {
                    // get all test attempts BEFORE this training
                    // (on first loop thru there shouldnt be any test history before first/initial training)
                    $currAttempts = $allAttempts->filter(function ($att) use ($markerDate) {
                        return strtotime($att->end_date) < strtotime($markerDate);
                    });

                    if (! $currAttempts->isEmpty()) {
                        // used to determine if we are on first/second/third
                        // since skill+knowledge attempts are combined now
                        $count['knowledge'] = 0;
                        $count['skill']     = 0;

                        foreach ($currAttempts as $attempt) {
                            // knowledge or skill attempt?
                            $currType = (class_basename($attempt) == 'Testattempt') ? 'knowledge' : 'skill';

                            // first/second/third
                            if ($currType == 'knowledge') {
                                $count['knowledge']++;
                                $current = $count['knowledge'];
                            } else {
                                $count['skill']++;
                                $current = $count['skill'];
                            }

                            // if outside of date range window dont count towards totals!
                            //  (increment $current so we know if attempt is first/second/third)
                            if (strtotime($attempt->end_date) < $fromTime || $toTime < strtotime($attempt->end_date)) {
                                continue;
                            }

                            // count totals
                            switch ($current) {
                                // first attempt
                                case 1:
                                    $attempts[$currType]['total']['first']++;

                                    if ($attempt->status == 'passed') {
                                        $attempts[$currType]['passed']['first']++;
                                    }

                                    break;

                                // second attempt
                                case 2:
                                    $attempts[$currType]['total']['second']++;

                                    if ($attempt->status == 'passed') {
                                        $attempts[$currType]['passed']['second']++;
                                    }

                                    break;
                                
                                // third and higher attempts
                                default:
                                    $attempts[$currType]['total']['third']++;

                                    if ($attempt->status == 'passed') {
                                        $attempts[$currType]['passed']['third']++;
                                    }

                                    break;
                            }
                        } // end FOREACH test attempt (knowledge/skill)
                    } // end IF currAttempts
                } // end FOREACH marker training dates
            } // end FOREACH students


            // All Facilities
            if ($type == 'all_facilities') {
                $currProgram = $programLookup[$license];
            }
            // Instructor
            elseif ($this->instructor) {
                $currProgram = $this->instructorProgram;
            }
            // Facility
            else {
                $currProgram = $this->facility;
            }

            $instructor->program             = $currProgram;
            $instructor->program->tm_license = $currProgram->allDisciplines->first()->pivot->tm_license;
            $instructor->tm_license          = $license;
            $instructor->counts              = $attempts;
        
            $retakeInfo->put($instructor->id, $instructor);
        }

        // re-order array (only if running for facility/program and has children/grandchildren)
        /*if($this->facility && ( ! $this->children->isEmpty() || ! $this->grandchildren->isEmpty())) 
        {
            $items = $this->reorganizePrograms();
        }*/

        $countReset = [
            'knowledge' => [
                'passed' => $reset,
                'total'  => $reset
            ],
            'skill' => [
                'passed' => $reset,
                'total'  => $reset
            ]
        ];

        // add up totals
        $totals['knowledge']['passed'] = $totals['knowledge']['total'] = $reset;
        $totals['skill']['passed']       = $totals['skill']['total'] = $reset;

        $finalized = [];

        foreach ($retakeInfo as $id => $instructor) {
            // Initialize Program (All Facilities)
            if ($type == 'all_facilities') {
                $currType    = 'program';
                $currId      = $instructor->program->id;
                $currName    = $instructor->program->name;
                $currLicense = $instructor->program->tm_license;
            }
            // Initialize Instructor
            else {
                $currType    = 'instructor';
                $currId      = $instructor->id;
                $currName    = $instructor->commaName;
                $currLicense = $instructor->tm_license;
            }


            // Add to finalized 
            if (! array_key_exists($currName, $finalized)) {
                $finalized[$currName] = [
                    'type'    => $currType,
                    'name'    => $currName,
                    'id'      => $currId,
                    'license' => $currLicense,
                    'counts'  => $countReset
                ];
            }


            // Add Counts
            // knowledge, skill
            foreach ($instructor->counts as $testType => $info) {
                // passed, total
                foreach ($info as $infoType => $counts) {
                    // first, second, third
                    foreach ($counts as $pos => $c) {
                        // increment final totals 
                        //  (all instructor/program totals together)
                        $totals[$testType][$infoType][$pos] += (int) $c;

                        // increment single instructor/program counts
                        $finalized[$currName]['counts'][$testType][$infoType][$pos] += (int) $c;
                    }
                }
            }
        }

        ksort($finalized);

        return View::make('core::reports.retake_summary', [
            'items'  => $finalized,
            'totals' => $totals,
            'info'   => $this->reportParams
        ]);
    }

    /**
     * Reorganize collection of programs
     * So original program will show first (in view), then child, then grandchild
     */
    private function reorganizePrograms()
    {
        $items = new Collection;

        $items->push($this->facility);

        if ($this->children) {
            $items = $items->merge($this->children);
        }
        if ($this->grandchildren) {
            $items = $items->merge($this->grandchildren);
        }

        return $items;
    }

    /**
     * Pass/Fail Report
     * Passed training at requested program/instructor AND has at least 1 knowledge/skill attempt during time frame
     */
    public function passFail($disciplineId, $type, $id, $license, $from = null, $to = null)
    {
        ini_set('memory_limit', '856M');
        ini_set('max_execution_time', 600);
        
        $reportType = 'Pass Fail';

        // setup class vars along with general validation
        $this->setupReport($reportType, $disciplineId, $type, $id, $license, $from, $to);
        if ($this->error) {
            return Redirect::route('reports.index')->withDanger($this->error);
        }


        $reset = [
            'total'  => 0,
            'passed' => 0
        ];

        // initialize totals
        $totals['match']['knowledge'] = $reset;
        $totals['match']['skill']     = $reset;
        $totals['all']['knowledge']   = $reset;
        $totals['all']['skill']       = $reset;
        $totals['all']['total']       = $reset;

        // get all relevent facility_id's
        $programIds = $this->getReportProgramIds();

        // collection holds both skill/knowledge attempts
        $allAttempts = new Collection;

        // get skill attempts within date range + discipline
        if ($this->skill) {
            $skillAttempts = Skillattempt::with([
                'student',
                'testevent',
                'responses.task',
                'studentTraining' => function ($q) use ($programIds) {
                    $q->where('student_training.discipline_id', $this->discipline->id);
                    $q->whereIn('student_training.training_id', $this->discipline->training->lists('id')->all());
                    $q->where('student_training.status', 'passed');
                },
            ])
            ->has('studentTraining')
            ->where('skillexam_id', $this->skill->id)
            ->where('skillattempts.end_time', '>=', $this->fromDate)
            ->where('skillattempts.end_time', '<=', $this->toDate)
            ->whereIn('status', ['passed', 'failed'])
            ->orderBy('skillattempts.end_time')
            ->get();

            if (! $skillAttempts->isEmpty()) {
                $allAttempts = $skillAttempts;
            }
        }

        // get knowledge attempts within date range + discipline
        if ($this->exam) {
            $knowledgeAttempts = Testattempt::with([
                'student',
                'testevent',
                'studentTraining' => function ($q) {
                    $q->where('student_training.discipline_id', $this->discipline->id);
                    $q->whereIn('student_training.training_id', $this->discipline->training->lists('id')->all());
                    $q->where('student_training.status', 'passed');
                },
            ])
            ->has('studentTraining')
            ->where('exam_id', $this->exam->id)
            ->where('testattempts.end_time', '>=', $this->fromDate)
            ->where('testattempts.end_time', '<=', $this->toDate)
            ->whereIn('status', ['passed', 'failed'])
            ->orderBy('testattempts.end_time')
            ->get();

            // include knowledge attempts
            if (! $knowledgeAttempts->isEmpty()) {
                foreach ($knowledgeAttempts as $attempt) {
                    $allAttempts->push($attempt);
                }
            }
        }

        // holds all final students
        //   will include field including all passFail data per student
        $matchedStudents = new Collection;

        foreach ($allAttempts as $attempt) {
            // get current student
            $currStudent = $attempt->student;
            
            // check if attempt matches to instructor/program
            $match = false;

            // instructor
            //  (student took this skill test on a training given by searched for instructor)
            //  (AND also current facility attached to instructor)
            if ($this->instructor && $attempt->studentTraining->instructor_id == $this->instructor->id && in_array($attempt->studentTraining->facility_id, $programIds)) {
                $match = true;
            }
            // training program
            elseif ($this->facility && in_array($attempt->studentTraining->facility_id, $programIds)) {
                $match = true;
            }

            // new matched student?
            // 	add to collection and init test history
            if ($match === true && ! $matchedStudents->has($currStudent->id)) {
                // initialize student test history 
                //  (that will be displayed on page)
                $currStudent->testHistory = array();

                // add student to final collection
                $matchedStudents->put($currStudent->id, $currStudent);
            }


            // Knowledge Attempt
            if (class_basename($attempt) == 'Testattempt') {
                // matched student!
                if ($match === true) {
                    // increment matched total counts
                    $totals['match']['knowledge']['total']++;
                    if ($attempt->status == 'passed') {
                        $totals['match']['knowledge']['passed']++;
                    }

                    $attempt->title         = 'Knowledge';
                    $attempt->tested_date   = $attempt->endDate;
                    $attempt->training_date = $attempt->studentTraining ? $attempt->studentTraining->ended : '';

                    // current student's test history
                    $currTestHistory = $matchedStudents->get($currStudent->id)->testHistory;

                    // no test history for this date yet
                    if (! array_key_exists($attempt->endDate, $currTestHistory)) {
                        $currTestHistory[$attempt->endDate] = array($attempt);
                    }
                    // student already has history for this date
                    //  (push knowledge attempt to front of array! knowledge attempt should always show first per test date)
                    else {
                        array_unshift($currTestHistory[$attempt->endDate], $attempt);
                    }
                }

                // increment statewide total counts
                $totals['all']['knowledge']['total']++;
                if ($attempt->status == 'passed') {
                    $totals['all']['knowledge']['passed']++;
                }
            }
            // Skill Attempt (+ responses)
            else {
                if ($match === true) {
                    // increment matched total counts
                    $totals['match']['skill']['total']++;
                    if ($attempt->status == 'passed') {
                        $totals['match']['skill']['passed']++;
                    }

                    // current student's test history
                    $currTestHistory = $matchedStudents->get($currStudent->id)->testHistory;

                    // map some extra fields on each response
                    foreach ($attempt->responses as $response) {
                        $response->title         = $response->task ? $response->task->title : 'N/A';
                        $response->tested_date   = $attempt->endDate;
                        $response->training_date = $attempt->studentTraining ? $attempt->studentTraining->ended : '';

                        // no test history for this date yet?
                        if (! array_key_exists($attempt->endDate, $currTestHistory)) {
                            $currTestHistory[$attempt->endDate] = array($response);
                        }
                        // test history already exists
                        // append to end of array (skill results always after knowledge results)
                        else {
                            $currTestHistory[$attempt->endDate][] = $response;
                        }
                    }
                }

                // increment statewide total counts
                $totals['all']['skill']['total']++;
                if ($attempt->status == 'passed') {
                    $totals['all']['skill']['passed']++;
                }
            }

            // update current students test history 
            //  (if student is a match)
            if ($match === true) {
                $matchedStudents->get($currStudent->id)->testHistory = $currTestHistory;
            }
        }


        // passed percentages
        $totals['match']['knowledge']['passed_percent'] = $totals['match']['knowledge']['total'] > 0 ? round(($totals['match']['knowledge']['passed'] / $totals['match']['knowledge']['total']) * 100, 1) : '';
        $totals['match']['skill']['passed_percent'] = $totals['match']['skill']['total'] > 0 ? round(($totals['match']['skill']['passed'] / $totals['match']['skill']['total']) * 100, 1) : '';
        $totals['all']['knowledge']['passed_percent'] = $totals['all']['knowledge']['total'] > 0 ? round(($totals['all']['knowledge']['passed'] / $totals['all']['knowledge']['total']) * 100, 1) : '';
        $totals['all']['skill']['passed_percent'] = $totals['all']['skill']['total'] > 0 ? round(($totals['all']['skill']['passed'] / $totals['all']['skill']['total']) * 100, 1) : '';
        // combined totals
        $totals['all']['total']['total'] = $totals['all']['knowledge']['total'] + $totals['all']['skill']['total'];
        $totals['all']['total']['passed'] = $totals['all']['knowledge']['passed'] + $totals['all']['skill']['passed'];
        $totals['all']['total']['passed_percent'] = $totals['all']['total']['total'] > 0 ? round(($totals['all']['total']['passed'] / $totals['all']['total']['total']) * 100, 1) : '';
        // add variance
        $totals['match']['knowledge']['variance'] = ! empty($totals['match']['knowledge']['passed_percent']) && ! empty($totals['all']['knowledge']['passed_percent']) ? round($totals['match']['knowledge']['passed_percent'] - $totals['all']['knowledge']['passed_percent'], 1) : '';
        $totals['match']['skill']['variance'] = ! empty($totals['match']['skill']['passed_percent']) && ! empty($totals['all']['skill']['passed_percent']) ? round($totals['match']['skill']['passed_percent'] - $totals['all']['skill']['passed_percent'], 1) : '';

        return View::make('core::reports.pass_fail', [
            'students' => $matchedStudents,
            'info'     => $this->reportParams,
            'totals'   => $totals
        ]);
    }

    /**
     * Select a facility (and instructor) via ajax calls
     *
     * Note: currently it is only used for the pass/fail report,
     * but in the future it could be configured to submit to a dynamic route
     * via a 3rd routing parameter
     */
    public function selectFacility($from = null, $to = null)
    {
        $facilities = Facility::orderBy('name')->lists('name', 'id')->all();
        $facilities = [0 => 'Please Select'] + $facilities;

        return View::make('core::reports.select_facility')->with([
            'facilities'  => $facilities,
            'instructors' => [],
            'from'        => $from,
            'to'          => $to
        ]);
    }

    /**
     * Find first training within date range for a Student
     *   If, within the specified date range, there are test attempts BEFORE any trainings, 
     *   then we need to go outside (just before start of) date range and find the closest training to first test attempt
     *
     * $attempts  - all test (knowledge/skill) attempts within date range
     * $trainings - all passed trainings for a student within the date range (in ascending order by ended, so last() is most recent)
     *              (collection of Training models, use pivot to access student_training table data)  
     * 
     * Retake summary subfunction
     * Returns array of dates in ascending order
     */
    private function getMarkerTrainingDates($student, $attempts, $trainings)
    {
        $markerDates = [];

        // first training before all test attempts within date range
        $startMarkerTraining = '';

        // get first/last attempt date within date range
        $firstAttemptDate = $attempts->first()->end_date;
        $lastAttemptDate = $attempts->last()->end_date;

        // check for passed training within only the requested date range first
        if (! $trainings->isEmpty()) {
            // trainings are in ascending order
            $firstTraining = $trainings->first();

            // training comes before first test attempt?
            if (strtotime($firstTraining->ended) < strtotime($firstAttemptDate)) {
                $startMarkerTraining = $firstTraining;
            }
        }

        // no start training yet?
        // try looking further back (before fromDate cutoff)
        if (empty($startMarkerTraining)) {
            // filter down to only passed trainings that happened BEFORE the first testattempt
            // order is important (ascending order)
            $passedTrainingsBeforeDateRange = $student->allPassedStudentTrainings->filter(function ($training) use ($firstAttemptDate) {
                return strtotime($training->ended) < strtotime($firstAttemptDate);
            })->sortBy('student_training.ended');

            // no trainings before first test attempt date?
            // test attempt BEFORE any training scenario
            // goto next student (need AT LEAST an initial training)
            if ($passedTrainingsBeforeDateRange->isEmpty()) {

                // spoof beginning start training date
                $startTime     = strtotime($firstAttemptDate);
                $markerDates[] = date('Y-m-d', strtotime("-1 day", $startTime));
                
                // show warning
                //Flash::warning('Unable to locate first Marker Training for ' . Lang::choice('core::terms.student', 1) .' '.$student->full_name.'. Test attempts before Training.');
            }
            // use last() to get most recent, closest to our date range window but *just* before it
            else {
                $startMarkerTraining = $passedTrainingsBeforeDateRange->last();
            }
        }

        // check if the start training marker already exists in our final passed training within date range array
        if (! empty($startMarkerTraining) && ! in_array($startMarkerTraining->id, $trainings->lists('id')->all())) {
            $trainings->push($startMarkerTraining);
        }

        // push training dates into final marker training array in order
        $trainings = $trainings->sortBy('ended')->lists('ended')->all();
        foreach ($trainings as $ended) {
            if (! empty($ended)) {
                $markerDates[] = date('Y-m-d', strtotime($ended));
            }
        }

        // spoofed ending training date (1 day after last testattempt date)
        // (used to grab last set of testattempts after last training when counting first,second,third
        $endTime       = ! empty($lastAttemptDate) ? strtotime($lastAttemptDate) : strtotime('01/01/2100');
        $markerDates[] = date('Y-m-d', strtotime("+1 day", $endTime));

        return $markerDates;
    }

    /**
     * Init vars and do some extra checking
     */
    private function setupReport($reportType, $disciplineId, $userType, $id, $license, $from = null, $to = null)
    {
        $user = Auth::user();

        // check discipline exists
        if (! in_array($disciplineId, $this->allDisciplines->lists('id')->all())) {
            $this->error = 'Invalid Discipline';
            return;
        }
        // check user type is valid
        if (! empty($userType) && ! in_array($userType, ['facility', 'instructor', 'all_facilities', 'all_testing_facilities'])) {
            $this->error = 'Invalid Type';
            return;
        }
        // if restricted to single discipline; only allow run report for currently logged in discipline
        if (Session::has('discipline.id') && Session::get('discipline.id') != $disciplineId) {
            $this->error = 'Unauthorized Discipline';
            return;
        }
        // if restricted to license (running as instructor)
        if (Session::has('discipline.program.license') && Session::get('discipline.program.license') != $license) {
            $this->error = 'Unauthorized License';
            return;
        }


        // Instructor Logged In
        //  (can only run report for SELF)
        if ($user->isRole('Instructor')) {
            $this->instructor = $user->userable;
        }

        // Facility Logged In
        //  (can run reports for SELF or INSTRUCTOR)
        if ($user->isRole('Facility')) {
            if ($userType == 'instructor') {
                $requestInstructor = Instructor::find($id);

                if (is_null($requestInstructor)) {
                    $this->error = 'Unable to locate requested '.Lang::choice('core::terms.instructor', 1);
                    return;
                }

                // set instructor that facility is running report for
                $this->instructor = $requestInstructor;
            } else {
                $this->facility = $user->userable;
            }
        }


        // get requested discipline
        $this->discipline = $this->allDisciplines->keyBy('id')->get($disciplineId);

        // 4/28 we need individual exam/skill to run the report for
        // TEMP FIX - just choose first(), since in oregon its 1 training, 1 knowledge exam, 1 skill exam
        //   later we intend to use vuejs to show only appropriate reports and exams depending on the discipline selected
        if (! $this->discipline->exams->isEmpty()) {
            $this->exam  = $this->discipline->exams->first();
        }
        if (! $this->discipline->skills->isEmpty()) {
            $this->skill = $this->discipline->skills->first();
        }


        // Date range
        if (! empty($from)) {
            $this->fromDate = new Carbon($from);
        }
        if (! empty($to)) {
            $this->toDate = new Carbon($to);
        }
        if ($this->toDate->gte(Carbon::now())) {
            $this->toDate = Carbon::now()->subDay();
        }
        if ($this->fromDate->gte($this->toDate)) {
            $this->error = 'Invalid Date Range -- \'From Date\' must come before \'To Date\'.';
            return;
        }


        // INSTRUCTOR
        if ($userType == 'instructor') {
            // if current user is Instructor this will already be set to userable
            if (! $this->instructor) {
                $this->instructor = Instructor::withTrashed()->find($id);
            }

            $this->instructor->tm_license = $license;

            $res = DB::table('facility_person')
                            ->where('discipline_id', $this->discipline->id)
                            ->where('person_id', $id)
                            ->where('person_type', 'Instructor')
                            ->where('tm_license', $license)
                            ->first();


            if (empty($res)) {
                $this->error = 'Unable to locate associated ' . Lang::choice('core::terms.instructor', 1) . ' by license.';
                return;
            }

            $this->instructorProgram = Facility::withTrashed()->with([
                'allDisciplines' => function ($q) {
                    $q->where('facility_discipline.discipline_id', $this->discipline->id);
                }
            ])->find($res->facility_id);

            $this->reportParams['instructor_name']    = $this->instructor->full_name;
            $this->reportParams['instructor_license'] = $res->tm_license;
            $this->reportParams['program_license']    = $this->instructorProgram->allDisciplines->first()->pivot->tm_license;
            $this->reportParams['program_name']       = $this->instructorProgram->name;
        }

        // TRAINING PROGRAM
        elseif ($userType == 'facility') {
            if (! $this->facility) {
                $this->facility = Facility::withTrashed()->with([
                    'instructors' => function ($q) {
                        $q->where('facility_person.discipline_id', $this->discipline->id);
                    }
                ])->findOrFail($id);
            }

            // check for children programs
            $childIds = DB::table('facility_discipline')
                                ->where('discipline_id', $this->discipline->id)
                                ->where('parent_id', $this->facility->id)
                                ->lists('facility_id')->all();

            if ($childIds) {
                // get facility children
                $this->children = Facility::with(['disciplines' => function ($q) {
                    $q->where('facility_discipline.discipline_id', $this->discipline->id);
                }])->whereIn('id', $childIds)->get();

                // check for grandchildren
                $grandIds = DB::table('facility_discipline')
                                ->where('discipline_id', $this->discipline->id)
                                ->whereIn('parent_id', $childIds)
                                ->lists('facility_id')->all();
                
                if ($grandIds) {
                    // get facility grandchildren
                    $this->grandchildren = Facility::with(['disciplines' => function ($q) {
                        $q->where('facility_discipline.discipline_id', $this->discipline->id);
                    }])->whereIn('id', $grandIds)->get();
                }
            }

            // get program license
            $programDisciplines = $this->facility->allDisciplines->filter(function ($d) {
                return $d->pivot->discipline_id == $this->discipline->id;
            });

            $this->reportParams['program_license'] = $programDisciplines->first()->pivot->tm_license;
            $this->reportParams['program_name']    = $this->facility->name;
        }
        // none
        //  (all facilities)
        else {
            switch ($reportType) {
                case 'Retake Summary':
                    $this->reportParams['program_name'] = 'All ' . Lang::choice('core::terms.facility_training', 2);
                    break;
                
                default:
                    $this->reportParams['program_name'] = 'All ' . Lang::choice('core::terms.facility_testing', 2);
                    break;
            }
        }


        // display logged user and report type
        $this->reportParams['report']             = $reportType;
        $this->reportParams['type']               = $userType;
        // display date range
        $this->reportParams['from']               = empty($this->fromDate) ? null : $this->fromDate->toFormattedDateString();
        $this->reportParams['to']                 = empty($this->toDate) ? null : $this->toDate->toFormattedDateString();
        // display discipline
        $this->reportParams['discipline']         = $this->discipline->name;
        $this->reportParams['discipline_id']      = $this->discipline->id;
        // display skills/knowledge portions for report
        $this->reportParams['show_skills']        = ! $this->discipline->skills->isEmpty();
        $this->reportParams['show_knowledge']     = ! $this->discipline->exams->isEmpty();
        // training program child sites
        $this->reportParams['children']           = $this->children ?: '';
        $this->reportParams['grandchildren']      = $this->grandchildren ?: '';
        $this->reportParams['instructor_program'] = $this->instructorProgram ?: '';
    }

    /**
     * Check if missing data warning must be shown
     * Only necessary for reports where certain data is missing from testmaster import (skill/knowledge detail only currently)
     */
    private function checkForLegacyWarning()
    {
        $legacyDate = Config::get('core.client.data_cutoff');
        
        // no legacy date set for the transition from legacy --> tmu
        // no date to compare against, no warning
        if (empty($legacyDate)) {
            return;
        }

        $cutoff = new Carbon($legacyDate);

        // was fromdate set?
        if (Auth::user()->ability(['Staff', 'Admin'], []) && (empty($this->fromDate) || $this->fromDate->lt($cutoff))) {
            Flash::warning('Legacy data being used. Counts may be incorrect');
        }
    }
}
