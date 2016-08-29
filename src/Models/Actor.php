<?php namespace Hdmaster\Core\Models\Actor;

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
use \Sorter;
use \Discipline;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actor extends \Eloquent
{
    use SoftDeletes;
    use Person;
    use Testteam;
    use StatusTrait;
    use LicenseTrait;

    protected $dates      = ['deleted_at'];
    protected $morphClass = 'Actor';
    protected $fillable   = [
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
        'discipline_id'         => 'required',
        'first'                 => 'required',
        'last'                  => 'required',
        'birthdate'             => 'required|date',
        'password'              => 'min:8|confirmed',
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
            Session::put('actors.search.filter', Input::get('s'));
        } elseif (! Session::has('actors.search.filter')) {
            Session::put('actors.search.filter', 'active');
        }

        $filter     = Session::get('actors.search.filter');
        $search     = Input::get('search', null);
        $loggedUser = Auth::user();

        // base instructors query
        $q = DB::table('actors')
            ->select(
                'actors.*',
                'users.username AS username',
                'users.email AS email',
                DB::raw('GROUP_CONCAT(disciplines.name) AS disc')
            )
            ->join('users', 'actors.user_id', '=', 'users.id')
            ->leftJoin('facility_person', function ($join) {
                $join->on('actors.id', '=', 'facility_person.person_id')
                     ->where('facility_person.person_type', '=', 'Actor');
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
            $resArch   = $qArch->where('actors.status', 'LIKE', '%archive%')->count(DB::raw('DISTINCT actors.id'));
            // get results from active region
            $resActive = $qActive->where('actors.status', 'LIKE', '%active%')->count(DB::raw('DISTINCT actors.id'));
            // get results from active region
            $resAll    = $qAll->count(DB::raw('DISTINCT actors.id'));

            $r['count']['inactive'] = $resArch;
            $r['count']['active']   = $resActive;
            $r['count']['all']      = $resAll;

            // which region is staff searching within?
            if ($filter) {
                switch ($filter) {
                    case 'active':
                        $q->where('actors.status', 'LIKE', '%active%');
                        break;
                    case 'inactive':
                        $q->where('actors.status', 'LIKE', '%archive%');
                        break;
                    default:
                        // all regions, no query needed
                        break;
                }
            }
        }
        // non power users only search active region
        else {
            $q->where('actors.status', 'LIKE', '%active%');
        }

        // get results, order and paginate
        $r['actors'] = $q->groupBy('actors.id')
                         ->orderBy(Input::get('sort', 'last'), Sorter::order())
                         ->paginate(Config::get('core.pagination.default'));

        return $r;
    }
}
