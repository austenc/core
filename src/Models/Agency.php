<?php namespace Hdmaster\Core\Models\Agency;

use Config;
use Input;
use Validator;
use \User;
use \Role;

class Agency extends \Eloquent
{
    
    protected $morphClass = 'Agency';
    protected $fillable = ['user_id', 'first', 'middle', 'last', 'phone', 'email', 'gender', 'birthdate', 'address', 'city', 'state', 'zip'];
    protected $table = 'agencies';

    public static $rules = [
        'first'        => 'sometimes|required',
        'last'        => 'sometimes|required',
        'phone'        => 'min:10',
        'email'    => 'required|email',
        'password'    => 'sometimes|required|min:8|confirmed',
        'password_confirmation' => 'sometimes|required|min:8'
    ];

    // Polymorphic relation to users table
    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    // accessors
    public function getFullNameAttribute()
    {
        return $this->first.' '.$this->last;
    }
    public function getPhoneAttribute($value)
    {
        return \Formatter::format_phone($value);
    }
    public function getBirthdateAttribute($value)
    {
        if ($value == '0000-00-00') {
            return '';
        }

        return empty($value) ? '' : date('m/d/Y', strtotime($value));
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

    /**
     * Person updating their own record with a form
     */
    public function updateSelf()
    {
        // Update User
        $user = User::find($this->user_id);
        $user->email    = Input::get('email');
        $user->save();

        // perform actual password reset
        $user->resetPassword();

        // Update Agency
        $this->phone = Input::get('phone');

        return $this->save();
    }

    public function validate($ignore_user_id=null)
    {
        $rules = static::$rules;

        if (is_numeric($ignore_user_id)) {
            $rules['username'] = 'unique:users,username,'.$ignore_user_id;
            $rules['email']    = 'required|email|unique:users,email,'.$ignore_user_id;
        
            // on update, is there pwd?
            $pwd = Input::get('password');
            if (! empty($pwd)) {
                $rules['password'] = 'min:8|confirmed';
                $rules['password_confirmation'] = 'min:8';
                $this->password = $pwd;
                $this->password_confirmation = Input::get('password_confirmation');
            } else {
                // updating but password left empty
                unset($rules['password']);
                unset($rules['password_confirmation']);
            }
        }

        //$this->attributes
        $validation = Validator::make(Input::all(), $rules);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    public function addWithInput()
    {
        $first   = Input::get('first');
        $middle  = Input::get('middle');
        $last    = Input::get('last');
        $email   = Input::get('email');
        $phone   = Input::get('phone');
        $dob     = Input::get('birthdate');
        $address = Input::get('address');
        $city    = Input::get('city');
        $state   = Input::get('state');
        $zip     = Input::get('zip');
        $gender  = Input::get('gender');

        // Create a new user
        $user                        = new User;
        $user->email                 = $email;
        $user->username              = $user->unique_username($last, $first);
        $user->password              = Input::get('password');
        $user->password_confirmation = Input::get('password_confirmation');
        $user->confirmed             = 1;
        $user->save();

        // Setup role
        $role = Role::where('name', '=', 'Agency')->first();
        $user->attachRole($role);

        // make sure user created above
        if ($user->id !== null) {
            // Create a new Agency
            $a = new Agency;

            $a->user_id   = $user->id;
            $a->first     = $first;
            $a->middle    = $middle;
            $a->last      = $last;
            $a->birthdate = $dob;
            $a->phone     = $phone;
            $a->address   = $address;
            $a->city      = $city;
            $a->state     = $state;
            $a->zip       = $zip;
            $a->gender    = $gender;
            $a->save();

            // polymorphic rel
            $user->userable_id   = $a->id;
            $user->userable_type = $a->getMorphClass();
            $user->save();

            return $a->id;
        }

        return false;
    }

    public function updateWithInput()
    {
        // Update user attached to student
        $user = User::find($this->user_id);
        $user->email = Input::get('email');
        $user->save();

        // perform actual password reset
        $user->resetPassword();

        // Update student
        return $this->update([
            'first'     => Input::get('first'),
            'middle'    => Input::get('middle'),
            'last'      => Input::get('last'),
            'birthdate' => Input::get('birthdate'),
            'phone'     => Input::get('phone'),
            'address'   => Input::get('address'),
            'city'      => Input::get('city'),
            'state'     => Input::get('state'),
            'zip'       => Input::get('zip'),
            'gender'    => Input::get('gender')
        ]);
    }
}
