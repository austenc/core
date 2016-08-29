<?php namespace Hdmaster\Core\Controllers;

use Auth;
use Redirect;
use Discipline;
use PayableRate;
use Pendingscore;
use View;
use Input;
use Session;
use Testevent;
use Hdmaster\Core\Controllers\BaseController;
use \Student;
use \Instructor;
use \Proctor;
use \Observer;
use \Actor;
use \Facility;
use \Agency;
use Hdmaster\Core\Notifications\Flash;

class PayablesController extends \BaseController
{

    protected $payrates;

    protected $totalKnowledgeR;
    protected $totalKnowledgeR_NS;
    protected $totalKnowledgeX;
    protected $totalKnowledgeX_NS;
    protected $totalKnowledgeW;
    protected $totalKnowledgeW_NS;
    protected $totalKnowledgeY;
    protected $totalKnowledgeY_NS;

    protected $totalKnowledgeR_Special_NS;
    protected $totalKnowledgeR_Special;
    protected $totalKnowledgeX_Special_NS;
    protected $totalKnowledgeX_Special;
    protected $totalKnowledgeW_Special_NS;
    protected $totalKnowledgeW_Special;
    protected $totalKnowledgeY_Special_NS;
    protected $totalKnowledgeY_Special;

    protected $totalOralR;
    protected $totalOralR_NS;
    protected $totalOralX;
    protected $totalOralX_NS;
    protected $totalOralW;
    protected $totalOralW_NS;
    protected $totalOralY;
    protected $totalOralY_NS;

    protected $totalSkillR;
    protected $totalSkillR_NS;
    protected $totalSkillX;
    protected $totalSkillX_NS;
    protected $totalSkillW;
    protected $totalSkillW_NS;
    protected $totalSkillY;
    protected $totalSkillY_NS;

    protected $payablelist;

    protected $totalDueKnowledge;
    protected $totalDueOral;
    protected $totalDueSkill;


    protected $oral_R;
    protected $oral_R_NS;
    protected $knowledge_R;
    protected $knowledge_R_NS;

    protected $oral_W_NS;
    protected $oral_X_NS;
    protected $oral_Y_NS;
    
    protected $oral_W;
    protected $oral_X;
    protected $oral_Y;
    
    protected $knowledge_W_NS;
    protected $knowledge_X_NS;
    protected $knowledge_Y_NS;
    
    protected $knowledge_W;
    protected $knowledge_X;
    protected $knowledge_Y;

