<?php namespace Hdmaster\Core\Models\Admin;

use \User;
use \Student;

use Config;
use Validator;
use Input;

/**
 * This class describes both 'Admin' and 'Staff' users
 */

class Admin extends \Eloquent
{
    
    protected $fillable   = [
            'first',
            'middle',
            'last',
            'phone',
            'email',
            'user_id'
    ];
    protected $morphClass = 'Admin';

    protected $rules      = [
        'first' => 'required',
        'last'  => 'required',
        'email' => 'required|email|unique:users'
    ];

    /**
     * Polymorphic relation to users table
     *
     * @return Relation
     */
    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    /**
     * Creator of these students
     *
     * @return Relation
     */
    public function students()
    {
        return $this->morphMany(Student::class, 'creator');
    }

    /**
     * Create a given record and an associated user id
     */
    public function doCreate($roles)
    {
        $newPwd = Input::get('password');

        // Create a user
        $user = new User;
        $user = $user->createNew($newPwd);
        
        // If we have a user, proceed
        if ($user !== false) {
            // Associate role(s)
            $user->addRoles($roles);

            // Create a new Person with the input data
            // Staff or Admin 
            $saved = $roles::create([
                'user_id'   => $user->id,
                'first'     => Input::get('first'),
                'middle'    => Input::get('middle'),
                'last'      => Input::get('last'),
                'phone'     => Input::get('phone')
            ]);


            // Associate this person record with a user record
            if ($saved->id !== null) {
                $user->userable_id   = $saved->id;
                $user->userable_type = $saved->getMorphClass();
                $user->save();
            }
        
            return $saved;
        }

        return false;
    }

    /**
     * Updates a person with input from a form
     */
    public function doUpdate()
    {
        // Update user attached to person
        $user = User::find($this->user_id);
        $user->email    = Input::get('email');
        $user->save();

        // perform actual password reset
        $user->resetPassword();

        // Update person
        $this->first     = Input::get('first');
        $this->middle    = Input::get('middle');
        $this->last      = Input::get('last');

        return $this->save();
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
            $rules['username'] = 'unique:users,username,'.$ignore_user_id;
            $rules['email']    = 'required|email|unique:users,email,'.$ignore_user_id;
        }

        // Create a validation Instance
        $v = Validator::make($this->attributes, $rules);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }

    // Full name (First Last)
    public function getFullNameAttribute()
    {
        return $this->first.' '.$this->last;
    }
    
    // Comma separated name (Last, First Middle)
    public function getCommaNameAttribute()
    {
        return $this->last.', '.$this->first.' '.$this->middle;
    }
}
