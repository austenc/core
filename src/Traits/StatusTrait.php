<?php namespace Hdmaster\Core\Traits;

use Auth;
use View;
use Lang;
use Input;
use Config;
use Session;
use Redirect;
use Response;
use Request;
use \Sorter;
use DB;
use URL;
use Formatter;
use Illuminate\Support\Collection as Collection;
use Hdmaster\Core\Notifications\Flash;
use Hdmaster\Core\Controllers\BaseController;
use \Log;

trait StatusTrait
{

    private static $curHold;
    private static $curLock;

    public function processStudentStatus()
    {
        $updated = true;

        // current account status
        $status    = $this->status;
        $statusArr = explode(",", $status);

        // requested status updates
        $setArr = Input::get('status');

        $this->updateStatus($setArr);

        // no status set? 
        // resolve all locks/holds
        if (is_null($setArr)) {
            // unset any active holds
            self::resolveHolds();
            // unset any active locks
            self::resolveLocks();

            $this->status = $this->isActive ? 'active' : 'archive';
            $this->save();

            return true;
        }
        // at least 1 status (hold/lock)
        else {
            // holds
            if (in_array('hold', $statusArr) || in_array('hold', $setArr)) {
                if (Input::get('holdreason') == "" || Input::get('holdinstructions') == "") {
                    if (Input::get('holdinstructions') == "") {
                        Flash::warning("Instructions to resolve hold required.");
                    }

                    if (Input::get('holdreason') == "") {
                        Flash::warning("Reason for account hold required.");
                    }

                    $updated = false;
                } else {
                    self::$curHold = DB::table('student_holds')->where('student_id', '=', $this->id)->where('hold_status', '=', 'active')->first();
                    
                    if (count(self::$curHold) > 0) {
                        self::updateStudentHold();
                    } else {
                        self::createStudentHold();
                    }
                }
            }

            // locks
            if (in_array('locked', $statusArr) || in_array('locked', $setArr)) {
                if (Input::get('lockreason') == "" || Input::get('lockinstructions') == "") {
                    if (Input::get('lockinstructions') == "") {
                        Flash::warning("Student instructions how student can resolve this lock is required.");
                    }

                    if (Input::get('lockreason') == "") {
                        Flash::warning("Reason for locking this students account is required.");
                    }

                    $updated = false;
                } else {
                    self::$curLock = DB::table('student_locks')->where('student_id', '=', $this->id)->where('lock_status', '=', 'active')->first();

                    if (count(self::$curLock) > 0) {
                        self::updateStudentLock();
                    } else {
                        self::createStudentLock();
                    }
                }
            }

            // update status
            $baseStatus   = $this->isActive ? ['active'] : ['archive'];
            $this->status = implode(',', array_merge($baseStatus, $setArr));
            $this->save();
        }

        return $updated;
    }

    public function getIsActiveAttribute()
    {
        return strpos($this->status, 'active') !== false;
    }

    public function getIsLockedAttribute()
    {
        return strpos($this->status, 'locked') !== false;
    }

    public function getIsHoldAttribute()
    {
        return strpos($this->status, 'hold') !== false;
    }

    public function getIsArchivedAttribute()
    {
        return strpos($this->status, 'archive') !== false;
    }

    public function getStudentHolds()
    {
        return DB::table('student_holds')->where('student_id', '=', $this->id)->orderBy('created_at', 'DESC')->get();
    }

    public function getStudentCurrentHold()
    {
        return DB::table('student_holds')->where('student_id', '=', $this->id)->where('hold_status', '=', 'active')->first();
    }

    /**
     * Set all active holds to resolved status
     */
    private function resolveHolds()
    {
        DB::table('student_holds')->where('student_id', $this->id)->where('hold_status', 'active')->update([
            'hold_status' => 'resolved',
            'updated_at'  => date("Y-m-d H:i:s")
        ]);
    }

    /**
     * Set all active locks to resolved status
     */
    private function resolveLocks()
    {
        DB::table('student_locks')->where('student_id', $this->id)->where('lock_status', 'active')->update([
            'lock_status' => 'resolved',
            'updated_at'  => date("Y-m-d H:i:s")
        ]);
    }

    private function updateStudentHold()
    {
        if (Input::get('clear_hold') == 1) {
            $holdStatus = 'resolved';
        } else {
            $holdStatus = 'active';
        }

        DB::table('student_holds')->where('id', '=', self::$curHold->id)->update(
            array('comments' => Input::get('holdreason'), 'instructions' => Input::get('holdinstructions'), 'hold_status' => $holdStatus, 'updated_at' => date("Y-m-d H:i:s"))
        );
    }

    private function createStudentHold()
    {
        DB::table('student_holds')->insert(
            array('student_id' => $this->id, 'comments' => Input::get('holdreason'), 'instructions' => Input::get('holdinstructions'), 'hold_status' => 'active', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date("Y-m-d H:i:s"))
        );
    }

    public function getStudentCurrentLock()
    {
        return DB::table('student_locks')->where('student_id', '=', $this->id)->where('lock_status', '=', 'active')->first();
    }
    
    public function getStudentLocks()
    {
        return DB::table('student_locks')->where('student_id', '=', $this->id)->orderBy('created_at', 'DESC')->get();
    }

    public function getStudentInstructions()
    {
        return DB::table('student_locks')->where('student_id', '=', $this->id)->where('status', '=', 'active')->get();
    }

    private function updateStudentLock()
    {
        if (Input::get('clear_lock') == 1) {
            $lockStatus = 'resolved';
        } else {
            $lockStatus = 'active';
        }

        DB::table('student_locks')->where('id', '=', self::$curLock->id)->update(
            array('comments' => Input::get('lockreason'), 'instructions' => Input::get('lockinstructions'), 'lock_status' => $lockStatus, 'updated_at' => date("Y-m-d H:i:s"))
        );
    }

    private function createStudentLock()
    {
        DB::table('student_locks')->insert(
            array('student_id' => $this->id, 'comments' => Input::get('lockreason'), 'instructions' => Input::get('lockinstructions'), 'lock_status' => 'active', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'))
        );
    }

    /**
     * Updates a record status with hold/lock/etc while preserving if record is active/archived
     */
    public function updateStatus($newStatus)
    {
        // ensure we have an array
        $newStatus = is_array($newStatus) ? $newStatus : [$newStatus];

        // remove active/archive if accidentally added
        // archive status is handled by toggleStatus()
        $newStatus = array_diff($newStatus, ['active', 'archive']);

        // current record active or not
        $baseStatus = $this->isActive ? ['active'] : ['archive'];

        // new record status with active/archived preserved
        $newStatus = array_merge($baseStatus, $newStatus);

        $this->status = implode(',', $newStatus);
        return $this->save();
    }
}
