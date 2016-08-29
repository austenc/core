<?php namespace Hdmaster\Core\Models\Instructor;

use Input;
use Config;
use View;
use Formatter;
use Log;
use Hash;
use Lang;
use Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use \StatusTrait;
use \LicenseTrait;
use \Training;
use \User;
use \Person;
use \Student;
use \Role;
use \Facility;
use \Session;
use \Sorter;
use \Auth;
use \StudentTraining;
use \DB;
use \Discipline;

class Instructor extends \Eloquent
{
    use Person;
    use SoftDeletes;
    use StatusTrait;
    use LicenseTrait;

    protected $dates      = ['deleted_at'];
    protected $morphClass = 'Instructor';
    protected $fillable   = [
        'first',
        'last',
        'middle',
        'email',
        'birthdate',
        'license',
        'phone',
        'alt_phone',
        'password',
        'password_confirmation',
        'sel_training',
        'comments',
        'expires'
    ];

    protected $rules = [
        'first'                 => 'required',
        'last'                  => 'required',
        'birthdate'             => 'date',
        'password'              => 'min:8|confirmed',
        'password_confirmation' => 'min:8',
        'training_id'           => 'required|array|min:1',
        'discipline_id'         => 'required|array|min:1',
        'training_site_id'      => 'required|array|min:1'
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
     * The active students 'owned' by this instructor
     * @return Relation
     */
    public function students()
    {
        return $this->allStudents()->where('instructor_student.active', '=', true);
    }

    /**
     * All this instructor's students, past or present
     * @return Relation
     */
    public function allStudents()
    {
        return $this->belongsToMany(Student::class)->withPivot('active')->withTimestamps();
    }

    /**
     * Gets a collection of every training thru this instructor
     */
    public function studentTrainings()
    {
        return $this->hasMany(StudentTraining::class)->orderBy('started', 'DESC');
    }

    /**
     * All facilities this instructor works at
     */
    public function facilities()
    {
        return $this->morphToMany(Facility::class, 'person', 'facility_person')
                    ->withTrashed()
                    ->withPivot('discipline_id', 'tm_license', 'old_license', 'active')
                    ->orderBy('discipline_id', 'ASC');
    }
    public function activeFacilities()
    {
        return $this->facilities()->where('facility_person.active', '=', true);
    }
    public function inactiveFacilities()
    {
        return $this->facilities()->where('facility_person.active', '=', false);
    }
    public function activeTrainingPrograms()
    {
        return $this->activeFacilities()->where('actions', 'LIKE', '%Training%');
    }


    /**
     * Students that this instructor is a creator OF
     */
    public function creatorOf()
    {
        return $this->morphMany(Student::class, 'creator');
    }

    public function disciplines()
    {
        return $this->morphToMany(Discipline::class, 'person', 'facility_person')
                    ->groupBy('facility_person.discipline_id')
                    ->withPivot('tm_license', 'old_license', 'active');
    }

    /**
     * Trainings this Instructor is allowed to teach (add to student record)
     */
    public function teaching_trainings()
    {
        return $this->belongsToMany(Training::class, 'instructor_training');
    }

    /**
     * Gets the number of days until this instructor will expire
     * Used to decide when to show warning on instructors.edit page to alert user they will expire soon
     */
    public function getExpiresInDaysAttribute()
    {
        $exp = $this->getOriginal('expires');
        
        if (empty($exp)) {
            return '';
        }

        $currExp = new \DateTime($exp);
        $now     = new \DateTime(date('Y-m-d'));
        $diff    = $currExp->diff($now)->format("%a");
        
        return $diff;
    }

    /**
     * Generate unique RN license number
     * XXXYYYZZZRN
     */
    public function generateUniqueLicense()
    {
        $faker = \Faker\Factory::create();

        $newLicense = '';
        while (empty($newLicense)) {
            $tmpLicense = $faker->numberBetween(100000, 999999).'RN';

            // check license is already in use
            $i = Instructor::where('license', $tmpLicense)->first();

            if (is_null($i)) {
                $newLicense = $tmpLicense;
            }
        }

        return $newLicense;
    }

    /**
     * Checks if this instructor has an active relation with discipline at facility
     */
    public function hasDisciplineFacility($disciplineId, $facilityId)
    {
        $res = DB::table('facility_person')
            ->where('facility_id', $facilityId)
            ->where('discipline_id', $disciplineId)
            ->where('person_id', $this->id)
            ->where('person_type', $this->getMorphClass())
            ->where('active', 1)
            ->first();

        return ! empty($res);
    }

    /**
     * Handle incoming search from instructors.index page
     */
    public function handleSearch()
    {
        // sidebar filter (active|inactive|all)
        if (Input::get('s')) {
            Session::put('instructors.search.filter', Input::get('s'));
        } else {
            Session::put('instructors.search.filter', 'active');
        }

        $filter     = Session::get('instructors.search.filter');
        $search     = Input::get('search', null);
        $loggedUser = Auth::user();

        $search_types      = Session::get('instructors.search.types');
        $search_discipline = Session::get('instructors.search.discipline');
        $search_queries    = Session::get('instructors.search.queries');
        $filter            = Session::get('instructors.search.filter');
        $disciplineId      = Session::get('discipline.id');

        // base instructors query
        $q = DB::table('instructors')
            ->select(
                'instructors.*',
                'users.username AS username',
                'users.email AS email',
                DB::raw('GROUP_CONCAT(disciplines.name) AS disc')
            )
            ->join('users', 'instructors.user_id', '=', 'users.id')
            ->join('facility_person', function ($join) {
                $join->on('instructors.id', '=', 'facility_person.person_id')
                     ->where('facility_person.person_type', '=', 'Instructor');
            })
            ->join('disciplines', 'facility_person.discipline_id', '=', 'disciplines.id');

        // search within specific discipline?
        if ($search_discipline !== null && $search_discipline != "All") {
            $discRec = Discipline::where('abbrev', '=', $search_discipline)->first();
            $q->where('facility_person.discipline_id', '=', $discRec->id);
        }

        // Added for Instructor Advanced Serach 08.12.2015 Issue #115
        if ($search_types !== null) {
            foreach ($search_types as $k => $type) {
                $search = $search_queries[$k];
                switch ($type) {
                    case 'Name':
                        // is there a comma?
                        if (strpos($search, ',') !== false) {
                            list($last, $first) = explode(',', $search, 2);

                            $q->where(function ($query) use ($first, $last) {
                                $query->where('instructors.first', 'like', $first.'%')
                                      ->where('instructors.last', 'like', $last.'%');
                            });
                        }
                        // First Last
                        elseif (strpos($search, ' ') !== false) {
                            list($first, $last) = explode(' ', $search, 2);

                            $q->where(function ($query) use ($first, $last) {
                                $query->where('instructors.first', 'like', $first.'%')
                                      ->where('instructors.last', 'like', $last.'%');
                            });
                        }
                        // Last First
                        else {
                            list($first, $last) = array($search, $search);

                            // OR WHERE
                            $q->where(function ($query) use ($first, $last) {
                                $query->where('instructors.first', 'like', $first.'%')
                                      ->orWhere('instructors.last', 'like', $last.'%');
                            });
                        }
                        break;

                    case 'Birth Date':
                        $bd = substr($search, 6, 4) . "-" . substr($search, 0, 2) . "-" . substr($search, 3, 2);
                        $q->where('instructors.birthdate', '=', $bd);
                        break;

                    case 'Email':
                        $q->where('users.email', '=', $search);
                        break;

                    case 'City':
                        $q->where('instructors.city', 'like', $search.'%');
                        break;

                    case 'License':
                        $q->where('instructors.license', 'like', $search.'%');
                        break;
                    case 'TM License':
                        $q->where('facility_person.person_type', '=', 'Instructor')->where('facility_person.tm_license', '=', $search);
                        break;
                    default:
                        // do nothing for now, just show all records
                }
            }
        }

        // count queries in each region (active/archived/all)
        // only necessary for admins/staff users
        if ($loggedUser->ability(['Admin', 'Staff'], [])) {
            $qArch   = clone $q;
            $qAll    = clone $q;
            $qActive = clone $q;

            // get results from archived region
            $resArch   = $qArch->where('instructors.status', 'LIKE', '%archive%')->count(DB::raw('DISTINCT instructors.id'));
            // get results from active region
            $resActive = $qActive->where('instructors.status', 'LIKE', '%active%')->count(DB::raw('DISTINCT instructors.id'));
            // get results from active region
            $resAll    = $qAll->count(DB::raw('DISTINCT instructors.id'));

            $r['count']['inactive'] = $resArch;
            $r['count']['active']   = $resActive;
            $r['count']['all']      = $resAll;

            // which region is staff searching within?
            if ($filter) {
                switch ($filter) {
                    case 'active':
                        $q->where('instructors.status', 'LIKE', '%active%');
                        break;
                    case 'inactive':
                        $q->where('instructors.status', 'LIKE', '%archive%');
                        break;
                    default:
                        // all regions, no query needed
                        break;
                }
            }
        }
        // non power users only search active region
        else {
            $q->where('instructors.status', 'LIKE', '%active%');
        }

        $r['instructors'] = $q->groupBy('instructors.id')
                              ->orderBy(Input::get('sort', 'last'), Sorter::order())
                              ->paginate(Config::get('core.pagination.default'));
        return $r;
    }

    /**
     * Generate fake instructor
     */
    public function populate()
    {
        $currUser = Auth::user()->userable;
        $faker    = \Faker\Factory::create();
        
        $gender    = rand(0, 1) ? 'Male' : 'Female';
        $pwd       = 'testing123';
        $user      = new User;
        $email     = $user->getFakeEmail('instructor');
        $strip     = [' ', ')', '(', 'x', '-', '+', '.'];
        $phone     = Formatter::format_phone(substr(str_replace($strip, '', $faker->phoneNumber), 0, 10));
        $zip       = substr(str_replace($strip, '', $faker->postcode), 0, 5);
        
        // choose disciplines
        $numDisp     = rand(1, Discipline::count());
        $disciplines = Discipline::with('training', 'trainingPrograms')->get()->random($numDisp);
        $dispIds     = [];
        if (! is_array($disciplines)) {
            $disciplines = array($disciplines);
        }

        // choose training programs and trainings from each discipline
        $programs  = [];
        $trainings = [];
        $dispIds   = [];
        foreach ($disciplines as $d) {
            $dispIds[] = $d->id;

            // choose some discipline training programs
            $numPrograms = rand(1, $d->trainingPrograms->count());
            $trPrograms    = $d->testSites->random($numPrograms);
            $trPrograms    = is_array($trPrograms) ? $trPrograms : array($trPrograms);
            $trPrograms    = new Collection($trPrograms);
            
            $programs[$d->id] = $trPrograms->lists('id')->all();

            // get discipline trainings
            $currTrainings = $faker->randomElements($d->training->lists('id')->all(), rand(1, $d->training->count()));
            $trainings = array_merge($currTrainings, $trainings);
        }


        $info = [
            'first'       => $faker->firstName($gender),
            'last'        => $faker->lastName,
            'birthdate'   => date('m/d/Y', strtotime($faker->date('Y-m-d', '-18 years'))),
            'license'     => $this->generateUniqueLicense(),    // state RN #
            'address'     => $faker->streetAddress,
            'city'        => $faker->city,
            'state'       => Config::get('core.client.abbrev'),
            'zip'         => $zip,
            'gender'      => $gender,
            'email'       => $email,
            'password'    => $pwd,
            'phone'       => $phone,
            'trainings'   => $trainings,
            'programs'    => $programs,
            'disciplines' => $dispIds,
            'comments'    => 'Generated by '.$currUser->first.' '.$currUser->last.' via populate '.date('m/d/Y H:i A')
        ];

        return $info;
    }

    /**
     * Create a new instructor
     */
    public function addWithInput()
    {
        $firstname     = Input::get('first');
        $lastname      = Input::get('last');
        $trainingIds   = Input::get('training_id');
        $disciplineIds = Input::get('discipline_id');
        $programIds    = Input::get('training_site_id');

        // Create a new user
        $user                        = new User;
        $username                    = $user->unique_username($lastname, $firstname);
        $user->email                 = Input::get('email');
        $user->username              = $username;
        $newPwd                      = str_random(8);
        $user->password              = $newPwd;
        $user->password_confirmation = $newPwd;
        $user->confirmed             = 1;
        $user->save();

        // Setup role
        $role = Role::where('name', '=', 'Instructor')->first();
        $user->attachRole($role);

        // make sure user created above
        if ($user->id !== null) {
            // Create a new instructor
            $i                = new Instructor;
            $i->user_id       = $user->id;
            $i->first         = $firstname;
            $i->middle        = Input::get('middle');
            $i->last          = $lastname;
            $i->birthdate     = date('Y-m-d', strtotime(Input::get('birthdate')));
            $i->address       = Input::get('address');
            $i->city          = Input::get('city');
            $i->state         = Input::get('state');
            $i->zip           = Input::get('zip');
            $i->gender        = Input::get('gender');
            $i->license       = Input::get('license');
            $i->phone         = Input::get('phone');
            $i->alt_phone     = Input::get('alt_phone');
            $i->comments      = Input::get('comments');
            $i->expires       = date('Y-m-t', strtotime("+2 years"));
            $saved            = $i->save();

            // polymorphic rel
            $user->userable_id   = $i->id;
            $user->userable_type = $i->getMorphClass();
            $user->save();

            // add disciplines
            if ($disciplineIds) {
                $i->disciplines()->sync($disciplineIds);
            }

            // add trainings
            if ($trainingIds) {
                $i->teaching_trainings()->sync($trainingIds);
            }

            // add training programs
            if ($programIds) {
                // need to add discipline and tm_license
                foreach ($programIds as $p) {
                    list($disciplineId, $facilityId) = explode("|", $p);

                    $i->facilities()->attach($facilityId, [
                        'discipline_id' => $disciplineId,
                        'tm_license'    => $this->generateTestmasterLicense()
                    ]);
                }
            }

            // Send email notification if email defined
            $this->notifyAccountCreated($i, $newPwd);

            return $i->id;
        }

        return false;
    }

    /**
     * Update Instructor
     * Trainings, Training programs, and Disciplines are managed separately
     */
    public function updateWithInput()
    {
        // Update user attached to instructor
        $user           = User::find($this->user_id);
        $user->email    = Input::get('email');
        $user->username = Input::get('username');
        $user->save();
            
        // perform actual password reset
        $user->resetPassword();

        // update status
        $status = ['active'];
        if (Input::get('holdStatus')) {
            $status[] = 'hold';
        }
        if (Input::get('lockStatus')) {
            $status[] = 'locked';
        }
        $this->status = implode(',', $status);

        // Update instructor
        $this->first         = Input::get('first');
        $this->middle        = Input::get('middle');
        $this->last          = Input::get('last');
        $this->birthdate     = Input::get('birthdate');
        $this->address       = Input::get('address');
        $this->city          = Input::get('city');
        $this->state         = Input::get('state');
        $this->zip           = Input::get('zip');
        $this->gender        = Input::get('gender');
        $this->license       = Input::get('license');
        $this->alt_phone     = Input::get('alt_phone');
        $this->phone         = Input::get('phone');
        $this->comments      = Input::get('comments');

        if (Auth::user()->ability(['Admin', 'Staff'], [])) {
            $this->setExpireDateAttribute(Input::get('expires'));
        }

        return $this->save();
    }

    public function validateAddProgram()
    {
        $rules = [
            'discipline_id' => 'required|not_in:0',
            'program_id'    => 'required|not_in:0',
        ];

        $messages = [
            'discipline_id.required' => 'Discipline is required',
            'discipline_id.not_in'   => 'Discipline is required',
            'program_id.required'    => Lang::choice('core::terms.facility_training', 1).' is required',
            'program_id.not_in'      => Lang::choice('core::terms.facility_training', 1).' is required'
        ];

        $v = Validator::make(Input::all(), $rules, $messages);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }
}