    public function __construct(PayableRate $payrates)
    {
        $this->payrates = $payrates;

        $this->totalKnowledgeR = 0;
        $this->totalKnowledgeR_NS = 0;
        $this->totalKnowledgeX = 0;
        $this->totalKnowledgeX_NS = 0;
        $this->totalKnowledgeW = 0;
        $this->totalKnowledgeW_NS = 0;
        $this->totalKnowledgeY = 0;
        $this->totalKnowledgeY_NS = 0;

        $this->totalKnowledgeR_Special_NS = 0;
        $this->totalKnowledgeR_Special = 0;
        $this->totalKnowledgeX_Special_NS = 0;
        $this->totalKnowledgeX_Special = 0;
        $this->totalKnowledgeW_Special_NS = 0;
        $this->totalKnowledgeW_Special = 0;
        $this->totalKnowledgeY_Special_NS = 0;
        $this->totalKnowledgeY_Special = 0;

        $this->totalOralR = 0;
        $this->totalOralR_NS = 0;
        $this->totalOralX = 0;
        $this->totalOralX_NS = 0;
        $this->totalOralW = 0;
        $this->totalOralW_NS = 0;
        $this->totalOralY = 0;
        $this->totalOralY_NS = 0;

        $this->totalSkillR = 0;
        $this->totalSkillR_NS = 0;
        $this->totalSkillX = 0;
        $this->totalSkillX_NS = 0;
        $this->totalSkillW = 0;
        $this->totalSkillW_NS = 0;
        $this->totalSkillY = 0;
        $this->totalSkillY_NS = 0;

        $this->payablelist = array();

        $this->totalDueKnowledge = 0;
        $this->totalDueOral = 0;
        $this->totalDueSkill = 0;
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

    public function payrates()
    {
        $payrates = PayableRate::all();
        $disciplines = Discipline::all();

        return View::make('core::accounting.payrates')->with([
            'payrates' => $payrates,
            'disciplines' => $disciplines
        ]);
    }

    public function billing()
    {
        $knowledge = 0;
        $knowledge_ns = 0;
        $oral = 0;
        $oral_ns = 0;
        $skill = 0;
        $skill_ns = 0;

        $special = 0;
        $specialDeduct = 0;

        $pendScoring = false;

        $cnaTests = array();
        $cmaTests = array();

        $payables = array();
        // Set an end date to look back on payables for the past 30 days.
        // This has to check on 7/9/2016 because all events prior to this date 
        // have a deleted observer_id and causes script to completely fail.
        if (date("Y-m-d", strtotime("-60 days") < strtotime("2016-07-09"))) {
            $endDate = "2016-07-09";
        } else {
            $endDate = date("Y-m-d", strtotime("-30 days"));
        }

        $pendingScoring = Pendingscore::lists('scoreable_id');

        $events = Testevent::with([
            'discipline',
            'testattempts',
            'knowledgeStudents',
            'knowledgeStudents.adas',
            'skillattempts',
            'skillStudents',
            'skillStudents.adas'
        ])->where('test_date', '>=', $endDate)->where('test_date', '<=', date("Y-m-d"))->orderBy('observer_id')->get();
        // The above query pulls 61 events as expected at the database level query with these restrictions on the day of development
        // Trying to add a conditional whereHas and orWhereHas with a payable_status != 'paid' returned 506 results when these are 
        // placed at the end of the query and returns 1350 results when put at the onset of this query. Either case is returning a 
        // ton more results then are actually there for events on the same date constraint. This will cause additional checks to make
        // sure the skillattempt/testattempt have not bee paid previously.

        $lastObserverId = $events->first()->observer->id;
        $observer = Observer::find($lastObserverId);

        // Validate $events has events, if yes, loop through events otherwise return null to page view
        if ($events->count()) {
            foreach ($events as $event) {
                /*
                ** First check CNA or CMA discipline abbreviation as CNA and CMA are paid different rates and 
                ** have different condition. The ending else is due to discipline abbreviation being editable 
                ** for an end user. Should this accidentally or with intention be changed accounting would no 
                ** longer know how to process the payable that would be due.
                */
            
                foreach ($event->testattempts as $attempt) {
                    if (in_array($attempt->id, $pendingScoring)) {
                        $pendScoring = true;
                        continue;
                    }
                }
                foreach ($event->skillattempts as $attempt) {
                    if (in_array($attempt->id, $pendingScoring)) {
                        $pendScoring = true;
                        continue;
                    }
                }

                if ($pendScoring) {
                    $pendScoring = false;
                    continue;
                }

                if ($event->discipline->abbrev == "CNA") {
                    $test_type = $this->getTestType($event->is_paper, $event->is_regional);

                    if ($event->observer_id != $lastObserverId) {
                        $totalKnowledgeCnt = 0;
                        $totalOralCnt = 0;
                        $totalSkillCnt = 0;
                        $totalAdaCnt = 0;

                        $cmaTotalKnowledgeCnt = 0;
                        $cmaTotalSpecialCnt = 0;
                        $cmaTotalAdaCnt = 0;

                        foreach ($cnaTests as $cnatest) {
                            $totalKnowledgeCnt += $cnatest['knowledge'];
                            $totalOralCnt += $cnatest['oral'] + $cnatest['oral_ns'];
                            $totalSkillCnt += $cnatest['skill'];
                            $totalAdaCnt += $cnatest['adas'];
                        }

                        foreach ($cmaTests as $cmatest) {
                            $cmaTotalKnowledgeCnt += $cmatest['knowledge'];
                            $cmaTotalSpecialCnt += $cmatest['special'];
                            $cmaTotalAdaCnt += $cmatest['adas'];
                        }

                        $cnaPayrates = PayableRate::find($observer->payable_rate);

                        $cmaPayrates = PayableRate::find(4);    // Hard coded at this time until determination if a CMA rates table should be used

                        $totalCnaKnowledgeDue = $totalKnowledgeCnt * $cnaPayrates->knowledge_rate;
                        $totalCnaOralDue    = $totalOralCnt * $cnaPayrates->oral_rate;
                        $totalCnaSkillDue    = $totalSkillCnt * $cnaPayrates->skill_rate;
                        $totalCmaKnowledgeDue = ($cmaTotalKnowledgeCnt - $cmaTotalSpecialCnt) * $cmaPayrates->knowledge_rate;
                        $totalCmaSpecialDue    = $cmaTotalSpecialCnt * $cmaPayrates->special_rate;

                        $totalKnowledgeDue = $totalCnaKnowledgeDue + $totalCmaKnowledgeDue + $totalCmaSpecialDue;

                        $totalAdaDue = ($totalAdaCnt * $cnaPayrates->ada_rate) + ($cmaTotalAdaCnt * $cmaPayrates->ada_rate);

                        $totalDue = $totalKnowledgeDue + $totalCnaOralDue + $totalCnaSkillDue + $totalAdaDue;

                        $eventArr = array();

                        foreach ($cnaTests as $test) {
                            array_push($eventArr, $test);
                        }
                        foreach ($cmaTests as $test) {
                            array_push($eventArr, $test);
                        }

                        array_push($payables, [
                            'observerId' => $observer->id,
                            'first' => $observer->first,
                            'last' => $observer->last,
                            'address' => $observer->address,
                            'city' => $observer->city,
                            'state' => $observer->state,
                            'zip' => $observer->zip,
                            'totalKnowledgeDue' => $totalKnowledgeDue,
                            'totalSkillDue' => $totalCnaSkillDue,
                            'totalOralDue' => $totalCnaOralDue,
                            'totalAdaDue' => $totalAdaDue,
                            'totalDue' => $totalDue,
                            'events' => $eventArr
                        ]);

                        $knowledge = 0;
                        $knowledge_ns = 0;
                        $oral = 0;
                        $oral_ns = 0;
                        $skill = 0;
                        $skill_ns = 0;

                        $special = 0;
                        $specialDeduct = 0;

                        $cnaTests = array();
                        $cmaTests = array();

                        $lastObserverId = $event->observer_id;
                        $observer = Observer::find($lastObserverId);
                    }

                    foreach ($event->testattempts as $testattempt) {
                        // Any one of these conditions the testattempt will not be paid to the observer until they are resolved and changed
                        if ($testattempt->payable_status == 'paid' || $testattempt->status == 'assigned' || $testattempt->status == 'pending' || $testattempt->status == 'started' || $testattempt->status == 'unscored') {
                            continue;
                        }
                        if ($testattempt->status == 'noshow') {
                            if ($testattempt->is_oral) {
                                $oral_ns++;
                                $knowledge_ns++; // Mandi wants total knowledge including orals under knowledge_ns, oral_ns is displayed and calculated at $2.
                            } else {
                                $knowledge_ns++;
                                $knowledge++;
                            }
                        } else {
                            if ($testattempt->is_oral) {
                                $oral++;
                                $knowledge++;    // Mandi wants total knowledge including orals under knowledge_ns, oral_ns is displayed and calculated at $2.
                            } else {
                                $knowledge++;
                            }
                        }
                    }
                    foreach ($event->skillattempts as $skillattempt) {
                        // Any one of these conditions the testattempt will not be paid to the observer until they are resolved and changed
                        if ($skillattempt->payable_status == 'paid' || $skillattempt->status == 'assigned' || $skillattempt->status == 'pending' || $skillattempt->status == 'started' || $skillattempt->status == 'unscored') {
                            continue;
                        }
                        if ($skillattempt->status == 'noshow') {
                            $skill_ns++;
                            $skill++;
                        } else {
                            $skill++;
                        }
                    }

                    $knowAda = array();
                    $skillAda = array();
                    $bothAda = array();

                    foreach ($event->knowledgeStudents as $student) {
                        foreach ($student->adas as $ada) {
                            switch ($ada->test_type) {
                                case 'knowledge':
                                    array_push($knowAda, [$ada->pivot->student_id, $ada->id]);
                                    break;

                                case 'both':
                                    array_push($bothAda, [$ada->pivot->student_id, $ada->id]);
                                    break;
                                
                                default:
                                    # code...
                                    break;
                            }
                        }
                    }

                    foreach ($event->skillStudents as $student) {
                        foreach ($student->adas as $ada) {
                            switch ($ada->test_type) {
                                case 'skill':
                                    array_push($skillAda, [$ada->pivot->student_id, $ada->id]);
                                    break;
                                
                                case 'both':
                                    if (! in_array($ada->pivot->student_id, $bothAda)) {
                                        array_push($bothAda, [$ada->pivot->student_id, $ada->id]);
                                    }
                                    break;
                                
                                default:
                                    # code...
                                    break;
                            }
                        }
                    }

                    $adas = count($knowAda) + count($skillAda) + count($bothAda);

                    // Check there are actual values for these variables before adding to the cmaTest array.
                    // A zero count on the events array returned to the view is stopping display of oberservers 
                    // who have no events in the payable period being calculated
                    if ($knowledge > 0 || $knowledge_ns > 0 || $oral > 0 || $oral_ns > 0 || $skill > 0 || $skill_ns > 0) {
                        // Zero values added for due to need to combine $cmaTests with $cnaTests when sending to the veiw
                        array_push($cnaTests, ['event_id' => $event->id, 'test_date' => $event->test_date, 'discipline' => $event->discipline->abbrev, 'test_type' => $test_type, 'knowledge' => $knowledge, 'knowledge_ns' => $knowledge_ns, 'oral' => $oral, 'oral_ns' => $oral_ns, 'skill' => $skill, 'skill_ns' => $skill_ns, 'adas' => $adas, 'special' => 0, 'specialDeduct' => 0]);
                    }
                    $knowledge = 0;
                    $knowledge_ns = 0;
                    $oral = 0;
                    $oral_ns = 0;
                    $skill = 0;
                    $skill_ns = 0;
                } elseif ($event->discipline->abbrev == "CMA") {
                    $knowledge = 0;
                    $knowledge_ns = 0;
                    $oral = 0;
                    $oral_ns = 0;
                    $skill = 0;
                    $skill_ns = 0;
                    $special = 0;
                    $specialDeduct = 0;
                    // No skill tests in CMA per Mandi
                    // No oral tests in CMA per Chad and Mandi
                    $test_type = $this->getTestType($event->is_paper, $event->is_regional);

                    foreach ($event->testattempts as $testattempt) {
                        // Any one of these conditions the testattempt will not be paid to the observer until they are resolved and changed
                        if ($testattempt->payable_status == 'paid' || $testattempt->status == 'assigned' || $testattempt->status == 'pending' || $testattempt->status == 'started' || $testattempt->status == 'unscored') {
                            continue;
                        }
                        if ($event->testattempts->count() < 3) {
                            $special++;
                            $specialDeduct += $event->testattempts->count();
                            $knowledge++;
                        } else {
                            if ($testattempt->status == 'noshow') {
                                $knowledge_ns++;
                            } else {
                                $knowledge++;
                            }
                        }
                    }

                    $knowAda = array();
                    $skillAda = array();
                    $bothAda = array();

                    foreach ($event->knowledgeStudents as $student) {
                        foreach ($student->adas as $ada) {
                            switch ($ada->test_type) {
                                case 'knowledge':
                                    array_push($knowAda, [$ada->pivot->student_id, $ada->id]);
                                    break;

                                case 'both':
                                    array_push($bothAda, [$ada->pivot->student_id, $ada->id]);
                                    break;
                                
                                default:
                                    # code...
                                    break;
                            }
                        }
                    }

                    foreach ($event->skillStudents as $student) {
                        foreach ($student->adas as $ada) {
                            switch ($ada->test_type) {
                                case 'skill':
                                    array_push($skillAda, [$ada->pivot->student_id, $ada->id]);
                                    break;
                                
                                case 'both':
                                    if (! in_array($ada->pivot->student_id, $bothAda)) {
                                        array_push($bothAda, [$ada->pivot->student_id, $ada->id]);
                                    }
                                    break;
                                
                                default:
                                    # code...
                                    break;
                            }
                        }
                    }

                    $adas = count($knowAda) + count($skillAda) + count($bothAda);

                    // Check there are actual values for these variables before adding to the cmaTest array.
                    // A zero count on the events array returned to the view is stopping display of oberservers 
                    // who have no events in the payable period being calculated
                    if ($knowledge > 0 || $knowledge_ns > 0 || $oral > 0 || $oral_ns > 0 || $skill > 0 || $skill_ns > 0) {
                        // Zero values added for due to need to combine $cmaTests with $cnaTests when sending to the veiw
                        array_push($cmaTests, ['event_id' => $event->id, 'test_date' => $event->test_date, 'discipline' => $event->discipline->abbrev, 'test_type' => $test_type, 'knowledge' => $knowledge, 'knowledge_ns' => $knowledge_ns, 'oral' => 0, 'oral_ns' => 0, 'skill' => 0, 'skill_ns' => 0, 'adas' => $adas, 'special' => $special, 'specialDeduct' => $specialDeduct]);
                    }
                    $knowledge = 0;
                    $knowledge_ns = 0;
                    $oral = 0;
                    $oral_ns = 0;
                    $skill = 0;
                    $skill_ns = 0;
                    $special = 0;
                    $specialDeduct = 0;
                } else {
                    // Log to file in the event a discipline abbreviation is changed. If either CNA or CMA aren't found this accounting 
                    // has no details in how to process the record
                    \Log::info("Discipline abbreviation for event " . $event->id . " isn't either CNA or CMA. The accounting package has no clue how to process this payable.");
                    continue;
                }
            }

            $totalKnowledgeCnt = 0;
            $totalOralCnt = 0;
            $totalSkillCnt = 0;
            $totalAdaCnt = 0;

            $cmaTotalKnowledgeCnt = 0;
            $cmaTotalSpecialCnt = 0;
            $cmaTotalAdaCnt = 0;

            foreach ($cnaTests as $cnatest) {
                $totalKnowledgeCnt += $cnatest['knowledge'];
                $totalOralCnt += $cnatest['oral'] + $cnatest['oral_ns'];
                $totalSkillCnt += $cnatest['skill'];
                $totalAdaCnt += $cnatest['adas'];
            }

            foreach ($cmaTests as $cmatest) {
                $cmaTotalKnowledgeCnt += $cmatest['knowledge'];
                $cmaTotalSpecialCnt += $cmatest['special'];
                $cmaTotalAdaCnt += $cmatest['adas'];
            }

            $cnaPayrates = PayableRate::find($observer->payable_rate);

            $cmaPayrates = PayableRate::find(4);    // Hard coded at this time until adding CMA rate field to observers table

            $totalCnaKnowledgeDue = $totalKnowledgeCnt * $cnaPayrates->knowledge_rate;
            $totalCnaOralDue    = $totalOralCnt * $cnaPayrates->oral_rate;
            $totalCnaSkillDue    = $totalSkillCnt * $cnaPayrates->skill_rate;
            $totalCmaKnowledgeDue = ($cmaTotalKnowledgeCnt - $cmaTotalSpecialCnt) * $cmaPayrates->knowledge_rate;
            $totalCmaSpecialDue    = $cmaTotalSpecialCnt * $cmaPayrates->special_rate;

            $totalKnowledgeDue = $totalCnaKnowledgeDue + $totalCmaKnowledgeDue + $totalCmaSpecialDue;

            $totalAdaDue = ($totalAdaCnt * $cnaPayrates->ada_rate) + ($cmaTotalAdaCnt * $cmaPayrates->ada_rate);

            $totalDue = $totalKnowledgeDue + $totalCnaOralDue + $totalCnaSkillDue + $totalAdaDue;

            $eventArr = array();

            foreach ($cnaTests as $test) {
                array_push($eventArr, $test);
            }
            foreach ($cmaTests as $test) {
                array_push($eventArr, $test);
            }

            array_push($payables, [
                'observerId' => $observer->id,
                'first' => $observer->first,
                'last' => $observer->last,
                'address' => $observer->address,
                'city' => $observer->city,
                'state' => $observer->state,
                'zip' => $observer->zip,
                'totalKnowledgeDue' => $totalKnowledgeDue,
                'totalSkillDue' => $totalCnaSkillDue,
                'totalOralDue' => $totalCnaOralDue,
                'totalAdaDue' => $totalAdaDue,
                'totalDue' => $totalDue,
                'events' => $eventArr
            ]);

            $knowledge = 0;
            $knowledge_ns = 0;
            $oral = 0;
            $oral_ns = 0;
            $skill = 0;
            $skill_ns = 0;

            $special = 0;
            $specialDeduct = 0;

            $cnaTests = array();
            $cmaTests = array();

            $lastObserverId = $event->observer_id;
            $observer = Observer::find($lastObserverId);

            return View::make('core::accounting.billing')->with([
                'payables' => $payables
            ]);
        } else {
            // Return payables = null when no tests are found to notify user no payables are outstanding
            return View::make('core::accounting.billing')->with([
                'payables' => null
            ]);
        }
    }

    public function processAllObservers()
    {
        $oral = 0;
        $oral_ns = 0;
        $know = 0;
        $know_ns = 0;
        $skill = 0;
        $skill_ns = 0;
        $special = 0;
        $special_ns = 0;
        $specialCnt = 0;

        $totalOral = 0;
        $totalKnowledge = 0;
        $totalSkill = 0;
        $totalSpecial = 0;

        $payables = array();
        $payableArr = array();
        
        $knowledgeAdas = array();
        $skillAdas = array();

        $events = Testevent::with([
            'testattempts',
            'skillattempts'
        ])->whereHas('testattempts', function ($query) {
            $query->where('payable_status', '!=', 'paid');
        })->orWhereHas('skillattempts', function ($query) {
            $query->where('payable_status', '!=', 'paid');
        })->where('test_date', '<=', date("Y-m-d"))->orderBy('observer_id')->get();

        $oldObserverId = $events->first()->observer_id;
        $observer = \Observer::find($events->first()->observer_id);

        foreach ($events as $event) {
            $test_type = $this->getTestType($event->is_paper, $event->is_regional);

            if ($event->observer_id != $oldObserverId) {
                $payRates = PayableRate::find($observer->payable_rate);
                $specialPayRate = PayableRate::find(4);

                foreach ($payables as $payable) {
                    $totalOral += $payable['oral'] + $payable['oral_ns'];
                    $totalKnowledge += $payable['know'] + $payable['know_ns'];
                    $totalSkill += $payable['skill'] + $payable['skill_ns'];
                    $totalSpecial += $payable['special'] + $payable['special_ns'];
                }

                $totalOralDue = $totalOral * $payRates->oral_rate;
                $totalKnowledgeDue = $totalKnowledge * $payRates->knowledge_rate;
                $totalSkillDue = $totalSkill * $payRates->skill_rate;
                $totalSpecialDue = $totalSpecial * $specialPayRate->knowledge_rate;

                $totalDue = $totalOralDue + $totalKnowledgeDue + $totalSkillDue + $totalSpecialDue;

                usort($payables, function ($a, $b) {
                    return (strtotime($a['date']) < strtotime($b['date'])) ? -1 : 1;
                });

                array_push($payableArr, [
                    'payables' => $payables,
                    'totalOralDue' => $totalOralDue,
                    'totalKnowledgeDue' => $totalKnowledgeDue,
                    'totalSkillDue' => $totalSkillDue,
                    'numSpecial' => $totalSpecial,
                    'specialRate' => $specialPayRate->knowledge_rate,
                    'totalSpecialDue' => $totalSpecialDue,
                    'totalAdaDue' => 0,
                    'totalPayable' => $totalDue,
                    'first' => $observer->first,
                    'last' => $observer->last,
                    'address' => $observer->address,
                    'city' => $observer->city,
                    'state' => $observer->state,
                    'zip' => $observer->zip
                ]);

                $oldObserverId = $event->observer_id;
                $observer = \Observer::find($event->observer_id);

                $payables = array();

                $totalOralDue = 0;
                $totalKnowledgeDue = 0;
                $totalSkillDue = 0;
                $totalSpecialDue = 0;

                $totalDue = 0;

                $oral = 0;
                $oral_ns = 0;
                $know = 0;
                $know_ns = 0;
                $skill = 0;
                $skill_ns = 0;
                $special = 0;
                $special_ns = 0;

                $totalOral = 0;
                $totalKnowledge = 0;
                $totalSkill = 0;
                $totalSpecial = 0;
            }
            if ($event->testattempts->count()) {
                if ($event->testattempts->count() < 3) {
                    foreach ($event->testattempts as $ka) {
                        if ($ka->status == "noshow") {
                            $special_ns++;
                        } else {
                            $special++;
                        }
                    }
                    $specialCnt++;
                } else {
                    foreach ($event->testattempts as $ka) {
                        if ($ka->status == "noshow") {
                            if ($ka->is_oral) {
                                $oral_ns++;
                            }
                            $know_ns++;
                        } else {
                            if ($ka->is_oral) {
                                $oral++;
                            }
                            $know++;
                        }
                    }
                }
            }
            if ($event->skillattempts->count()) {
                foreach ($event->skillattempts as $sa) {
                    if ($sa->status == "noshow") {
                        $skill_ns++;
                    } else {
                        $skill++;
                    }
                }
            }

            if ($event->testattempts->count() > 0 || $event->skillattempts->count() > 0) {
                array_push($payables, [
                    'date' => $event->test_date,
                    'id' => $event->id,
                    'test_type' => $test_type,
                    'oral' => $oral,
                    'oral_ns' => $oral_ns,
                    'know' => $know,
                    'know_ns' => $know_ns,
                    'skill' => $skill,
                    'skill_ns' => $skill_ns,
                    'special' => $special,
                    'special_ns' => $special_ns,
                    'specialCnt' => $specialCnt
                ]);
            }

            $oral = 0;
            $oral_ns = 0;
            $know = 0;
            $know_ns = 0;
            $skill = 0;
            $skill_ns = 0;
            $special = 0;
            $special_ns = 0;
            $specialCnt = 0;
        }

        $payRates = PayableRate::find($observer->payable_rate);
        $specialPayRate = PayableRate::find(4);

        foreach ($payables as $payable) {
            $totalOral += $payable['oral'] + $payable['oral_ns'];
            $totalKnowledge += $payable['know'] + $payable['know_ns'];
            $totalSkill += $payable['skill'] + $payable['skill_ns'];
            $totalSpecial += $payable['specialCnt'];
        }

        $totalOralDue = $totalOral * $payRates->oral_rate;
        $totalKnowledgeDue = $totalKnowledge * $payRates->knowledge_rate;
        $totalSkillDue = $totalSkill * $payRates->skill_rate;
        $totalSpecialDue = $totalSpecial * $specialPayRate->knowledge_rate;

        $totalDue = $totalOralDue + $totalKnowledgeDue + $totalSkillDue + $totalSpecialDue;

        usort($payables, function ($a, $b) {
            return (strtotime($a['date']) < strtotime($b['date'])) ? -1 : 1;
        });

        array_push($payableArr, [
            'payables' => $payables,
            'totalOralDue' => $totalOralDue,
            'totalKnowledgeDue' => $totalKnowledgeDue,
            'totalSkillDue' => $totalSkillDue,
            'numSpecial' => $totalSpecial,
            'specialRate' => $specialPayRate->knowledge_rate,
            'totalSpecialDue' => $totalSpecialDue,
            'totalAdaDue' => 0,
            'totalPayable' => $totalDue,
            'first' => $observer->first,
            'last' => $observer->last,
            'address' => $observer->address,
            'city' => $observer->city,
            'state' => $observer->state,
            'zip' => $observer->zip
        ]);

        return View::make('core::accounting.payallobservers')->with([
            'payableArr' => $payableArr
        ]);
    }

    public function payObserver($id)
    {
        $oral = 0;
        $oral_ns = 0;
        $know = 0;
        $know_ns = 0;
        $skill = 0;
        $skill_ns = 0;
        $special = 0;
        $special_ns = 0;
        $specialCnt = 0;

        $totalOral = 0;
        $totalKnowledge = 0;
        $totalSkill = 0;
        $totalSpecial = 0;

        $payables = array();
        
        $knowledgeAdas = array();
        $skillAdas = array();

        $events = Testevent::with([
            'discipline',
            'testattempts',
            'testattempts.testform',
            'knowledgeStudents',
            'knowledgeStudents.adas',
            'skillattempts',
            'skillStudents',
            'skillStudents.adas'
        ])->where('test_date', '<=', date("Y-m-d"))->where('test_date', '>=', '2016-06-01')->where('observer_id', $id)->orderBy('test_date')->get();

        if ($events->count()) {
            $observer = \Observer::find($id);

            foreach ($events as $event) {
                $test_type = $this->getTestType($event->is_paper, $event->is_regional);

                if ($event->testattempts->count()) {
                    if ($event->testattempts->count() < 3) {
                        foreach ($event->testattempts as $ka) {
                            if ($ka->status == "noshow") {
                                $special_ns++;
                                if ($event->discipline->abbrev == "CNA") {
                                    if ($ka->is_oral && $ka->testform->getOriginal()) {
                                        $oral_ns++;
                                    }
                                }
                            } else {
                                $special++;
                                if ($event->discipline->abbrev == "CNA") {
                                    if ($ka->is_oral && $ka->testform->getOriginal()) {
                                        $oral++;
                                    }
                                }
                            }
                        }
                        $specialCnt++;
                    } else {
                        foreach ($event->testattempts as $ka) {
                            if ($ka->status == "noshow") {
                                if ($event->discipline->abbrev == "CNA") {
                                    if ($ka->is_oral && $ka->testform->getOriginal()) {
                                        $oral_ns++;
                                    }
                                }
                                $know_ns++;
                            } else {
                                if ($event->discipline->abbrev == "CNA") {
                                    if ($ka->is_oral && $ka->testform->getOriginal()) {
                                        $oral++;
                                    }
                                }
                                $know++;
                            }
                        }
                    }
                }
                if ($event->skillattempts->count()) {
                    foreach ($event->skillattempts as $sa) {
                        if ($sa->status == "noshow") {
                            $skill_ns++;
                        } else {
                            $skill++;
                        }
                    }
                }

                if ($event->testattempts->count() > 0 || $event->skillattempts->count() > 0) {
                    array_push($payables, [
                        'date' => $event->test_date,
                        'id' => $event->id,
                        'test_type' => $test_type,
                        'oral' => $oral,
                        'oral_ns' => $oral_ns,
                        'know' => $know,
                        'know_ns' => $know_ns,
                        'skill' => $skill,
                        'skill_ns' => $skill_ns,
                        'special' => $special,
                        'special_ns' => $special_ns,
                        'specialCnt' => $specialCnt
                    ]);
                }

                // $adaArr = array_diff($knowledgeAdas, $skillAdas)

                // dd($adaArr);

                $oral = 0;
                $oral_ns = 0;
                $know = 0;
                $know_ns = 0;
                $skill = 0;
                $skill_ns = 0;
                $special = 0;
                $special_ns = 0;
                $specialCnt = 0;
            }

            $payRates = PayableRate::find($observer->payable_rate);
            $specialPayRate = PayableRate::find(4);

            foreach ($payables as $payable) {
                $totalOral += $payable['oral'] + $payable['oral_ns'];
                $totalKnowledge += $payable['know'] + $payable['know_ns'];
                $totalSkill += $payable['skill'] + $payable['skill_ns'];
                $totalSpecial += $payable['specialCnt'];
            }

            $totalOralDue = $totalOral * $payRates->oral_rate;
            $totalKnowledgeDue = $totalKnowledge * $payRates->knowledge_rate;
            $totalSkillDue = $totalSkill * $payRates->skill_rate;
            $totalSpecialDue = $totalSpecial * $specialPayRate->knowledge_rate;

            $totalDue = $totalOralDue + $totalKnowledgeDue + $totalSkillDue + $totalSpecialDue;

            return View::make('core::accounting.payable_payobserver')->with([
                'payables' => $payables,
                'totalOralDue' => $totalOralDue,
                'totalKnowledgeDue' => $totalKnowledgeDue,
                'totalSkillDue' => $totalSkillDue,
                'numSpecial' => $totalSpecial,
                'specialRate' => $specialPayRate->knowledge_rate,
                'totalSpecialDue' => $totalSpecialDue,
                'totalAdaDue' => 0,
                'totalPayable' => $totalDue,
                'first' => $observer->first,
                'last' => $observer->last,
                'address' => $observer->address,
                'city' => $observer->city,
                'state' => $observer->state,
                'zip' => $observer->zip
            ]);
        } else {
            return Redirect::route('accounting.billing');
        }
    }

    public function processObserverPayment()
    {
        $paylist = Session::get('payable');

        //dd($paylist);

        foreach ($paylist as $pay) {
            $events = Testevent::with([
                'testattempts',
                'skillattempts'
            ])->where('id', $pay['id'])->get();

            foreach ($events as $event) {
                //dd($event);
                foreach ($event->testattempts as $attempt) {
                    $attempt->payable_status = 'paid';
                    $attempt->save();
                }
                foreach ($event->skillattempts as $attempt) {
                    $attempt->payable_status = 'paid';
                    $attempt->save();
                }
            }
        }
        Session::forget('payable');
        Session::flash('success', 'Observer has been paid successfull.');
        return Redirect::route('accounting.billing');
    }

    public function processAllObserverPayments()
    {
        $paylist = Session::get('payable');

        foreach ($paylist as $observer) {
            foreach ($observer['payables'] as $p) {
                $events = Testevent::with([
                    'testattempts',
                    'skillattempts'
                ])->where('id', $p['id'])->get();

                foreach ($events as $e) {
                    foreach ($e->testattempts as $attempt) {
                        $attempt->payable_status = 'paid';
                        $attempt->save();
                    }
                    foreach ($e->skillattempts as $attempt) {
                        $attempt->payable_status = 'paid';
                        $attempt->save();
                    }
                }
            }
        }

        Session::forget('payable');
        Session::flash('success', 'All Observers have been paid successfully.');
        return Redirect::route('accounting.billing');
    }

    public function getTestType($is_paper, $is_regional)
    {
        if ($is_paper && $is_regional) {            // Paper Test Regional [R]
            return 'R';
        }
        if ($is_paper && ! $is_regional) {        // Paper Test Flexible [X]
            return 'X';
        }
        if (! $is_paper && ! $is_regional) {        // Web Test Flexible [W]
            return 'W';
        }
        if (! $is_paper && $is_regional) {        // Web Test Regional [Y]
            return 'Y';
        }
    }

    public function pushPayableList($first, $last, $observerId, $test_date, $eventID)
    {
        array_push($this->payablelist, [
            'first' => $first,
            'last' => $last,
            'observer_id' => $observerId,
            'eventId' => $eventID,
            'test_date' => $test_date,
            'totalKnowledgeR' => $this->totalKnowledgeR,
            'totalKnowledgeR_NS' => $this->totalKnowledgeR_NS,
            'totalOralR' => $this->totalOralR,
            'totalOralR_NS' => $this->totalOralR_NS,
            'totalSkillR' => $this->totalSkillR,
            'totalSkillR_NS' => $this->totalSkillR_NS,
            'totalKnowledgeX' => $this->totalKnowledgeX,
            'totalKnowledgeX_NS' => $this->totalKnowledgeX_NS,
            'totalOralX' => $this->totalOralX,
            'totalOralX_NS' => $this->totalOralX_NS,
            'totalSkillX' => $this->totalSkillX,
            'totalSkillX_NS' => $this->totalSkillX_NS,
            'totalKnowledgeW' => $this->totalKnowledgeW,
            'totalKnowledgeW_NS' => $this->totalKnowledgeW_NS,
            'totalOralW' => $this->totalOralW,
            'totalOralW_NS' => $this->totalOralW_NS,
            'totalSkillW' => $this->totalSkillW,
            'totalSkillW_NS' => $this->totalSkillW_NS,
            'totalKnowledgeY' => $this->totalKnowledgeY,
            'totalKnowledgeY_NS' => $this->totalKnowledgeY_NS,
            'totalOralY' => $this->totalOralY,
            'totalOralY_NS' => $this->totalOralY_NS,
            'totalSkillY' => $this->totalSkillY,
            'totalSkillY_NS' => $this->totalSkillY_NS,
            'totalKnowledgeR_Special_NS' => $this->totalKnowledgeR_Special_NS,
            'totalKnowledgeR_Special' => $this->totalKnowledgeR_Special,
            'totalKnowledgeX_Special_NS' => $this->totalKnowledgeX_Special_NS,
            'totalKnowledgeX_Special' => $this->totalKnowledgeX_Special,
            'totalKnowledgeW_Special_NS' => $this->totalKnowledgeW_Special_NS,
            'totalKnowledgeW_Special' => $this->totalKnowledgeW_Special,
            'totalKnowledgeY_Special_NS' => $this->totalKnowledgeY_Special_NS,
            'totalKnowledgeY_Special' => $this->totalKnowledgeY_Special
        ]);
    }

    public function setCount($event, $payable, $testtype)
    {
        switch ($this->getTestType($event->is_paper, $event->is_regional)) {
            case 'R':
                if ($payable->status == "noshow") {
                    if ($testtype == "S") {
                        $this->totalSkillR_NS++;
                        $this->totalDueSkill++;
                    } else {
                        if ($payable->is_oral) {
                            $this->totalOralR_NS++;
                            $this->totalDueOral++;

                            $this->totalKnowledgeR_NS++;
                            $this->totalDueKnowledge++;
                        } else {
                            if ($this->getNumTests($payable) < 3) {
                                $this->totalKnowledgeR_Special_NS++;
                            } else {
                                $this->totalKnowledgeR_NS++;
                                $this->totalDueKnowledge++;
                            }
                        }
                    }
                } else {
                    if ($testtype == "S") {
                        $this->totalSkillR++;
                        $this->totalDueSkill++;
                    } else {
                        if ($payable->is_oral) {
                            $this->totalOralR++;
                            $this->totalDueOral++;

                            $this->totalKnowledgeR++;
                            $this->totalDueKnowledge++;
                        } else {
                            if ($this->getNumTests($payable) < 3) {
                                $this->totalKnowledgeR_Special++;
                            } else {
                                $this->totalKnowledgeR++;
                                $this->totalDueKnowledge++;
                            }
                        }
                    }
                }
                break;
            case 'X':
                if ($payable->status == "noshow") {
                    if ($testtype == "S") {
                        $this->totalSkillX_NS++;
                        $this->totalDueSkill++;
                    } else {
                        if ($payable->is_oral) {
                            $this->totalOralX_NS++;
                            $this->totalDueOral++;

                            $this->totalKnowledgeX_NS++;
                            $this->totalDueKnowledge++;
                        } else {
                            if ($this->getNumTests($payable) < 3) {
                                $this->totalKnowledgeX_Special_NS++;
                            } else {
                                $this->totalKnowledgeX_NS++;
                                $this->totalDueKnowledge++;
                            }
                        }
                    }
                } else {
                    if ($testtype == "S") {
                        $this->totalSkillX++;
                        $this->totalDueSkill++;
                    } else {
                        if ($payable->is_oral) {
                            $this->totalOralX++;
                            $this->totalDueOral++;

                            $this->totalKnowledgeX++;
                            $this->totalDueKnowledge++;
                        } else {
                            if ($this->getNumTests($payable) < 3) {
                                $this->totalKnowledgeX_Special++;
                            } else {
                                $this->totalKnowledgeX++;
                                $this->totalDueKnowledge++;
                            }
                        }
                    }
                }
                break;
            case 'W':
                if ($payable->status == "noshow") {
                    if ($testtype == "S") {
                        $this->totalSkillW_NS++;
                        $this->totalDueSkill++;
                    } else {
                        if ($payable->is_oral) {
                            $this->totalOralW_NS++;
                            $this->totalDueOral++;

                            $this->totalKnowledgeW_NS++;
                            $this->totalDueKnowledge++;
                        } else {
                            if ($this->getNumTests($payable) < 3) {
                                $this->totalKnowledgeW_Special_NS++;
                            } else {
                                $this->totalKnowledgeW_NS++;
                                $this->totalDueKnowledge++;
                            }
                        }
                    }
                } else {
                    if ($testtype == "S") {
                        $this->totalSkillW++;
                        $this->totalDueSkill++;
                    } else {
                        if ($payable->is_oral) {
                            $this->totalOralW++;
                            $this->totalDueOral++;

                            $this->totalKnowledgeW++;
                            $this->totalDueKnowledge++;
                        } else {
                            if ($this->getNumTests($payable) < 3) {
                                $this->totalKnowledgeW_Special++;
                            } else {
                                $this->totalKnowledgeW++;
                                $this->totalDueKnowledge++;
                            }
                        }
                    }
                }
                break;
            case 'Y':
                if ($payable->status == "noshow") {
                    if ($testtype == "S") {
                        $this->totalSkillY_NS++;
                        $this->totalDueSkill++;
                    } else {
                        if ($payable->is_oral) {
                            $this->totalOralY_NS++;
                            $this->totalDueOral++;

                            $this->$totalKnowledgeY_NS++;
                            $this->totalDueKnowledge++;
                        } else {
                            if ($this->getNumTests($payable) < 3) {
                                $this->totalKnowledgeY_Special_NS++;
                            } else {
                                $this->$totalKnowledgeY_NS++;
                                $this->totalDueKnowledge++;
                            }
                        }
                    }
                } else {
                    if ($testtype == "S") {
                        $this->totalSkillY++;
                        $this->totalDueSkill++;
                    } else {
                        if ($payable->is_oral) {
                            $this->totalOralY++;
                            $this->totalDueOral++;

                            $this->totalKnowledgeY++;
                            $this->totalDueKnowledge++;
                        } else {
                            if ($this->getNumTests($payable) < 3) {
                                $this->totalKnowledgeY_Special++;
                            } else {
                                $this->totalKnowledgeY++;
                                $this->totalDueKnowledge++;
                            }
                        }
                    }
                }
                break;
            default:
        }
    }

    public function getNumTests($payable)
    {
        return \DB::table('testattempts')->where('testevent_id', $payable->testevent_id)->count();
    }

    public function resetTestCounts()
    {
        $this->totalKnowledgeR = 0;
        $this->totalKnowledgeR_NS = 0;
        $this->totalKnowledgeX = 0;
        $this->totalKnowledgeX_NS = 0;
        $this->totalKnowledgeW = 0;
        $this->totalKnowledgeW_NS = 0;
        $this->totalKnowledgeY = 0;
        $this->totalKnowledgeY_NS = 0;

        $this->totalKnowledgeR_Special_NS = 0;
        $this->totalKnowledgeR_Special = 0;
        $this->totalKnowledgeX_Special_NS = 0;
        $this->totalKnowledgeX_Special = 0;
        $this->totalKnowledgeW_Special_NS = 0;
        $this->totalKnowledgeW_Special = 0;
        $this->totalKnowledgeY_Special_NS = 0;
        $this->totalKnowledgeY_Special = 0;

        $this->totalOralR = 0;
        $this->totalOralR_NS = 0;
        $this->totalOralX = 0;
        $this->totalOralX_NS = 0;
        $this->totalOralW = 0;
        $this->totalOralW_NS = 0;
        $this->totalOralY = 0;
        $this->totalOralY_NS = 0;

        $this->totalSkillR = 0;
        $this->totalSkillR_NS = 0;
        $this->totalSkillX = 0;
        $this->totalSkillX_NS = 0;
        $this->totalSkillW = 0;
        $this->totalSkillW_NS = 0;
        $this->totalSkillY = 0;
        $this->totalSkillY_NS = 0;
    }
}
