<?php namespace Hdmaster\Core\Models\PrintProfile;

use \User;

class PrintProfile extends \Eloquent
{
    
    protected $fillable = ['user_id', 'scanform_v', 'scanform_h'];

    /**
     * A print profile has one user
     *
     * @return Relation
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
