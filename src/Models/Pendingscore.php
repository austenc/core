<?php namespace Hdmaster\Core\Models\Pendingscore;

class Pendingscore extends \Eloquent
{
    protected $fillable = ['scoreable_id', 'scoreable_type', 'expected_outcome'];
    protected $with = ['scoreable']; // always load this relation

    public function scoreable()
    {
        return $this->morphTo();
    }

    public function getTypeAttribute()
    {
        return str_contains(strtolower($this->scoreable_type), 'skill') ? 'Skill' : 'Knowledge';
    }
}
