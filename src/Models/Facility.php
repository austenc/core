<?php namespace Hdmaster\Core\Models\Facility;

use Config;
use Formatter;
use Input;
use Validator;
use Mail;
use Log;
use \User;
use \Testevent;
use \Proctor;
use \Actor;
use \Observer;
use \Student;
use \Instructor;
use \Discipline;
use \Role;
use \Auth;
use \Sorter;
use \Session;
use \DB;
use \FacilityTrait;
use \LicenseTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;

class Facility extends \Eloquent implements StaplerableInterface
{
    use EloquentTrait;
    use SoftDeletes;
    use FacilityTrait;
    use LicenseTrait;

    protected $dates      = ['deleted_at'];
    protected $morphClass = 'Facility';
    protected $fillable   = [
        'name',
        'license',
        'fax',
        'email',
        'phone',
        'alt_phone',
        'timezone',
        'max_seats',
        'actions',
        'password',
        'password_confirmation',
        'address',
        'city',
        'state',
        'zip',
        'comments',
        'mail_address',
        'mail_city',
        'mail_state',
        'mail_zip',
        'driving_map'
    ];

    public static $rules = [
        'name'                  => 'required|unique:facilities',
        'discipline_id'         => 'required',
        'phone'                 => 'min:10',
        'fax'                   => 'min:10',
        'email'                 => 'required|email',
        'password'              => 'min:8|confirmed',
        'password_confirmation' => 'min:8',
        'max_seats'             => 'integer',
        'actions'               => 'sometimes|array',
        'address'               => 'required',
        'city'                  => 'required',
        'state'                 => 'required',
        'zip'                   => 'required'
    ];

    public $errors;

    public $siteTypes = [
        'Community College',
        'High School',
        'Independent',
        'Long Term Care',
        'Hospital',
        'Home Health',
        'Assisted Living',
        'Extended Training'
    ];

    public $avActions = [
        'Employer',
        'Reporting',
        'Sponsor',
        'Testing',
        'Training'
    ];

    /**
     * Constructor
     */
    public function __construct($attributes = [])
    {
        $this->hasAttachedFile('driving_map', [
            'styles' => [
                'medium' => '384x288'
            ]
        ]);

        parent::__construct($attributes);
    }

    // relations
    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function events()
    {
        return $this->hasMany(Testevent::class)->orderBy('test_date', 'DESC');
    }

    /**
     * Disciplines
     */
    public function disciplines()
    {
        return $this->allDisciplines()->where('facility_discipline.active', '=', true);
    }
    public function allDisciplines()
    {
        return $this->belongsToMany(Discipline::class, 'facility_discipline')->withPivot('parent_id', 'tm_license', 'old_license', 'active');
    }
    public function deactiveDisciplines()
    {
        return $this->allDisciplines()->where('facility_discipline.active', '=', false);
    }
    
    /**
     * If facility is testing approved students that trained at affiliated programs may schedule into closed events here
     * affiliated_id is facility_id
     */
    public function affiliated()
    {
        return $this->belongsToMany(Facility::class, 'facility_affiliated', 'facility_id', 'affiliated_id')->withPivot('discipline_id');
    }
    public function allAffiliated()
    {
        return $this->affiliated()->withTrashed();
    }

    // Proctors
    public function proctors()
    {
        return $this->morphedByMany(Proctor::class, 'person', 'facility_person')
                    ->withPivot('discipline_id', 'tm_license', 'old_license', 'active')
                    ->orderBy('last', 'ASC');
    }
    public function activeProctors()
    {
        return $this->proctors()->where('facility_person.active', '=', true);
    }
    public function allProctors()
    {
        return $this->proctors()->withTrashed();
    }

