<?php namespace Hdmaster\Core\Traits;

use Config;
use Validator;
use Formatter;
use Mail;
use Input;
use Schema;
use Auth;
use Session;
use \User;
use \Facility;
use \Discipline;
use Illuminate\Database\Eloquent\Collection as Collection;

trait Person
{

    /**
     * Register event bindings
     */
    public static function bootPerson()
    {
        // Updating 
        // Also update the person's other accounts if they have them
        self::saved(function ($person) {
            // Grab the user and any other roles
            $user    = $person->user;
            $roles   = $user->roles()->get();
            $current = $person->getTable();

            // If multiple roles, update all associated tables
            if ($roles->count() > 1) {
                // For each role
                foreach ($roles->lists('name')->all() as $role) {
                    $table = str_plural(strtolower($role));

                    // if not the current table AND a table exists matching this role name
                    if ($table != $current && Schema::hasTable($table)) {
                        $info = [
                            'first'     => $person->first,
                            'middle'    => $person->middle,
                            'last'      => $person->last,
                            'birthdate' => date('Y-m-d', strtotime($person->birthdate)),
                            'address'   => $person->address,
                            'city'      => $person->city,
                            'state'     => $person->state,
                            'zip'       => $person->zip,
                            'gender'    => $person->gender,
                            'phone'     => $person->phone,
                            'alt_phone' => $person->alt_phone
                        ];

                        if (Schema::hasColumn($table, 'expires')) {
                            $info['expires'] = date('Y-m-d', strtotime($person->expires));
                        }

                        // update db record
                        \DB::table($table)->where('user_id', '=', $user->id)->update($info);
                    }
                }
            }
        });

        // Soft Delete
        // Delete any user model related
        self::deleted(function ($person) {
            $person->user->delete();
        });
    }

    /**
     * Polymorphic relation to users table
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Validation for a user
     * @param  array
     * @return boolean
     */
    public function validate($ignore_user_id = null)
    {
        $rules = $this->rules;

        if (is_numeric($ignore_user_id)) {
            unset($rules['discipline_id']);
            unset($rules['training_id']);
            unset($rules['training_site_id']);
            
            $rules['username'] = 'required|min:4|unique:users,username,'.$ignore_user_id;
            $rules['email']    = 'required|email|unique:users,email,'.$ignore_user_id;
        }

        // Create a validation Instance
        $v = Validator::make(Input::all(), $rules);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }

    /**
     * ------------------------------------------------------------------------
     * ACCESSORS
     * ------------------------------------------------------------------------
     */
    // Birthdate
    public function getBirthdateAttribute($value)
    {
        if ($value == '0000-00-00') {
            return '';
        }

        return empty($value) ? '' : date('m/d/Y', strtotime($value));
    }
    
    // Full name (First Last)
    public function getFullNameAttribute()
    {
        if (! empty($this->middle)) {
            return $this->first.' '.$this->middle.' '.$this->last;
        }
        
        return $this->first.' '.$this->last;
    }

    public function getAddressNameAttribute()
    {
        $mid = empty($this->middle) ? '' : ' '.$this->middle;
        return $this->first . $mid . ' ' . $this->last;
    }
    
    // Comma separated name (Last, First Middle)
    public function getCommaNameAttribute()
    {
        return $this->last.', '.$this->first.' '.$this->middle;
    }
    
    // Timestamps
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

    // City, State Zip as single attribute
    public function getCityStateZipAttribute($value)
    {
        if (empty($this->city) || empty($this->state) || empty($this->zip)) {
            return null;
        }
        
        return $this->city . ', ' . $this->state . ' ' . $this->zip;
    }

    // Phone numbers
    public function getPhoneAttribute($value)
    {
        return Formatter::format_phone($value);
    }
    public function getMainPhoneAttribute($value)
    {
        return Formatter::format_phone($value);
    }
    public function getAltPhoneAttribute($value)
    {
        return Formatter::format_phone($value);
    }
    
    // Expiration (used for Instructors)
    public function getExpiresAttribute($value)
    {
        return empty($value) ? '' : date('m/d/Y', strtotime($value));
    }

