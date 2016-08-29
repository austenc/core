<?php namespace Hdmaster\Core\Models\Ada;

use Validator;
use \Student;

class Ada extends \Eloquent
{
    protected $fillable   = ['name', 'paper_only', 'test_type', 'abbrev', 'extend_time'];

    public static $rules = [
        'name'   => 'required',
        'abbrev' => 'required'
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_ada')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }

    public function validate()
    {
        $rules = static::$rules;

        $v = Validator::make($this->attributes, $rules);

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();
        return false;
    }
}
