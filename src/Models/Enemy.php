<?php namespace Hdmaster\Core\Models\Enemy;

use \Testitem;

class Enemy extends \Eloquent
{
    
    protected $fillable = [];

    public function testitem()
    {
        return $this->belongsToMany('Testitem');
    }

    public function enemies()
    {
        return $this->hasManyThrough('Enemy', 'Testitem');
    }
}