    // Observers
    public function observers()
    {
        return $this->morphedByMany(Observer::class, 'person', 'facility_person')
                    ->withPivot('discipline_id', 'tm_license', 'old_license', 'active')
                    ->orderBy('last', 'ASC');
    }
    public function activeObservers()
    {
        // if person does multiple disciplines they will be in facility_person table multiple times
        // groupBy() or distinct to get only active observers working at this facility back 
        return $this->observers()->where('facility_person.active', '=', true);
    }
    public function allObservers()
    {
        return $this->observers()->withTrashed();
    }

    // Actors
    public function actors()
    {
        return $this->morphedByMany(Actor::class, 'person', 'facility_person')
                    ->withPivot('discipline_id', 'tm_license', 'old_license', 'active')
                    ->orderBy('last', 'ASC');
    }
    public function activeActors()
    {
        return $this->actors()->where('facility_person.active', '=', true);
    }
    public function allActors()
    {
        return $this->actors()->withTrashed();
    }

    // Instructors
    public function instructors()
    {
        return $this->morphedByMany(Instructor::class, 'person', 'facility_person')
                    ->withPivot('discipline_id', 'tm_license', 'old_license', 'active')
                    ->orderBy('last', 'ASC');
    }
    public function activeInstructors()
    {
        return $this->instructors()->where('facility_person.active', '=', true);
    }
    public function allInstructors()
    {
        return $this->instructors()->withTrashed();
    }
    
