<?php namespace Hdmaster\Core\Models\Staff;

use \User;
use \Student;
use Config;
use Validator;
use Input;

/**
 * This class describes both 'Admin' and 'Staff' users
 */

class Staff extends \Eloquent
{
    
    protected $fillable   = [
        'first',
        'middle',
        'last',
        'phone',
        'email',
        'user_id'
    ];
    protected $morphClass = 'Staff';
    protected $table = 'staff';

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
}