    // Conflict dates for people who have events relation
    public function getConflictDatesAttribute()
    {
        $conflicts = [];

        if (method_exists($this, 'events')) {
            // all dates observer is already scheduled for
            foreach ($this->events()->lists('test_date')->all() as $con) {
                $conflicts[] = date('Y-m-d', strtotime($con));
            }
        }

        // TODO: all blackout dates (dates observer requested not to be scheduled)		
        return $conflicts;
    }


    /**
     * ------------------------------------------------------------------------
     * MUTATORS
     * ------------------------------------------------------------------------
     */
    public function setExpireDateAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['expires'] = null;
            return;
        }

        // only format if it's a valid date already
        $date = date_parse($value);

        if (checkdate($date['month'], $date['day'], $date['year'])) {
            // valid date, format it to be compatible with our database
            $this->attributes['expires'] = date('Y-m-d', strtotime($value));
        } else {
            // invalid, set the attribute to whatever it was so validation takes care of it
            $this->attributes['expires'] = $value;
        }
    }
    public function setBirthdateAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['birthdate'] = null;
            return;
        }
        
        // only format if it's a valid date already
        $date = date_parse($value);
        if (checkdate($date['month'], $date['day'], $date['year'])) {
            // valid date, format it to be compatible with our database
            $this->attributes['birthdate'] = date('Y-m-d', strtotime($value));
        } else {
            // invalid, set the attribute to whatever it was so validation takes care of it
            $this->attributes['birthdate'] = $value;
        }
    }
    
    public function setMainPhoneAttribute($value)
    {
        $replace_chars = array(' ', ')', '(', '-', 'x');

        $this->attributes['phone'] = str_replace($replace_chars, '', $value);
    }
    
    public function setAltPhoneAttribute($value)
    {
        $replace_chars = array(' ', ')', '(', '-', 'x');

        $this->attributes['alt_phone'] = str_replace($replace_chars, '', $value);
    }


    /**
     * Sends an email notification that account created
     */
    public function notifyAccountCreated($person, $password)
    {
        if ($person->id && ! empty($person->user->email)) {
            // Send a confirmation of the account to the person
            Mail::send('core::emails.account_created', ['password' => $password, 'user'=>$person], function ($message) use (&$person) {
                $message->to($person->user->email, $person->first.' '.$person->last)->subject('Account Created');
            });
        }
    }

    /**
     * Add short snippet to comments 
     */
    public function signComments($signedBy, $action)
    {
        if (empty($this->comments)) {
            $this->comments = $this->comments.' | '.$action.' VIA '.$signedBy.' '.date('m/d/Y H:i:s');
        } else {
            $this->comments = $action.' VIA '.$signedBy.' '.date('m/d/Y H:i:s');
        }

        return $this->save();
    }

    /**
     * Sets required discipline/program session vars for a logged in person (instructor)
     * Should apply to all person types EXCEPT student
     */
    public function setSession($facilityId, $disciplineId, $license)
    {
        Auth::loginUsingId($this->user_id);
        Auth::user()->setupSession();

        $currProgram    = Facility::find($facilityId);
        $currDiscipline = Discipline::find($disciplineId);

        // set discipline
        Session::set('discipline.id', $currDiscipline->id);
        Session::set('discipline.name', $currDiscipline->name);
        Session::set('discipline.abbrev', $currDiscipline->abbrev);
        // set training program
        Session::set('discipline.program.id', $currProgram->id);
        Session::set('discipline.program.name', $currProgram->name);
        Session::set('discipline.program.license', $license);
        Session::set('discipline.program.training_approved', in_array('Training', $currProgram->actions));
    }

    /**
     * Default archiving method for person records
     * Overridden by specific state model archive()
     */
    public function archive()
    {
        $type = $this->getMorphClass();

        $this->toggleStatus('archive');

        // Student
        // archive all training/tests/adas
        if ($type == 'Student') {
            // archive test attempts
            $this->activeAttempts->each(function ($att) {
                $att->archived = true;
                $att->save();
            });
            $this->activeSkillAttempts->each(function ($att) {
                $att->archived = true;
                $att->save();
            });

            // archive trainings
            $this->trainings->each(function ($tr) {
                $tr->pivot->archived_at = date('Y-m-d H:i:s');
                $tr->pivot->save();
            });

            // soft delete ADA
            $this->adas->each(function ($ada) {
                $ada->pivot->deleted_at = date('Y-m-d H:i:s');
                $ada->pivot->save();
            });

            $this->deactivated_at = date('Y-m-d H:i:s');
        } else {
            // else 
        // observers/proctors/actors/instructors
            if ($type == 'Instructor') {
                // deactivate trainings
                $this->teaching_trainings()->sync([]);
            }

            // deactivate all facility relations
            \DB::table('facility_person')->where('person_id', $this->id)->where('person_type', $type)->where('active', 1)->update([
                'active' => 0
            ]);
        }

        // Log this archive
        $u = Auth::user();
        $name = is_null($u) ? '' : ' by ' . $u->userable->fullName;
        \Log::info($type . ' ' . $this->fullName . ' archived' . $name . '.');
    }

    /**
     * Restore archived record
     */
    public function activate()
    {
        if ($this->isActive) {
            return false;
        }

        $type = $this->getMorphClass();

        $this->toggleStatus('active');

        // ensure user account is active too
        $this->user->deleted_at = null;
        $this->user->save();

        // if instructor, connect student to instructor
        if (Auth::user() && Auth::user()->isRole('Instructor')) {
            $this->setCurrentInstructor(Auth::user()->userable->id);
        }

        // Log this archive
        $u = Auth::user();
        $name = is_null($u) ? '' : ' by ' . $u->userable->fullName;
        \Log::info($type . ' ' . $this->fullName . ' activated' . $name . '.');
    }

    /**
     * Change a person status from ACTIVE/ARCHIVE while preserving existing hold/lock status
     */
    public function toggleStatus($newStatus)
    {
        // get all status this record currently has
        $allStatus = explode(',', $this->status);

        // does the record already have this status? nothing to do here
        if (in_array($newStatus, $allStatus)) {
            return true;
        }

        // remove the opposite status from this
        $removeStatus = $newStatus === 'archive' ? ['active'] : ['archive'];
        $status = array_diff($allStatus, $removeStatus);

        // add new status and save
        $status = array_merge($status, (array) $newStatus);
        $this->status = implode(',', $status);
        return $this->save();
    }

    /**
     * Create a person record with the given info
     * @param  array $data  
     * @param  mixed $roles 
     * @return boolean
     */
    public function doCreate($roles)
    {
        // Randomly generate a user password
        $newPwd = str_random(8);

        // Create a user
        $user = new User;
        $user = $user->createNew($newPwd);

        // If we have a user, proceed
        if ($user !== false) {
            // Associate role(s)
            $user->addRoles($roles);

            // new person info
            $info = [
                'user_id'   => $user->id,
                'first'     => Input::get('first'),
                'middle'    => Input::get('middle'),
                'last'      => Input::get('last'),
                'birthdate' => Input::get('birthdate'),
                'gender'    => Input::get('gender'),
                'address'   => Input::get('address'),
                'city'      => Input::get('city'),
                'state'     => Input::get('state'),
                'zip'       => Input::get('zip'),
                'phone'     => Input::get('phone'),
                'alt_phone' => Input::get('alt_phone'),
                'fax'       => Input::get('fax'),
                'comments'  => Input::get('comments'),
                'status'    => 'active'
            ];

            // only observers have 'license' field
            if ($roles == 'Observer') {
                $info['license'] = Input::get('license');
                $info['payable_rate'] = Input::get('payable_rate');
            }

            // Create a new Person with the input data
            $saved = $this->create($info);

            // Associate this person record with a user record
            $saved->associateWith($user);
    
            // Disciplines and Test Sites
            // (staff do not have certain disciplines or test sites, all others do)
            if ($roles != 'Staff') {
                // Disciplines
                if (Input::get('discipline_id')) {
                    $disciplines = Input::get('discipline_id');
                    $saved->disciplines()->sync($disciplines);
                }

                // Test Sites (working at relations..)
                if (Input::get('testsite_id')) {
                    foreach (Input::get('testsite_id') as $info) {
                        $data         = explode('|', $info);
                        $disciplineId = $data[0];
                        $siteId       = $data[1];

                        $saved->facilities()->attach($siteId, [
                            'discipline_id' => $disciplineId,
                            'tm_license'    => self::generateTestmasterLicense(),
                            'active'        => true
                        ]);
                    }
                }
            }

            // send notification if email defined
            $saved->notifyAccountCreated($this, $newPwd);

            return $saved;
        }

        return false;
    }


    /**
     * Associate self with a given user account
     */
    public function associateWith($user)
    {
        if ($this->id !== null) {
            $user->userable_id   = $this->id;
            $user->userable_type = $this->getMorphClass();
            $user->save();
        }
    }

    /**
     * Person updating their own record with a form
     */
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

        return $this->save();
    }

    /**
     * Updates a person with input from a form
     */
    public function doUpdate()
    {
        // Update user attached to actor
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

        // Update actor/proctor
        $this->first      = Input::get('first');
        $this->middle     = Input::get('middle');
        $this->last       = Input::get('last');
        $this->birthdate  = Input::get('birthdate');
        $this->gender     = Input::get('gender');
        $this->address    = Input::get('address');
        $this->city       = Input::get('city');
        $this->state      = Input::get('state');
        $this->zip        = Input::get('zip');
        $this->comments   = Input::get('comments');
        $this->phone      = Input::get('phone');
        $this->alt_phone  = Input::get('alt_phone');
        $this->status     = implode(',', $status);

        return $this->save();
    }

    /**
     * Populate a new person record
     */
    public function populate()
    {
        $faker    = \Faker\Factory::create();
        $currUser = Auth::user()->userable;

        $gender    = rand(0, 1) ? 'Male' : 'Female';
        $pwd       = 'testing123';
        $user      = new User;
        $email     = $user->getFakeEmail($this->getMorphClass());
        $strip     = [' ', ')', '(', 'x', '-', '+', '.'];
        $phone     = Formatter::format_phone(substr(str_replace($strip, '', $faker->phoneNumber), 0, 10));
        $zip       = substr(str_replace($strip, '', $faker->postcode), 0, 5);

        // disciplines
        $allDisciplines = Discipline::with('testSites')->get();
        $numDisc        = rand(1, $allDisciplines->count());
        $disciplines    = $allDisciplines->random($numDisc);
        $disciplines    = new Collection($disciplines);

        // choose test sites
        $testSites = [];
        foreach ($disciplines as $d) {
            // choose some test sites
            $numSites = rand(1, $d->testSites->count());
            $sites    = $d->testSites->random($numSites);
            $sites    = is_array($sites) ? $sites : array($sites);
            $sites    = new Collection($sites);
            
            $testSites[$d->id] = $sites->lists('id')->all();
        }

        $info = [
            'first'       => $faker->firstName($gender),
            'last'        => $faker->lastName,
            'birthdate'   => date('m/d/Y', strtotime($faker->date('Y-m-d', '-18 years'))),
            'address'     => $faker->streetAddress,
            'city'        => $faker->city,
            'state'       => Config::get('core.client.abbrev'),
            'zip'         => $zip,
            'gender'      => $gender,
            'email'       => $email,
            'password'    => $pwd,
            'phone'       => $phone,
            'comments'    => 'Generated by '.$currUser->first.' '.$currUser->last.' via populate '.date('m/d/Y H:i A'),
            'disciplines' => $disciplines->lists('id')->all(),
            'testSites'      => $testSites
        ];

        // if Observer...
        // generate RN# 
        $currClass = $this->getMorphClass();
        if ($currClass == 'Observer') {
            $info['license'] = $faker->randomNumber(8).'RN';
        }
        
        return $info;
    }
}
