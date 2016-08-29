<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffAccounts extends Migration
{

    private $admins = [
        'admin@dandsdiversifiedtech.lan'           => 'Default Admin',
        'pauld@dandsdiversifiedtech.lan'           => 'Paul Dorrance',
        'chrisp@dandsdiversifiedtech.lan'          => 'Chris Petrick',
        'timp@dandsdiversifiedtech.lan'            => 'Tim Petrick',
        'austenc@dandsdiversifiedtech.lan'         => 'Austen Cameron',
        'chads@dandsdiversifiedtech.lan'           => 'Chad Salois',
        'mandiv@dandsdiversifiedtech.lan'          => 'Mandi Vulk',
        'allorda@dandsdiversifiedtech.lan'         => 'Andy Allord',
        'teresaw@dandsdiversifiedtech.lan'         => 'Teresa Whitney',
        'junderwood@mail.ohio.diversifiedtech.lan' => 'Jennifer Underwood',
        'cmoses@mail.ohio.diversifiedtech.lan'     => 'Chris Moses'
    ];

    private $staff = [
        'donnac@dandsdiversifiedtech.lan'  => 'Donna Campbell',
        'naomiw@dandsdiversifiedtech.lan'  => 'Naomi Wolfe',
        'beckis@dandsdiversifiedtech.lan'  => 'Becki Smith',
        'karleew@dandsdiversifiedtech.lan' => 'Karlee Warren',
        'amyo@dandsdiversifiedtech.lan'    => 'Amy Owens',
        'juliep@dandsdiversifiedtech.lan'  => 'Julie Peterson'
    ];

    private $adminPassword = 'dsdt;86!';
    private $password = 'Headmaster15!';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $admin = Role::where('name', 'Admin')->first();
        $staff = Role::where('name', 'Staff')->first();

        // Create admin accounts
        foreach ($this->admins as $email => $name) {
            $this->createPerson($name, $email, $admin);
        }

        // Create staff accounts
        foreach ($this->staff as $email => $name) {
            $this->createPerson($name, $email, $staff);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Clear the admin and staff tables
        $userIds = Admin::all()->lists('user_id')->all() + Staff::all()->lists('user_id')->all();

        DB::table('admins')->truncate();
        DB::table('staff')->truncate();

        DB::table('users')->whereIn('id', $userIds)->truncate();
    }

    private function createPerson($name, $email, $role)
    {
        $split = explode(' ', $name);
        $first = $split[0];
        $last  = $split[1];

        // Create the user
        $user = new User;
        $username = $user->unique_username($last, $first);

        // Create a new user
        $user->email                 = $email;
        $user->username              = $username;

        $password = $role->name === 'Admin' ? $this->adminPassword : $this->password;

        // set password as what was passed in
        $user->password              = $password;
        $user->password_confirmation = $password;

        $user->confirmed = 1;
        $user->save();

        // Now add the role!
        $user->attachRole($role);

        // create Staff or Admin
        $currRole = $role->name;
        $newRecord = $currRole::create([
            'first'   => $first,
            'last'    => $last,
            'user_id' => $user->id
        ]);

        // now set the other side of the polymorphic relation
        $user->userable_type = $newRecord->getMorphClass();
        $user->userable_id   = $newRecord->id;
        $user->save();
    }
}
