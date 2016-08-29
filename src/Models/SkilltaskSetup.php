<?php namespace Hdmaster\Core\Models\SkilltaskSetup;

use \Skilltask;

class SkilltaskSetup extends \Eloquent
{
    protected $fillable = ['skilltask_id', 'setup', 'comments'];

    public function task()
    {
        return $this->belongsTo(Skilltask::class);
    }
}
