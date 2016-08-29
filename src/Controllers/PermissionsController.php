<?php namespace Hdmaster\Core\Controllers;

use Input;
use View;
use Redirect;
use \Role;
use \Permission;
use Hdmaster\Core\Notifications\Flash;

class PermissionsController extends BaseController
{

    /**
     * Display a listing of the resource.
     * GET /permissions
     *
     * @return Response
     */
    public function index()
    {
        // get a list of all roles
        return View::make('core::permissions.index')->withRoles(Role::all());
    }

    public function editRole($id)
    {
        $role = Role::with('perms')->find($id);

        return View::make('core::permissions.edit_role')->with([
            'role'           => $role,
            'permissions'    => Permission::all(),
            'hasPermissions' => $role->perms->lists('id')->all()
        ]);
    }

    public function updateRole($id)
    {
        $role = Role::find($id);

        $role->perms()->sync(Input::get('permissions'));

        Flash::success('Permissions updated.');
        return Redirect::route('permissions.edit_role', $id);
    }
}
