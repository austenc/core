<?php namespace Hdmaster\Core\Traits;

trait Attainable
{

    public function getNameWithAbbrevAttribute()
    {
        return $this->name.'<br><small>'.$this->abbrev.'</small>';
    }
    public function getCreatedAtAttribute()
    {
        return date('m/d/Y h:i A', strtotime($this->attributes['created_at']));
    }
    public function getUpdatedAtAttribute()
    {
        return date('m/d/Y h:i A', strtotime($this->attributes['updated_at']));
    }
}
