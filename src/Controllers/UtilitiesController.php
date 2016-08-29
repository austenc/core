<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use Config;
use Redirect;
use Flash;
use DB;
use Lang;
use Request;
use Illuminate\Database\Eloquent\Collection as Collection;
use \Instructor;
use \Observer;
use \Proctor;
use \Actor;
use \User;
use \Discipline;
use \Skillexam;
use \Exam;
use \Testattempt;
use \Skillattempt;
use \Pendingscore;

class UtilitiesController extends BaseController
{

    protected $endedStatus = [
        'passed'   => 0,
        'failed'   => 0,
        'unscored' => 0,
        'noshow'   => 0
    ];
    protected $disciplines, $exams, $skills;

    public function __construct()
    {
        $this->disciplines = Discipline::with('exams', 'skills')->get()->keyBy('id');
        $this->exams       = Exam::all()->keyBy('id');
        $this->skills      = Skillexam::all()->keyBy('id');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
    }

    /**
     * Listing of # tests taken between dates
     */
    public function testHistory()
    {
        $history = null;
        $start   = Input::get('start');
        $end     = Input::get('end');

        if (Request::isMethod('post')) {
            $start = ! empty($start) ? date('m/d/Y', strtotime($start)) : '';
            $end   = ! empty($end) ? date('m/d/Y', strtotime($end)) : date('m/d/Y', strtotime("+ 1 day"));
        
            // start date required
            if (! empty($start)) {
                $history = $this->findTestHistory($start, $end);
            } else {
                Flash::warning('<strong>Start</strong> date is required to generate history.');
            }
        }

        return View::make('core::utilities.test_history')->with([
            'start'         => $start,
            'end'           => $end,
            'history'       => $history,
            'endTestStatus' => array_keys($this->endedStatus),
            'disciplines'   => $this->disciplines,
            'exams'         => $this->exams,
            'skills'        => $this->skills
        ]);
    }

    /**
     * Grabs count of test history knowledge & skill for ALL disciplines
     * and only specific "ended" type status' 
     *  (passed,failed,unscored,noshow)
     */
    private function findTestHistory($start, $end)
    {
        // start is required
        if (empty($start)) {
            return null;
        }
        if (empty($end)) {
            $end = date('m/d/Y', strtotime("+ 1 day"));
        }

        // format dates
        $start = date('Y-m-d', strtotime($start));
        $end   = date('Y-m-d', strtotime($end));

        $pendingScores = [];
        $history       = [];
        $statusOnly    = array_keys($this->endedStatus);

        // get all knowledge tests within date range

        $allKnowledge = Testattempt::whereHas('testevent', function ($e) use ($start, $end) {
            $e->where('test_date', '>=', $start)->where('test_date', '<=', $end);
        })->whereIn('status', $statusOnly)->get();

        // get all skill tests within date range
        $allSkill = Skillattempt::whereHas('testevent', function ($e) use ($start, $end) {
            $e->where('test_date', '>=', $start)->where('test_date', '<=', $end);
        })->whereIn('status', $statusOnly)->get();
        
        // get all pending scores
        //  (exclude these from counts)
        $allPendingScores = Pendingscore::all();
        
        $pendingScores['knowledge'] = $allPendingScores->filter(function ($score) {
            return $score->type == 'Knowledge';
        });
        
        $pendingScores['skill'] = $allPendingScores->filter(function ($score) {
            return $score->type == 'Skill';
        });

        // per Discipline
        foreach ($this->disciplines as $disciplineId => $discipline) {
            // init knowledge
            if (! $discipline->exams->isEmpty()) {
                $history[$disciplineId]['knowledge'] = [];
            }

            foreach ($discipline->exams as $exam) {
                // init counts for this knowledge exam
                $history[$disciplineId]['knowledge'][$exam->id] = $this->endedStatus;

                // get all pending scores for this knowledge exam
                $currPending = $pendingScores['knowledge']->filter(function ($score) use ($exam) {
                    return $score->scoreable->exam_id == $exam->id;
                });
                
                // init pending score count
                $history[$disciplineId]['knowledge'][$exam->id]['pending_scores'] = $currPending->count();
                
                // initialize totals
                if (! array_key_exists('total', $history[$disciplineId]['knowledge'][$exam->id])) {
                    $history[$disciplineId]['knowledge'][$exam->id]['total'] = 0;
                }

                // per Status
                foreach ($statusOnly as $status) {
                    $currCount = $allKnowledge->filter(function ($k) use ($exam, $status, $currPending) {
                        return $k->exam_id == $exam->id && $k->status == $status && ! in_array($k->id, $currPending->lists('scoreable_id')->all());
                    })->count();
                    
                    $history[$disciplineId]['knowledge'][$exam->id][$status] = $currCount;
                    $history[$disciplineId]['knowledge'][$exam->id]['total'] += $currCount;
                }

                // orals
                $history[$disciplineId]['knowledge'][$exam->id]['oral'] = $allKnowledge->filter(function ($k) use ($exam, $status) {
                    return $k->exam_id == $exam->id && $k->is_oral == 1;
                })->count();
            }

            // init skills
            if (! $discipline->skills->isEmpty()) {
                $history[$disciplineId]['skill'] = [];
            }

            foreach ($discipline->skills as $skill) {
                $history[$disciplineId]['skill'][$skill->id] = $this->endedStatus;

                // get all pending scores for this knowledge exam
                $currPending = $pendingScores['skill']->filter(function ($score) use ($skill) {
                    return $score->scoreable->skillexam_id == $skill->id;
                });

                // init pending score count
                $history[$disciplineId]['skill'][$skill->id]['pending_scores'] = $currPending->count();

                // initialize totals
                if (! array_key_exists('total', $history[$disciplineId]['skill'][$skill->id])) {
                    $history[$disciplineId]['skill'][$skill->id]['total'] = 0;
                }

                // per Status
                foreach ($statusOnly as $status) {
                    $currCount = $allSkill->filter(function ($s) use ($skill, $status, $currPending) {
                        return $s->skillexam_id == $skill->id && $s->status == $status && ! in_array($s->id, $currPending->lists('scoreable_id')->all());
                    })->count();

                    $history[$disciplineId]['skill'][$skill->id][$status] = $currCount;
                    $history[$disciplineId]['skill'][$skill->id]['total'] += $currCount;
                }
            }
        }

        return $history;
    }

