<?php namespace Hdmaster\Core\Controllers;

use \Admin;

class AdminsController extends StaffController
{

    public function __construct(Admin $admin)
    {
        parent::__construct($admin);

        $this->type = [
            'type'      => 'Admin',
            'routeBase' => 'admins'
        ];
    }

    public function index($type = 'Admin')
    {
        return parent::index($type);
    }
}
