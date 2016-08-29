<?php namespace Hdmaster\Core\Models\Proctor;

use Config;
use Input;
use Formatter;
use \StatusTrait;
use \LicenseTrait;
use \User;
use \Person;
use \Testteam;
use \Role;
use \Testevent;
use \Facility;
use \Session;
use \Auth;
use \DB;
use \Discipline;
use \Sorter;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proctor extends \Eloquent
{
    use SoftDeletes;
    use Person;
    use Testteam;
    use StatusTrait;
    use LicenseTrait;

    protected $dates      = ['deleted_at'];
    protected $morphClass = 'Proctor';
    
    protected $fillable = [
        'user_id',
        'first',
        'middle',
        'last',
        'email',
        'birthdate',
        'gender',
        'address',
        'city',
        'state',
        'zip',
        'phone',
        'alt_phone',
        'fax',
        'comments',
        'password',
        'password_confirmation',
        'status'
    ];

    protected $rules = [
        'first'     => 'required',
        'last'      => 'required',
        'birthdate' => 'required|date',
        'password'  => 'min:8|confirmed',
        'password_confirmation' => 'min:8'
    ];

    public $errors;

    /**
     * Constructor
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        
        // Have to define these here since we can't initialize dynamic properties in class definition
        $this->rules['email']     = 'required|email|unique:'.'users';
        $this->rules['username']  = 'unique:'.'users';
    }


    public function handleSearch()
    {
        // sidebar filter
        // active|inactive|all
        if (Input::get('s')) {
            Session::put('proctors.search.filter', Input::get('s'));
        } elseif (! Session::has('proctors.search.filter')) {
            Session::put('proctors.search.filter', 'active');
        }


        $filter     = Session::get('proctors.search.filter');
        $search     = Input::get('search', null);
        $loggedUser = Auth::user();

        // base instructors query
        $q = DB::table('proctors')
            ->select(
                'proctors.*',
                'users.username AS username',
                'users.email AS email',
                DB::raw('GROUP_CONCAT(disciplines.name) AS disc')
            )
            ->join('users', 'proctors.user_id', '=', 'users.id')
            ->leftJoin('facility_person', function ($join) {
                $join->on('proctors.id', '=', 'facility_person.person_id')
                     ->where('facility_person.person_type', '=', 'Proctor');
            })
            ->leftJoin('disciplines', 'facility_person.discipline_id', '=', 'disciplines.id');


        // search params
        // first,last,username,email
        $q->whereNested(function ($query) use ($search) {
            $query->where('first', 'like', $search.'%');
            $query->orWhere('last', 'like', $search.'%');
         });

        // count queries in each region (active/archived/all)
        // only necessary for admins/staff users
        if ($loggedUser->ability(['Admin', 'Staff'], [])) {
            $qArch   = clone $q;
            $qAll    = clone $q;
            $qActive = clone $q;

            // get results from archived region
            $resArch   = $qArch->where('proctors.status', 'LIKE', '%archive%')->count(DB::raw('DISTINCT proctors.id'));
            // get results from active region
            $resActive = $qActive->where('proctors.status', 'LIKE', '%active%')->count(DB::raw('DISTINCT proctors.id'));
            // get results from active region
            $resAll    = $qAll->count(DB::raw('DISTINCT proctors.id'));

            $r['count']['inactive'] = $resArch;
            $r['count']['active']   = $resActive;
            $r['count']['all']      = $resAll;

            // which region is staff searching within?
            if ($filter) {
                switch ($filter) {
                    case 'active':
                        $q->where('proctors.status', 'LIKE', '%active%');
                        break;
                    case 'inactive':
                        $q->where('proctors.status', 'LIKE', '%archive%');
                        break;
                    default:
                        // all regions, no query needed
                        break;
                }
            }
        }
        // non power users only search active region
        else {
            $q->where('proctors.status', 'LIKE', '%active%');
        }

        // get results, order and paginate
        $r['proctors'] = $q->groupBy('proctors.id')
                           ->orderBy(Input::get('sort', 'last'), Sorter::order())
                           ->paginate(Config::get('core.pagination.default'));

        return $r;
    }
}