    /**
     * Reassign user id 
     *   Effectively merges 2 user accounts into one (i.e. double agents; instructor/observer) 
     */
    public function doMergeUsers()
    {
        // single user id from button value
        $userId        = Input::get('user');
        $personId      = Input::get('person_id.'.$userId);
        $userRole      = Input::get('user_role.'.$userId);
        $consumeUserId = Input::get('consume_user_id.'.$userId);

        if (empty($userId) || empty($consumeUserId) || empty($personId)) {
            Flash::danger('Merge Failed. Invalid User data');
            return Redirect::back();
        }

        // find user record
        //  (this record will be staying)
        $mainUser = User::find($userId);
        if (is_null($mainUser)) {
            Flash::danger('Merge Failed. Unknown main User ID#'.$userId);
            return Redirect::back();
        }

        // get person record
        $currClass = ucfirst($userRole);
        $plural    = str_plural($userRole);
        $person    = $currClass::find($personId);
        if (is_null($person)) {
            Flash::danger('Merge Failed. Unknown Person ID#'.$personId);
            return Redirect::back();
        }

        // find consume user record
        //  (this record will be deleted)
        $consumeUser = User::find($consumeUserId);
        if (is_null($consumeUser)) {
            Flash::danger('Merge Failed. Unknown merge User ID#'.$consumeUserId);
            return Redirect::back();
        }

        // determine any missing roles
        $missingRoles = array_diff($consumeUser->roles->lists('name')->all(), $mainUser->roles->lists('name')->all());
        
        // add missing roles
        if (! empty($missingRoles)) {
            $mainUser->addRoles($missingRoles);
        }
        
        // remap user id
        DB::table('observers')->where('user_id', $consumeUserId)->update(['user_id' => $userId]);
        DB::table('proctors')->where('user_id', $consumeUserId)->update(['user_id' => $userId]);
        DB::table('actors')->where('user_id', $consumeUserId)->update(['user_id' => $userId]);
        DB::table('instructors')->where('user_id', $consumeUserId)->update(['user_id' => $userId]);

        // add logging
        \Log::info($consumeUser->toJson());
        \Log::info('Merge User; remapping all UserID#'.$consumeUserId.' to correct UserID#'.$userId);
        
        // delete orphan user record
        DB::table('users')->where('id', $consumeUserId)->delete();

        $link = link_to_route($plural.'.edit', $person->fullname, $person->id);
        Flash::success('Successfully consumed and remapped User#'.$consumeUserId.' to '.Lang::choice('core::terms.'.$userRole, 1).' '.$link);
        return Redirect::route('utilities.users.merge');
    }

    /**
     * Sets up and returns match collection for user type passed
     */
    private function findMatches($type, $items, $numMatchChars = 5)
    {
        $matched  = [];

        $allTypes    = ['instructor', 'actor', 'observer', 'proctor'];
        $lookupTypes = array_diff($allTypes, [$type]);

        foreach ($items as $item) {
            foreach ($lookupTypes as $currType) {
                // setup partial names for matching
                $partialFirst = substr($item->first, 0, $numMatchChars).'%';
                $partialLast  = substr($item->last, 0, $numMatchChars).'%';

                $currClass   = ucfirst($currType);
                $currMatches = $currClass::where('first', 'LIKE', $partialFirst)
                                        ->where('last', 'LIKE', $partialLast)
                                        ->where('user_id', '!=', $item->user_id)
                                        ->get();

                foreach ($currMatches as $match) {
                    $match->descript_name = $match->fullname.' ('.$currClass.'#'.$match->id.')';

                    if (! array_key_exists($item->id, $matched)) {
                        $matched[$item->id] = new Collection;
                    }

                    $matched[$item->id]->push($match);
                }
            }
        }

        return $matched;
    }

    /**
     * Lists all user types that have another type with a matching name 
     */
    public function mergeUsers()
    {
        // num characters used to grab substring to check for possible name matches
        $numMatchChars = 5;

        $allTypes = ['instructor', 'observer', 'actor', 'proctor'];

        $lookup = [];
        $final  = [];

        $lookup['instructor'] = Instructor::all()->keyBy('id');
        $lookup['observer']   = Observer::all()->keyBy('id');
        $lookup['proctor']    = Proctor::all()->keyBy('id');
        $lookup['actor']      = Actor::all()->keyBy('id');

        foreach ($allTypes as $type) {
            $className = ucfirst($type);

            $lookup[$type] = $className::all()->keyBy('id');

            // get matches
            $final[$type]  = $this->findMatches($type, $lookup[$type], $numMatchChars);
        }

        return View::make('core::utilities.merge_users')->with([
            'numCharsMatched' => $numMatchChars,
            'allTypes'        => $allTypes,
            'matched'         => $final,
            'lookup'          => $lookup
        ]);
    }
}
