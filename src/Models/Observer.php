<?php namespace Hdmaster\Core\Models\Observer;

use Config;
use Input;
use Validator;
use Lang;
use Crypt;
use Formatter;
use \StatusTrait;
use \LicenseTrait;
use \User;
use \Role;
use \Person;
use \Testteam;
use \Testevent;
use \Facility;
use \Session;
use \Auth;
use \Discipline;
use \DB;
use \Sorter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

class Observer extends \Eloquent
{
    use SoftDeletes;
    use Person;
    use Testteam;
    use StatusTrait;
    use LicenseTrait;

    protected $dates      = ['deleted_at'];
    protected $morphClass = 'Observer';
    
    protected $fillable = [
        'user_id',
        'first',
        'middle',
        'last',
        'email',
        'birthdate',
        'gender',
        'license',
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
        'status',
        'payable_rate'
    ];

    protected $rules = [
        'discipline_id'         => 'required',
        'first'                 => 'required',
        'last'                  => 'required',
        'birthdate'             => 'required|date',
        'phone'                 => 'required',
        'password'              => 'required|min:8|confirmed',
        'password_confirmation' => 'required|min:8',
        'license'               => 'unique_observer_license',
        'payable_rate'            => 'required'
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

    /**
     * Observer acting as actor/proctor
     */
    public function acting()
    {
        return $this->morphMany(Testevent::class, 'actor')->orderBy('test_date', 'ASC');
    }
    public function proctoring()
    {
        return $this->morphMany(Testevent::class, 'proctor')->orderBy('test_date', 'ASC');
    }


    public function handleSearch()
    {
        // sidebar filter (active|inactive|all)
        if (Input::get('s')) {
            Session::put('observers.search.filter', Input::get('s'));
        } elseif (! Session::has('observers.search.filter')) {
            Session::put('observers.search.filter', 'active');
        }

        $filter     = Session::get('observers.search.filter');
        $search     = Input::get('search', null);
        $loggedUser = Auth::user();

        // base query
        $q = DB::table('observers')
            ->select(
                'observers.*',
                'users.username AS username',
                'users.email AS email',
                DB::raw('GROUP_CONCAT(disciplines.name) AS disc')
            )
            ->join('users', 'observers.user_id', '=', 'users.id')
            ->leftJoin('facility_person', function ($join) {
                $join->on('observers.id', '=', 'facility_person.person_id')
                     ->where('facility_person.person_type', '=', 'Observer');
            })
            ->leftJoin('disciplines', 'facility_person.discipline_id', '=', 'disciplines.id');

        // search params
        // first,last,username,email
        $q->whereNested(function ($query) use ($search) {
            $query->where('first', 'like', $search.'%');
            $query->orWhere('last', 'like', $search.'%');
            $query->orWhere('users.email', '=', $search);
            $query->orWhere('users.username', '=', $search);
         });

        // count queries in each region (active/archived/all)
        // only necessary for admins/staff users
        if ($loggedUser->ability(['Admin', 'Staff'], [])) {
            $qArch   = clone $q;
            $qAll    = clone $q;
            $qActive = clone $q;

            // get results from archived region
            $resArch   = $qArch->where('observers.status', 'LIKE', '%archive%')->count(DB::raw('DISTINCT observers.id'));
            // get results from active region
            $resActive = $qActive->where('observers.status', 'LIKE', '%active%')->count(DB::raw('DISTINCT observers.id'));
            // get results from all regions
            $resAll    = $qAll->count(DB::raw('DISTINCT observers.id'));

            // count results from each region
            $r['count']['inactive'] = $resArch;
            $r['count']['active']   = $resActive;
            $r['count']['all']      = $resAll;
        
            // which region is staff searching within?
            if ($filter) {
                switch ($filter) {
                    case 'active':
                        $q->where('observers.status', 'LIKE', '%active%');
                        break;
                    case 'inactive':
                        $q->where('observers.status', 'LIKE', '%archive%');
                        break;
                    default:
                        // all regions, no query needed
                        break;
                }
            }
        }
        // non power users only search active region
        else {
            $q->where('observers.status', 'LIKE', '%active%');
        }

        // get results, order and paginate
        $r['observers'] = $q->groupBy('observers.id')
                            ->orderBy(Input::get('sort', 'last'), Sorter::order())
                            ->paginate(Config::get('core.pagination.default'));

        return $r;
    }

    /**
     * Returns observers full name with (Observer filling in) tags
     * Useful for dropdown constructed via lists()
     */
    public function getFillingInFullNameAttribute()
    {
        return $this->full_name.' ('.Lang::choice('core::terms.observer', 1).')';
    }

    /**
     * Get all events an observer is participating in: observer, actor, or proctor
     */
    public function getAllFutureEventsAttribute()
    {
        $events = new Collection;

        $actEvents = $this->acting()->where('test_date', '>=', date('Y-m-d'))->get();
        $obsEvent = $this->events()->where('test_date', '>=', date('Y-m-d'))->get();
        $proEvent = $this->proctoring()->where('test_date', '>=', date('Y-m-d'))->get();

        // combine all events
        $events = $events->merge($actEvents);
        $events = $events->merge($obsEvent);
        $events = $events->merge($proEvent);

        // add role attribute
        $events->each(function ($evt) {
            $roles = [];

            if ($evt->observer_id == $this->id) {
                $roles[] = 'Observer';
            }
            if ($evt->proctor_type == 'Observer' && $evt->proctor_id == $this->id) {
                $roles[] = 'Proctor';
            }
            if ($evt->actor_type == 'Actor' && $evt->actor_id == $this->id) {
                $roles[] = 'Actor';
            }

            $evt->role = implode('<br>', $roles);
            return $evt;
        });

        return $events;
    }

    public function updateWithInput()
    {
        $user           = User::find($this->user_id);
        $user->email    = Input::get('email');
        $user->username = Input::get('username');
        $user->save();

        // perform actual password reset
        $user->resetPassword();

        // record status
        $status = ['active'];
        if (Input::get('holdStatus')) {
            $status[] = 'hold';
        }
        if (Input::get('lockStatus')) {
            $status[] = 'locked';
        }
        
        $this->first     = Input::get('first');
        $this->middle    = Input::get('middle');
        $this->last      = Input::get('last');
        $this->birthdate = Input::get('birthdate');
        $this->license   = Input::get('license');
        $this->gender    = Input::get('gender');
        $this->address   = Input::get('address');
        $this->city      = Input::get('city');
        $this->state     = Input::get('state');
        $this->zip       = Input::get('zip');
        $this->phone     = Input::get('phone');
        $this->alt_phone = Input::get('alt_phone');
        $this->comments  = Input::get('comments');
        $this->status    = implode(',', $status);
        $this->payable_rate = Input::get('payable_rate');

        return $this->save();
    }

    public function updateSelf()
    {
        // Update user
        $user           = User::find($this->user_id);
        $user->email    = Input::get('email');
        $user->username = Input::get('username');
        $user->save();

        // perform actual password reset
        $user->resetPassword();

        // Update actor
        $this->birthdate = Input::get('birthdate');
        $this->address   = Input::get('address');
        $this->city      = Input::get('city');
        $this->state     = Input::get('state');
        $this->zip       = Input::get('zip');
        $this->gender    = Input::get('gender');
        $this->phone     = Input::get('phone');
        $this->alt_phone = Input::get('alt_phone');

        return $this->save();
    }

    public function validate($ignoreUserId = null)
    {
        $rules = $this->rules;

        $messages = [
            'discipline_id.required'  => 'At least 1 Discipline must be selected.',
            'unique_observer_license' => 'There is already an '.Lang::choice('core::terms.observer', 1).' using this License.'
        ];

        // updating
        if (is_numeric($ignoreUserId)) {
            $observer = Observer::where('user_id', $ignoreUserId)->first();

            unset($rules['discipline_id']);

            $rules['username']              = 'required|min:4|unique:users,username,'.$ignoreUserId;
            $rules['email']                 = 'required|email|unique:users,email,'.$ignoreUserId;
            $rules['license']               = 'unique_observer_license:'.$observer->id;
            $rules['password']              = 'min:8|confirmed';
            $rules['password_confirmation'] = 'min:8';
        }

        $v = Validator::make(Input::all(), $rules, $messages);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }
}