    // Students
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_training')
                    ->where('student_training.expires', '>', date('Y-m-d'));
    }
    public function allStudents()
    {
        return $this->belongsToMany(Student::class, 'student_training')->orderBy('started', 'DESC');
    }

    // Accessors
    public function getNameAttribute($value)
    {
        return ucwords($value);
    }
    public function getFullNameAttribute()
    {
        return $this->name;
    }
    public function getFullAddressAttribute()
    {
        return $this->address.' '.$this->city.', '.$this->state.' '.$this->zip;
    }
    public function getPrettyNameAddressAttribute()
    {
        return $this->name."<br><small>".$this->city.", ".$this->state."</small>";
    }
    public function getPhoneAttribute($value)
    {
        return Formatter::format_phone($value);
    }
    public function getFaxAttribute($value)
    {
        return Formatter::format_phone($value);
    }
    public function getActionsAttribute($value)
    {
        return array_map('ucwords', explode('|', $value));
    }
    public function getExpiresAttribute($value)
    {
        return empty($value) ? '' : date('m/d/Y', strtotime($value));
    }
    public function getLastTrainingApprovalAttribute($value)
    {
        return empty($value) ? '' : date('m/d/Y', strtotime($value));
    }
    public function getUpdatedAtAttribute($value)
    {
        return date('m/d/Y g:i A', strtotime($value));
    }
    public function getCreatedAtAttribute($value)
    {
        return date('m/d/Y g:i A', strtotime($value));
    }
    public function getDeletedAtAttribute($value)
    {
        return empty($value) ? '' : date('m/d/Y g:i A', strtotime($value));
    }
    public function getSyncedAtAttribute($value)
    {
        return empty($value) ? '' : date('m/d/Y g:i A', strtotime($value));
    }
    public function getIsArchivedAttribute()
    {
        return strpos($this->status, 'archive') !== false;
    }
    public function getIsLockedAttribute()
    {
        return strpos($this->status, 'locked') !== false;
    }
    public function getIsHoldAttribute()
    {
        return strpos($this->status, 'hold') !== false;
    }
    public function getCanLoginAsAttribute()
    {
        return Auth::user()->can('login_as') && ! $this->isLocked && ! $this->disciplines->isEmpty();
    }

    // Mutators
    public function setPhoneAttribute($value)
    {
        $replace_chars = array(' ', ')', '(', '-', 'x');

        $this->attributes['phone'] = str_replace($replace_chars, '', $value);
    }
    public function setFaxAttribute($value)
    {
        $replace_chars = array(' ', ')', '(', '-', 'x');

        $this->attributes['fax'] = str_replace($replace_chars, '', $value);
    }

    /**
     * Saves current or default search filter
     */
    public function setSearchFilter()
    {
        // sidebar filter
        // active|inactive|all
        if (Input::get('s')) {
            Session::put('facilities.search.filter', Input::get('s'));
        } else {
            // if filter isnt already set, set to default
            if (! Session::has('facilities.search.filter')) {
                Session::put('facilities.search.filter', 'active');
            }
        }
    }

    /**
     * Check for seach terms and apply them to query
     */
    public function addSearchTerms($query, $types, $terms)
    {
        if ($types !== null) {
            foreach ($types as $k => $type) {
                $search = $terms[$k];

                switch ($type) {
                    case 'Name':
                        $query->where('facilities.name', 'like', '%'.$search.'%');
                    break;
                    case 'Email':
                        $query->where('users.email', 'like', '%'.$search.'%');
                    break;
                    case 'City':
                        $query->where('city', $search);
                    break;
                    case 'State License':
                        $query->where('facilities.license', $search);
                    break;
                    case 'Testmaster License':
                        $query->where('facility_discipline.tm_license', $search);
                    break;

                    default:
                        // do nothing for now, just show all records
                }
            }
        }

        return $query;
    }

    /**
     * Sets necessary discipline session vars for a logged in facility
     */
    public function setSession($discipline)
    {
        Session::set('discipline.id', $discipline->id);
        Session::set('discipline.name', $discipline->name);
        Session::set('discipline.abbrev', $discipline->abbrev);

        // lookup facility tm_license for this discipline
        $res = DB::table('facility_discipline')
                ->where('discipline_id', $discipline->id)
                ->where('facility_id', $this->id)
                ->first();

        if ($res) {
            Session::set('discipline.license', $res->tm_license);
        }
    }

    /**
     * Main function that handles finding subset of students for search
     * Assembles query with search terms and region filter
     */
    public function handleSearch()
    {
        // set session vars to track which region we are filtering
        $this->setSearchFilter();

        $searchTypes   = Session::get('facilities.search.types');
        $searchQueries = Session::get('facilities.search.queries');
        $filter        = Session::get('facilities.search.filter');
        $loggedUser    = Auth::user();

        // This is new based on discipline
        $searchDiscipline = Session::get('facilities.search.discipline');

        // base query
        $q = DB::table('facilities')
            ->select(
                'facilities.*',
                'users.username AS username',
                'users.email AS email',
                DB::raw('GROUP_CONCAT(disciplines.name) AS disc'),
                DB::raw('GROUP_CONCAT(facility_discipline.tm_license) AS tm_license'),
                DB::raw('GROUP_CONCAT(facility_discipline.active) AS disc_active')
            )
            ->join('users', 'facilities.user_id', '=', 'users.id')
            ->join('facility_discipline', 'facilities.id', '=', 'facility_discipline.facility_id')
            ->join('disciplines', 'facility_discipline.discipline_id', '=', 'disciplines.id');

        if ($searchDiscipline !== null && $searchDiscipline != "All") {
            $discRec = Discipline::where('abbrev', '=', $searchDiscipline)->first();
            $q->where('facility_discipline.discipline_id', '=', $discRec->id);
        }

        // add search terms to the query
        $q = $this->addSearchTerms($q, $searchTypes, $searchQueries);

        // count queries in each region (active/archived/all)
        // only necessary for admins/staff users
        if ($loggedUser->ability(['Admin', 'Staff'], [])) {
            $qArch   = clone $q;
            $qAll    = clone $q;
            $qActive = clone $q;

            // get results from archived region
            $resArch   = $qArch->where('facilities.status', 'LIKE', '%archive%')->count(DB::raw('DISTINCT facilities.id'));
            // get results from active region
            $resActive = $qActive->where('facilities.status', 'LIKE', '%active%')->count(DB::raw('DISTINCT facilities.id'));
            // get results from all regions
            $resAll    = $qAll->count(DB::raw('DISTINCT facilities.id'));


            // count results from each region
            $r['count']['inactive'] = $resArch;
            $r['count']['active']   = $resActive;
            $r['count']['all']      = $resAll;

            // which region is staff searching within?
            if ($filter) {
                switch ($filter) {
                    case 'active':
                        $q->where('facilities.status', 'LIKE', '%active%');
                        break;
                    case 'inactive':
                        $q->where('facilities.status', 'LIKE', '%archive%');
                        break;
                    default:
                        // all regions, no query needed
                        break;
                }
            }
        }
        // non-powerusers
        else {
            // active region only
            $q->where('facilities.status', 'LIKE', '%active%');
        }

        $r['facilities'] = $q->groupBy('facilities.id')
                             ->orderBy(Input::get('sort', 'name'), Sorter::order())
                             ->paginate(Config::get('core.pagination.default'));

        return $r;
    }

    /**
     * Validate a new facility record before creating
     */
    public function validateCreate($data)
    {
        $rules    = static::$rules;
        $messages = ['discipline_id.required' => 'At least 1 Discipline is required.'];

        $validation = Validator::make($data, $rules, $messages);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    /**
     * Validate an existing facility record before updating
     */
    public function validateUpdate($data)
    {
        $rules    = static::$rules;

        $rules['email']    = 'required|email|unique:users,email,'.$this->user_id;
        $rules['name']     = 'required|unique:facilities,name,'.$this->id;
        $rules['username'] = 'required|min:4|unique:users,username,'.$this->user_id;
        unset($rules['discipline_id']);

        $validation = Validator::make($data, $rules);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    /**
     * Adds new user based on form input
     */
    public function addWithInput()
    {
        $name = Input::get('name');

        // Create a new user
        $user                        = new User;
        $username                    = $user->unique_username($name);
        $user->email                 = Input::get('email');
        $user->username              = $username;
        $newPwd                      = str_random(8);
        $user->password              = $newPwd;
        $user->password_confirmation = $newPwd;
        $user->confirmed             = 1;
        $user->save();

        // Setup role
        $role = Role::where('name', '=', 'Facility')->first();
        $user->attachRole($role);

        // make sure user created above
        if ($user->id !== null) {
            // Create a new instructor
            $f = new Facility;

            $f->user_id       = $user->id;
            $f->name          = Input::get('name');
            $f->license       = Input::get('license') ? Input::get('license') : null;    // state license
            $f->phone         = Input::get('phone') ?: null;
            $f->fax           = Input::get('fax') ?: null;
            $f->timezone      = Input::get('timezone');
            $f->address       = Input::get('address');
            $f->city          = Input::get('city');
            $f->state         = Input::get('state');
            $f->zip           = Input::get('zip');
            $f->mail_address  = Input::get('mail_address') ?: null;
            $f->mail_city     = Input::get('mail_city') ?: null;
            $f->mail_state    = Input::get('mail_state') ?: null;
            $f->mail_zip      = Input::get('mail_zip') ?: null;
            $f->comments      = Input::get('comments') ?: null;
            $f->actions       = implode('|', Input::get('actions', []));
            $f->site_type     = Input::get('site_type');
            $f->administrator = Input::get('administrator') ?: null;
            $f->don           = Input::get('don') ?: null;
            $f->expires       = date('Y-m-t', strtotime("+2 years"));
            $f->status        = 'active';
            $f->save();

            // add disciplines
            foreach (Input::get('discipline_id') as $disciplineId) {
                $parent = Input::get('discipline_parent.'.$disciplineId) != 0 ? Input::get('discipline_parent.'.$disciplineId) : 0;

                $f->disciplines()->attach($disciplineId, [
                    'parent_id'  => $parent,
                    'tm_license' => $this->generateTestmasterLicense()
                ]);
            }

            // polymorphic rel
            $user->userable_id   = $f->id;
            $user->userable_type = $f->getMorphClass();
            $user->save();

            // Send email notification if email defined
            $this->notifyAccountCreated($f, $newPwd);

            return $f->id;
        }

        return false;
    }

    private function getStatusString($currStatus)
    {
        $status = [$currStatus];

        if (strpos($this->status, 'hold') !== false) {
            $status[] = 'hold';
        }
        if (strpos($this->status, 'locked') !== false) {
            $status[] = 'locked';
        }

        return $status;
    }

    /**
     * Archive this Facility
     */
    public function archive()
    {
        $this->status = implode(',', $this->getStatusString('archive'));
        $this->save();

        // deactivate all disciplines and facility workers
        \DB::table('facility_discipline')->where('active', true)->where('facility_id', $this->id)->update(['active' => false]);
        \DB::table('facility_person')->where('active', true)->where('facility_id', $this->id)->update(['active' => false]);

        // Log this archive
        $u = Auth::user();
        $name = is_null($u) ? '' : ' by ' . $u->userable->fullName;
        \Log::info('Facility ' . $this->name . ' archived' . $name . '.');

        return;
    }

    /**
     * Activate this facility
     */
    public function activate()
    {
        $this->status = implode(',', $this->getStatusString('active'));
        $this->save();

        // check user record exists
        if ($this->user) {
            // make sure the user account is active
            $this->user->deleted_at = null;
            $this->user->save();
        }
        // create new user account
        // (in case they didnt start with a user record, make sure they get one now)
        else {
            $user                        = new User;
            $user->email                 = $user->getFakeEmail('facility');
            $username                    = $user->unique_username($this->name);
            $user->username              = $username;
            $newPwd                      = str_random(8);
            $user->password              = $newPwd;
            $user->password_confirmation = $newPwd;
            $user->confirmed             = 1;
            $user->save();

            // Setup role
            $role = Role::where('name', '=', 'Facility')->first();
            $user->attachRole($role);

            // polymorphic rel
            $user->userable_id   = $this->id;
            $user->userable_type = $this->getMorphClass();
            $user->save();

            // update facility user id
            $this->user_id = $user->id;
            $this->save();
        }

        // add any email triggers or logs here
        // Log this activation
        $u = Auth::user();
        $name = is_null($u) ? '' : ' by ' . $u->userable->fullName;
        \Log::info('Facility ' . $this->name . ' activated' . $name . '.');

        return;
    }

    /**
     * Updates (or tries) with input from form
     * @return bool
     */
    public function updateWithInput()
    {
        // Update user attached to facility
        $user           = User::find($this->user_id);
        $user->email    = Input::get('email');
        $user->username = Input::get('username');
        $user->save();

        // perform actual password reset
        $user->resetPassword();

        $curHoldStatus = in_array('hold', explode(",", $this->status));

        // record status
        $status = ['active'];
        if (Input::get('holdStatus')) {
            $status[] = 'hold';
        }
        if (Input::get('lockStatus')) {
            $status[] = 'locked';
        }
        
        // Update facility
        $this->name          = Input::get('name');
        $this->license       = Input::get('license') ?: null;
        $this->phone         = Input::get('phone') ?: null;
        $this->fax           = Input::get('fax') ?: null;
        $this->timezone      = Input::get('timezone');
        $this->max_seats     = Input::get('max_seats') ?: null;
        $this->address       = Input::get('address');
        $this->city          = Input::get('city');
        $this->state         = Input::get('state');
        $this->zip           = Input::get('zip');
        $this->mail_address  = Input::get('mail_address') ?: null;
        $this->mail_city     = Input::get('mail_city') ?: null;
        $this->mail_state    = Input::get('mail_state') ?: null;
        $this->mail_zip      = Input::get('mail_zip') ?: null;
        $this->actions       = implode('|', Input::get('actions', []));
        $this->site_type     = Input::get('site_type');
        $this->agency_only   = Input::get('agency_only');
        $this->administrator = Input::get('administrator') ?: null;
        $this->don           = Input::get('don') ?: null;
        $this->comments      = Input::get('comments') ?: null;
        $this->directions    = Input::get('directions') ?: null;
        $this->driving_map   = Input::file('driving_map');
        $this->status        = implode(',', $status);

        // All facility people (actors, observers, proctors, instructors)
        // must be added thru special page to ensure each relation has unique license

        // update disciplines
        if (Input::get('discipline_parent')) {
            foreach (Input::get('discipline_parent') as $discId => $parentId) {
                $parentId = $parentId == 0 ? null : Input::get('discipline_parent.'.$discId);

                DB::table('facility_discipline')
                    ->where('facility_id', $this->id)
                    ->where('discipline_id', $discId)
                    ->update([
                        'parent_id' => $parentId
                    ]);
            }
        }

        return $this->save();
    }

    /**
     * Facility updating their own record
     */
    public function updateSelf()
    {
        $user           = User::find($this->user_id);
        $user->email    = Input::get('email');
        $user->username = Input::get('username');
        $user->save();

        $user->resetPassword();

        $this->phone        = Input::get('phone');
        $this->fax          = Input::get('fax');
        $this->address      = Input::get('address');
        $this->city         = Input::get('city');
        $this->state        = Input::get('state');
        $this->zip          = Input::get('zip');
        $this->mail_address = Input::get('mail_address');
        $this->mail_city    = Input::get('mail_city');
        $this->mail_state   = Input::get('mail_state');
        $this->mail_zip     = Input::get('mail_zip');

        return $this->save();
    }


    public function signComments($signedBy, $action)
    {
        if (empty($this->comments)) {
            $this->comments = $action.' VIA '.$signedBy.' '.date('m/d/Y H:i:s');
        } else {
            $this->comments = $this->comments.' | '.$action.' VIA '.$signedBy.' '.date('m/d/Y H:i:s');
        }

        return $this->save();
    }


    /**
     * Sends an email notification that account created
     */
    public function notifyAccountCreated($facility, $password)
    {
        if ($facility->id && ! empty($facility->user->email)) {
            // Send a confirmation of the account to the person
            Mail::send('core::emails.account_created', ['password' => $password, 'user' => $facility], function ($message) use (&$facility) {
                $message->to($facility->user->email, $facility->name)->subject('Account Created');
            });
        }
    }

    /**
     * Generate fake facility
     */
    public function populate()
    {
        $faker = \Faker\Factory::create();

        // choose some disciplines (certifications)
        $disp       = Discipline::all();
        $discipline = $faker->randomElements($disp->lists('id')->all(), rand(1, $disp->count()));

        $user     = new User;
        $email    = $user->getFakeEmail('facility');
        $strip    = [' ', ')', '(', 'x', '-', '+', '.'];
        $phone    = Formatter::format_phone(substr(str_replace($strip, '', $faker->phoneNumber), 0, 10));
        $zip      = substr(str_replace($strip, '', $faker->postcode), 0, 5);
        $actions  = $faker->randomElements($this->avActions, rand(1, count($this->avActions)));

        // site type
        $avSiteTypes = $this->siteTypes;
        array_unshift($avSiteTypes, "Select Site Type");
        unset($avSiteTypes[0]);    // remove default option
        $siteType = $faker->randomElement($avSiteTypes);

        $info = [
            'name'       => $faker->company,
            'discipline' => $discipline,
            'address'    => $faker->streetAddress,
            'city'       => $faker->city,
            'state'      => Config::get('core.client.abbrev'),
            'phone'      => $phone,
            'zip'        => $zip,
            'email'      => $email,
            'don'        => $faker->firstName.' '.$faker->lastName.' RN',
            'actions'    => $actions,
            'siteType'   => $siteType
        ];

        return $info;
    }
}
