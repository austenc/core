<?php namespace Hdmaster\Core\Models\Role;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    protected $fillable = ['name'];
}
