<?php namespace Hdmaster\Core\Commands;

use Config;
use DB;
use \Role;
use \Testevent;
use \User;
use Hdmaster\Core\Notifications\Notification;
use Illuminate\Console\Command;

class EventEndCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tmu:events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for un-ended test events and sends notification to staff about any outstanding events.';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Checking for outstanding unended events...');

        // relative dates to today
        $threeDaysAgo = date('Y-m-d', strtotime('-3 days'));
        $oneMonthAgo  = date('Y-m-d', strtotime('-30 days'));

        // Events at least 3 days old (max 1 month old) which haven't been ended
        // and are NOT paper events (these shouldn't need to be ended)
        $events = Testevent::where('test_date', '<=', $threeDaysAgo)
            ->where('test_date', '>=', $oneMonthAgo)
            ->where('ended', null)
            ->where('is_paper', false)
            ->orderBy('test_date', 'desc')->limit(100)->get();

        // Notify staff about any events that need to be manually ended
        if (! $events->isEmpty()) {
            $notification = new Notification;

            // grab id's of roles 
            $roles = Role::whereIn('name', ['Admin', 'Staff'])->lists('id')->all();
            // get user id's who have these role id's 
            $userIds = DB::table('assigned_roles')->whereIn('role_id', $roles)->lists('user_id')->all();
            // finally, grab the user model records
            $users = User::whereIn('id', $userIds)->get();

            // Send notification about each event to all user/staff members
            foreach ($events as $e) {
                // send notification to all admin/staff users
                $notification->broadcast($users, [
                    'type'   => 'warning',
                   'subject' => 'Unended Event',
                   'body'    => 'core::notifications.unended_event',
                   'params'  => ['event' => $e]
                ]);

                // give some feedback for the command run
                $this->info('Unended Event notifications sent for event #' . $e->id);
            }
        } else {
            $this->info('No unended events found.');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
