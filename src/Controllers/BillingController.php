<?php namespace Hdmaster\Core\Controllers;

use Auth;
use Billing;
use BillingRate;
use Discipline;
use Redirect;
use View;
use Input;
use Session;
use Hdmaster\Core\Controllers\BaseController;
use \Student;
use \Instructor;
use \Proctor;
use \Observer;
use \Actor;
use \Facility;
use \Agency;
use \Testevent;
use Hdmaster\Core\Notifications\Flash;

class BillingController extends \BaseController
{

    protected $billing;

    public function __construct(Billing $billing)
    {
        $this->billing = $billing;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $billing = Billing::all();
        $disciplines = Discipline::all()->lists('abbrev', 'id');

        return View::make('core::accounting.invoice')->with([
            'billings' => $billing,
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
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        //
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
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
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

    public function getTests()
    {
        $know = 0;
        $know_ns = 0;
        $oral = 0;
        $oral_ns = 0;
        $skill = 0;
        $skill_ns = 0;

        $freeKnow = 0;
        $freeSkill = 0;

        $eventList = array();

        $disciplines = Discipline::all()->lists('abbrev', 'id');

        $startDate = substr(Input::get('start_date'), 6, 4) . "-" . substr(Input::get('start_date'), 0, 2) . "-" . substr(Input::get('start_date'), 3, 2);
        $endDate = substr(Input::get('end_date'), 6, 4) . "-" . substr(Input::get('end_date'), 0, 2) . "-" . substr(Input::get('end_date'), 3, 2);

        $events = Testevent::with([
            'discipline',
            'facility',
            'testattempts',
            'knowledgeStudents',
            'skillattempts',
            'skillStudents',
        ])->where('discipline_id', Input::get('report_type'))->where('test_date', '>=', $startDate)->where('test_date', '<=', $endDate)->orderBy('test_date')->get();

        $lastEventId = $events->first()->id;
        $lastEventDate = $events->first()->test_date;
        $lastFacilityId = $events->first()->facility_id;

        foreach ($events as $event) {
            if ($event->id != $lastEventId) {
                if ($know > 0 || $know_ns > 0 || $oral > 0 || $oral_ns > 0 || $skill > 0 || $skill_ns > 0) {
                    array_push($eventList, [
                        'event_date' => $lastEventDate,
                        'event_id' => $lastEventId,
                        'facility_id' => $lastFacilityId,
                        'test_type' => $test_type,
                        'know' => $know,
                        'know_ns' => $know_ns,
                        'oral' => $oral,
                        'oral_ns' => $oral_ns,
                        'skill' => $skill,
                        'skill_ns' => $skill_ns
                    ]);
                }
                
                $lastEventId = $event->id;
                $lastEventDate = $event->test_date;
                $lastFacilityId = $event->facility_id;

                $know = 0;
                $know_ns = 0;
                $oral = 0;
                $oral_ns = 0;
                $skill = 0;
                $skill_ns = 0;
            }
            $test_type = $this->getTestType($event->is_paper, $event->is_regional);

            foreach ($event->testattempts as $attempt) {
                if ($attempt->status == 'assigned' || $attempt->status == 'pending' || $attempt->status == 'unscored' || $attempt->status == 'rescheduled' || $attempt->billing_status == 'invoiced' || $attempt->billing_status == 'paid') {
                    continue;
                }
                if ($attempt->is_oral) {
                    if ($attempt->status == 'noshow') {
                        $oral_ns++;
                    }
                    $oral++;
                    $know++;
                } else {
                    if ($attempt->status == 'noshow') {
                        $know_ns++;
                    }
                    $know++;
                }
                if ($attempt->payable_status == 'free') {
                    $freeKnow++;
                }
            }

            foreach ($event->skillattempts as $attempt) {
                if ($attempt->status == 'assigned' || $attempt->status == 'pending' || $attempt->status == 'unscored' || $attempt->status == 'rescheduled' || $attempt->billing_status == 'invoiced' || $attempt->billing_status == 'paid') {
                    continue;
                }
                if ($attempt->status == 'noshow') {
                    $skill_ns++;
                }
                $skill++;
                if ($attempt->payable_status == 'free') {
                    $freeSkill++;
                }
            }
        }
        if ($know > 0 || $know_ns > 0 || $oral > 0 || $oral_ns > 0 || $skill > 0 || $skill_ns > 0) {
            array_push($eventList, [
                'event_date' => $lastEventDate,
                'event_id' => $lastEventId,
                'facility_id' => $lastFacilityId,
                'test_type' => $test_type,
                'know' => $know,
                'know_ns' => $know_ns,
                'oral' => $oral,
                'oral_ns' => $oral_ns,
                'skill' => $skill,
                'skill_ns' => $skill_ns
            ]);
        }

        $know_r_total = 0;
        $know_r_ns_total = 0;
        $oral_r_total = 0;
        $oral_r_ns_total = 0;
        $skill_r_total = 0;
        $skill_r_ns_total = 0;

        $know_w_total = 0;
        $know_w_ns_total = 0;
        $oral_w_total = 0;
        $oral_w_ns_total = 0;
        $skill_w_total = 0;
        $skill_w_ns_total = 0;

        $know_x_total = 0;
        $know_x_ns_total = 0;
        $oral_x_total = 0;
        $oral_x_ns_total = 0;
        $skill_x_total = 0;
        $skill_x_ns_total = 0;

        $know_y_total = 0;
        $know_y_ns_total = 0;
        $oral_y_total = 0;
        $oral_y_ns_total = 0;
        $skill_y_total = 0;
        $skill_y_ns_total = 0;

        foreach ($eventList as $event) {
            switch ($event['test_type']) {
                case 'R':
                    $know_r_total += $event['know'] - $event['know_ns'];
                    $know_r_ns_total += $event['know_ns'];
                    $oral_r_total += $event['oral'] - $event['oral_ns'];
                    $oral_r_ns_total += $event['oral_ns'];
                    $skill_r_total += $event['skill'] - $event['skill_ns'];
                    $skill_r_ns_total += $event['skill_ns'];
                    break;

                case 'X':
                    $know_x_total += $event['know'] - $event['know_ns'];
                    $know_x_ns_total += $event['know_ns'];
                    $oral_x_total += $event['oral'] - $event['oral_ns'];
                    $oral_x_ns_total += $event['oral_ns'];
                    $skill_x_total += $event['skill'] - $event['skill_ns'];
                    $skill_x_ns_total += $event['skill_ns'];
                    break;

                case 'W':
                    $know_w_total += $event['know'] - $event['know_ns'];
                    $know_w_ns_total += $event['know_ns'];
                    $oral_w_total += $event['oral'] - $event['oral_ns'];
                    $oral_w_ns_total += $event['oral_ns'];
                    $skill_w_total += $event['skill'] - $event['skill_ns'];
                    $skill_w_ns_total += $event['skill_ns'];
                    break;

                case 'Y':
                    $know_y_total += $event['know'] - $event['know_ns'];
                    $know_y_ns_total += $event['know_ns'];
                    $oral_y_total += $event['oral'] - $event['oral_ns'];
                    $oral_y_ns_total += $event['oral_ns'];
                    $skill_y_total += $event['skill'] - $event['skill_ns'];
                    $skill_y_ns_total += $event['skill_ns'];
                    break;
                
                default:
                    break;
            }
        }

        $total_know_r_cnt = $know_r_total - $oral_r_total;
        $total_know_x_cnt = $know_x_total - $oral_x_total;
        $total_know_w_cnt = $know_w_total - $oral_w_total;
        $total_know_y_cnt = $know_y_total - $oral_y_total;

        $total_know_cnt = $total_know_r_cnt + $total_know_x_cnt + $total_know_w_cnt + $total_know_y_cnt - $freeKnow;
        $total_know_ns_cnt = $know_r_ns_total + $know_x_ns_total + $know_w_ns_total + $know_y_ns_total;

        $total_oral_cnt = $oral_r_total + $oral_x_total + $oral_w_total + $oral_y_total;
        $total_oral_ns_cnt = $oral_r_ns_total + $oral_x_ns_total + $oral_w_ns_total + $oral_y_ns_total;

        $total_skill_cnt = $skill_r_total + $skill_x_total + $skill_w_total + $skill_y_total;
        $total_skill_ns_cnt = $skill_r_ns_total + $skill_x_ns_total + $skill_w_ns_total + $skill_y_ns_total;

        if (Input::get('report_type') == 1) {
            $knowRate = BillingRate::find(1);
        } else {
            $knowRate = BillingRate::find(4);
        }
        $oralRate = BillingRate::find(2);
        $skillRate = BillingRate::find(3);

        $know_due = $total_know_cnt * $knowRate->rate;
        $know_ns_due = $total_know_ns_cnt * $knowRate->rate_ns;

        $oral_due = $total_oral_cnt * $oralRate->rate;
        $oral_ns_due = $total_oral_ns_cnt * $oralRate->rate_ns;

        $skill_due = $total_skill_cnt * $skillRate->rate;
        $skill_ns_due = $total_skill_ns_cnt * $skillRate->rate_ns;

        $total_due = $know_due + $know_ns_due + $oral_due + $oral_ns_due + $skill_due + $skill_ns_due;

        $studentList = array();

        if (count($eventList)) {
            foreach ($events as $event) {
                foreach ($event->knowledgeStudents as $student) {
                    array_push($studentList, [
                        'student_id' => $student->id,
                        'facility_id' => $event->facility_id,
                        'facility' => $event->facility->name,
                        'event_id' => $event->id,
                        'test_date' => $event->test_date,
                        'type' => $this->getTestType($event->is_paper, $event->is_regional),
                        'name' => $student->first . " " . $student->last,
                        'dob' => $student->birthdate,
                        'rater' => $event->observer->first . " " . $event->observer->last,
                        'knowledge' => 'K',
                        'skill' => '',
                        'noshow' => '',
                        'free' => '',
                        'oral' => ''
                    ]);
                }

                foreach ($event->skillStudents as $student) {
                    $added = false;
                    for ($i=0; $i < count($studentList); $i++) {
                        if ($studentList[$i]['student_id'] == $student->id && $event->id == $studentList[$i]['event_id']) {
                            $studentList[$i]['skill'] = "S";
                            $added = true;
                            $i = count($studentList);
                        }
                    }
                    if (! $added) {
                        array_push($studentList, [
                            'student_id' => $student->id,
                            'facility_id' => $event->facility_id,
                            'facility' => $event->facility->name,
                            'event_id' => $event->id,
                            'test_date' => $event->test_date,
                            'type' => $this->getTestType($event->is_paper, $event->is_regional),
                            'name' => $student->first . " " . $student->last,
                            'dob' => $student->birthdate,
                            'rater' => $event->observer->first . " " . $event->observer->last,
                            'knowledge' => '',
                            'skill' => 'S',
                            'noshow' => '',
                            'free' => '',
                            'oral' => ''
                        ]);
                        $added = true;
                    }
                }

                foreach ($event->testattempts as $attempt) {
                    for ($i=0; $i < count($studentList); $i++) {
                        if ($studentList[$i]['student_id'] == $attempt->student_id) {
                            if ($attempt->status == 'noshow') {
                                $studentList[$i]['noshow'] = "*NS*";
                            }
                            if ($attempt->payable_status == 'free') {
                                $studentList[$i]['free'] = "*FREE*";
                            }
                            if ($attempt->is_oral) {
                                $studentList[$i]['oral'] = "*Oral*";
                            }
                        }
                    }
                }

                foreach ($event->skillattempts as $attempt) {
                    for ($i=0; $i < count($studentList); $i++) {
                        if ($studentList[$i]['student_id'] == $attempt->student_id) {
                            // Split knowledge and skill if skill is a noshow and knowledge it not
                            if ($studentList[$i]['noshow'] == '' && $attempt->status == 'noshow') {
                                if ($studentList[$i]['knowledge'] == 'K') {
                                    $studentList[$i]['skill'] = '';
                                    array_push($studentList, [
                                        'student_id' => $studentList[$i]['student_id'],
                                        'facility_id' => $studentList[$i]['facility_id'],
                                        'facility' => $studentList[$i]['facility'],
                                        'event_id' => $studentList[$i]['event_id'],
                                        'test_date' => $studentList[$i]['test_date'],
                                        'type' => $studentList[$i]['type'],
                                        'name' => $studentList[$i]['name'],
                                        'dob' => $studentList[$i]['dob'],
                                        'rater' => $studentList[$i]['rater'],
                                        'knowledge' => '',
                                        'skill' => 'S',
                                        'noshow' => '*NS*',
                                        'free' => '',
                                        'oral' => ''
                                    ]);
                                } else {
                                    $studentList[$i]['noshow'] = "*NS*";
                                }
                            } elseif ($attempt->status == 'noshow') {
                                $studentList[$i]['noshow'] = "*NS*";
                            }
                            if ($attempt->payable_status == 'free') {
                                $studentList[$i]['free'] = "*FREE*";
                            }
                            // Split knowledge and skill if knowledge is a noshow and skill is not.
                            if ($studentList[$i]['noshow'] == "*NS*" && $attempt->status != "noshow") {
                                $studentList[$i]['skill'] = '';
                                array_push($studentList, [
                                    'student_id' => $studentList[$i]['student_id'],
                                    'facility_id' => $studentList[$i]['facility_id'],
                                    'facility' => $studentList[$i]['facility'],
                                    'event_id' => $studentList[$i]['event_id'],
                                    'test_date' => $studentList[$i]['test_date'],
                                    'type' => $studentList[$i]['type'],
                                    'name' => $studentList[$i]['name'],
                                    'dob' => $studentList[$i]['dob'],
                                    'rater' => $studentList[$i]['rater'],
                                    'knowledge' => '',
                                    'skill' => 'S',
                                    'noshow' => '',
                                    'free' => '',
                                    'oral' => ''
                                ]);
                            }
                        }
                    }
                }
            }
        }
        
        return View::make('core::accounting.invoice')->with([
            'disciplines' => $disciplines,
            'eventlist' => $eventList,
            'know_r_total' => $total_know_r_cnt,
            'know_r_ns_total' => $know_r_ns_total,
            'oral_r_total' => $oral_r_total,
            'oral_r_ns_total' => $oral_r_ns_total,
            'skill_r_total' => $skill_r_total,
            'skill_r_ns_total' => $skill_r_ns_total,
            'know_x_total' => $total_know_x_cnt,
            'know_x_ns_total' => $know_x_ns_total,
            'oral_x_total' => $oral_x_total,
            'oral_x_ns_total' => $oral_x_ns_total,
            'skill_x_total' => $skill_x_total,
            'skill_x_ns_total' => $skill_x_ns_total,
            'know_w_total' => $total_know_w_cnt,
            'know_w_ns_total' => $know_w_ns_total,
            'oral_w_total' => $oral_w_total,
            'oral_w_ns_total' => $oral_w_ns_total,
            'skill_w_total' => $skill_w_total,
            'skill_w_ns_total' => $skill_w_ns_total,
            'know_y_total' => $total_know_y_cnt,
            'know_y_ns_total' => $know_y_ns_total,
            'oral_y_total' => $oral_y_total,
            'oral_y_ns_total' => $oral_y_ns_total,
            'skill_y_total' => $skill_y_total,
            'skill_y_ns_total' => $skill_y_ns_total,
            'know_due' => $know_due,
            'know_ns_due' => $know_ns_due,
            'oral_due' => $oral_due,
            'oral_ns_due' => $oral_ns_due,
            'skill_due' => $skill_due,
            'skill_ns_due' => $skill_ns_due,
            'total_due' => $total_due,
            'start_date' => Input::get('start_date'),
            'end_date' => Input::get('end_date'),
            'report_type' => Input::get('report_type'),
            'studentlist' => $studentList
        ]);
    }

    public function invoiceCsv($startDate, $endDate, $discipline_id)
    {
        $start_date = substr($startDate, 4) . "-" . substr($startDate, 0, 2) . "-" . substr($startDate, 2, 2);
        $end_date = substr($endDate, 4) . "-" . substr($endDate, 0, 2) . "-" . substr($endDate, 2, 2);
        
        $know = 0;
        $know_ns = 0;
        $oral = 0;
        $oral_ns = 0;
        $skill = 0;
        $skill_ns = 0;

        $freeKnow = 0;
        $freeSkill = 0;

        $eventList = array();

        $events = Testevent::with([
            'discipline',
            'facility',
            'testattempts',
            'knowledgeStudents',
            'skillattempts',
            'skillStudents',
        ])->where('discipline_id', $discipline_id)->where('test_date', '>=', $start_date)->where('test_date', '<=', $end_date)->orderBy('test_date')->get();

        $studentList = array();

        foreach ($events as $event) {
            foreach ($event->knowledgeStudents as $student) {
                array_push($studentList, [
                    'student_id' => $student->id,
                    'facility_id' => $event->facility_id,
                    'facility' => $event->facility->name,
                    'event_id' => $event->id,
                    'test_date' => $event->test_date,
                    'type' => $this->getTestType($event->is_paper, $event->is_regional),
                    'name' => $student->first . " " . $student->last,
                    'dob' => $student->birthdate,
                    'rater' => $event->observer->first . " " . $event->observer->last,
                    'knowledge' => 'K',
                    'skill' => '',
                    'noshow' => '',
                    'free' => '',
                    'oral' => ''
                ]);
            }

            foreach ($event->skillStudents as $student) {
                $added = false;
                for ($i=0; $i < count($studentList); $i++) {
                    if ($studentList[$i]['student_id'] == $student->id && $event->id == $studentList[$i]['event_id']) {
                        $studentList[$i]['skill'] = "S";
                        $added = true;
                        $i = count($studentList);
                    }
                }
                if (! $added) {
                    array_push($studentList, [
                        'student_id' => $student->id,
                        'facility_id' => $event->facility_id,
                        'facility' => $event->facility->name,
                        'event_id' => $event->id,
                        'test_date' => $event->test_date,
                        'type' => $this->getTestType($event->is_paper, $event->is_regional),
                        'name' => $student->first . " " . $student->last,
                        'dob' => $student->birthdate,
                        'rater' => $event->observer->first . " " . $event->observer->last,
                        'knowledge' => '',
                        'skill' => 'S',
                        'noshow' => '',
                        'free' => '',
                        'oral' => ''
                    ]);
                    $added = true;
                }
            }

            foreach ($event->testattempts as $attempt) {
                for ($i=0; $i < count($studentList); $i++) {
                    if ($studentList[$i]['student_id'] == $attempt->student_id) {
                        if ($attempt->status == 'noshow') {
                            $studentList[$i]['noshow'] = "*NS*";
                        }
                        if ($attempt->payable_status == 'free') {
                            $studentList[$i]['free'] = "*FREE*";
                        }
                        if ($attempt->is_oral) {
                            $studentList[$i]['oral'] = "*Oral*";
                        }
                    }
                }
            }

            foreach ($event->skillattempts as $attempt) {
                for ($i=0; $i < count($studentList); $i++) {
                    if ($studentList[$i]['student_id'] == $attempt->student_id) {
                        // Split knowledge and skill if skill is a noshow and knowledge it not
                        if ($studentList[$i]['noshow'] == '' && $attempt->status == 'noshow') {
                            if ($studentList[$i]['knowledge'] == 'K') {
                                $studentList[$i]['skill'] = '';
                                array_push($studentList, [
                                    'student_id' => $studentList[$i]['student_id'],
                                    'facility_id' => $studentList[$i]['facility_id'],
                                    'facility' => $studentList[$i]['facility'],
                                    'event_id' => $studentList[$i]['event_id'],
                                    'test_date' => $studentList[$i]['test_date'],
                                    'type' => $studentList[$i]['type'],
                                    'name' => $studentList[$i]['name'],
                                    'dob' => $studentList[$i]['dob'],
                                    'rater' => $studentList[$i]['rater'],
                                    'knowledge' => '',
                                    'skill' => 'S',
                                    'noshow' => '*NS*',
                                    'free' => '',
                                    'oral' => ''
                                ]);
                            } else {
                                $studentList[$i]['noshow'] = "*NS*";
                            }
                        } elseif ($attempt->status == 'noshow') {
                            $studentList[$i]['noshow'] = "*NS*";
                        }
                        if ($attempt->payable_status == 'free') {
                            $studentList[$i]['free'] = "*FREE*";
                        }
                        // Split knowledge and skill if knowledge is a noshow and skill is not.
                        if ($studentList[$i]['noshow'] == "*NS*" && $attempt->status != "noshow") {
                            $studentList[$i]['skill'] = '';
                            array_push($studentList, [
                                'student_id' => $studentList[$i]['student_id'],
                                'facility_id' => $studentList[$i]['facility_id'],
                                'facility' => $studentList[$i]['facility'],
                                'event_id' => $studentList[$i]['event_id'],
                                'test_date' => $studentList[$i]['test_date'],
                                'type' => $studentList[$i]['type'],
                                'name' => $studentList[$i]['name'],
                                'dob' => $studentList[$i]['dob'],
                                'rater' => $studentList[$i]['rater'],
                                'knowledge' => '',
                                'skill' => 'S',
                                'noshow' => '',
                                'free' => '',
                                'oral' => ''
                            ]);
                        }
                    }
                }
            }
        }

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=InvoiceDetail.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $out = fopen('php://output', 'w');

        fputcsv($out, array("Site ID", "Site", "Pkt#", "Date", "Typ", "Candidate", "DOB", "Rater", "Tests", ""));

        foreach ($studentList as $student) {
            fputcsv($out, [$student['facility_id'], $student['facility'], $student['event_id'], $student['test_date'], $student['type'], $student['name'], $student['dob'], $student['rater'], $student['knowledge'] . $student['skill'], $student['noshow'] . " " . $student['free'] . " " . $student['oral']]);
        }

        fclose($out);
    }

    public function getBilling()
    {
        $startDate = substr(Input::get('start_date'), 6, 4) . "-" . substr(Input::get('start_date'), 0, 2) . "-" . substr(Input::get('start_date'), 3, 2);
        $endDate = substr(Input::get('end_date'), 6, 4) . "-" . substr(Input::get('end_date'), 0, 2) . "-" . substr(Input::get('end_date'), 3, 2);

        $billingStatus = Input::get('billing_status');

        $events = Testevent::with([
            'facility',
            'testattempts',
            'testattempts.student',
            'skillattempts',
            'skillattempts.student'
        ])->where('test_date', '>=', $startDate)->where('test_date', '<=', $endDate)->get();

        $billingAttempts = array();

        foreach ($events as $event) {
            $testAttempts = $event->testattempts;

            if ($billingStatus != '') {
                $testAttempts = $testAttempts->filter(function ($attempt) use ($billingStatus) {
                    if ($attempt->billing_status == $billingStatus) {
                        return true;
                    }
                });
            }

            foreach ($testAttempts as $attempt) {
                array_push($billingAttempts, [
                    'attempt_id' => $attempt->id,
                    'facility_name' => $event->facility->name,
                    'student_name' => $attempt->student->first . " " . $attempt->student->last,
                    'test_type' => 'Knowledge',
                    'status' => $attempt->status,
                    'payment_status' => $attempt->payment_status,
                    'payable_status' => $attempt->payable_status,
                    'billing_status' => $attempt->billing_status
                ]);
            }

            $skillAttempts = $event->skillattempts;

            if ($billingStatus != '') {
                $skillAttempts = $skillAttempts->filter(function ($attempt) use ($billingStatus) {
                    if ($attempt->billing_status == $billingStatus) {
                        return true;
                    }
                });
            }

            foreach ($skillAttempts as $attempt) {
                array_push($billingAttempts, [
                    'attempt_id' => $attempt->id,
                    'facility_name' => $event->facility->name,
                    'student_name' => $attempt->student->first . " " . $attempt->student->last,
                    'test_type' => 'Skill',
                    'status' => $attempt->status,
                    'payment_status' => $attempt->payment_status,
                    'payable_status' => $attempt->payable_status,
                    'billing_status' => $attempt->billing_status
                ]);
            }
        }

        return View::make('core::accounting.manage_billing')->with([
            'billingAttempts' => $billingAttempts,
            'start_date' => Input::get('start_date'),
            'end_date' => Input::get('end_date'),
            'billing_status' => Input::get('billing_status')
        ]);
    }

    public function manageBilling()
    {
        return View::make('core::accounting.manage_billing')->with([
            'billing_status' => ''
        ]);
    }

    public function updateBilling($id, $type, $status)
    {
        if ($type == "K") {
            \DB::update("UPDATE testattempts SET billing_status = ? WHERE id = ?", array($status, $id));
        } else {
            \DB::update("UPDATE skillattempts SET billing_status = ? WHERE id = ?", array($status, $id));
        }
    }

    public function updateStudentBilling($id, $type, $status)
    {
        if ($type == 'knowledge') {
            \DB::update("UPDATE testattempts SET payment_status = ? WHERE id = ?", array($status, $id));
        }
        if ($type == 'skill') {
            \DB::update("UPDATE skillattempts SET payment_status = ? WHERE id = ?", array($status, $id));
        }
    }

    public function markInvoiced()
    {
        $eventlist = Session::get('eventlist');

        foreach ($eventlist as $event) {
            $testEvents = Testevent::with([
                'testattempts'
            ])->where('id', $event['event_id'])->get();

            foreach ($testEvents as $e) {
                foreach ($e->testattempts as $attempt) {
                    if ($attempt->status != "assigned") {
                        $attempt->billing_status = 'invoiced';
                        $attempt->save();
                    }
                }
                foreach ($e->skillattempts as $attempt) {
                    if ($attempt->status != "assigned") {
                        $attempt->billing_status = 'invoiced';
                        $attempt->save();
                    }
                }
            }
        }

        Session::forget('eventlist');
        Session::flash('success', 'Invoice have been marked invoiced successfully.');
        return Redirect::route('accounting.invoice');
    }

    public function getTestType($is_paper, $is_regional)
    {
        if ($is_paper && $is_regional) {        // Paper Test Regional [R]
            return 'R';
        }
        if ($is_paper && ! $is_regional) {        // Paper Test Flexible [X]
            return 'X';
        }
        if (! $is_paper && ! $is_regional) {    // Web Test Flexible [W]
            return 'W';
        }
        if (! $is_paper && $is_regional) {        // Web Test Regional [Y]
            return 'Y';
        }
    }
}
