<?php namespace Hdmaster\Core\Models\Permission;

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
    protected $fillable = ['name', 'display_name'];

    public function getReadableNameAttribute()
    {
        $name = $this->name;
        $name = str_replace('.', ' - ', $name);
        $name = str_replace('_', ' ', $name);
        return ucwords($name);
    }
}
