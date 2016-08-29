<?php namespace Hdmaster\Core\Helpers;

use Input;
use Flash;
use Auth;
use Config;
use Exam;
use Skillexam;
use Skilltask;
use SkilltaskStep;
use Testitem;
use Stat;
use Mail;
use Vocab;
use Distractor;
use Subject;
use Enemy;
use Training;
use Instructor;
use User;
use Role;

class Importer
{

    /**
     * Parse Input datafile containing skill tasks
     */
    public function importSkillTasks($skillId)
    {
        if (! $this->_testInputfile()) {
            return false;
        }

        $c       = 0;    // created
        $d         = 0;    // duplicate
        $records = [];

        // read datafile
        $file  = Input::file('file');
        $items = file($file->getRealPath());

        // get exam
        $skillexam = Skillexam::find($skillId);
        if (is_null($skillexam)) {
            Flash::danger('Could not find Skill Exam.');
            return false;
        }

        // load all tasks 
        // used to check if task already exists to protect against double import
        $allTasks = $this->_setupTaskMatching();

        foreach ($items as $i => $item) {
            $data           = explode('|', $item);
            $legacyId       = $data[0];
            $title          = $data[1];
            $status         = $data[2];
            $weight         = $data[3];
            $legacyParentId = $data[4];
            $longTitle      = $data[5];
            // attempt trim off "Scenario"
            $scenario       = (substr($data[6], 0, 8) == "Scenario") ? substr($data[6], 9) : $data[6];

            $taskInfo = [
                'legacy_id'    => $legacyId,
                'title'         => $title,
                'long_title'    => $longTitle,
                'scenario'      => $scenario,
                'weight'        => $weight,
                'status'        => $status
            ];

            // does task already exist?
            $compKey = implode('|', [$legacyId, $weight, $status]);
            if (array_key_exists($compKey, $allTasks)) {
                $records['invalid']['existing'][] = $taskInfo;
                $d++;
                continue;
            }

            // look up parent by legacy id
            if (! empty($legacyParentId)) {
                $parentTask = Skilltask::where('legacy_id', $legacyParentId)->first();

                // if found task by legacy id, set db key to create parent/child relation
                if (! is_null($parentTask)) {
                    $taskInfo['parent_id'] = $parentTask->id;
                }
            }

            // create skill task
            $task = Skilltask::create($taskInfo);
            $skillexam->tasks()->attach([$task->id]);

            $records[$status][] = $taskInfo;
            $c++;
        }

        // send email?
        $params['sendTo']      = Config::get('workbench.email');
        $params['sendName']    = Config::get('workbench.name');
        $params['sendSubject'] = 'Import Skill Tasks Report';
        $this->_sendReport($records, 'core::emails.imports.skills.tasks.report', $params);

        Flash::success('Imported '.$c.' Tasks for Skillexam '.$skillexam->name.'.');
        return true;
    }

    /**
     * Parse Input datafile containing skill steps
     */
    public function importSkillSteps($skillId)
    {
        if (! $this->_testInputfile()) {
            return false;
        }

        $r       = 0;    // needs review
        $c       = 0;    // total imported step count
        $d       = 0;   // duplicate steps
        $records = [];

        // read datafile
        $file  = Input::file('file');
        $items = file($file->getRealPath());

        // get exam
        $skillexam = Skillexam::find($skillId);
        if (is_null($skillexam)) {
            Flash::danger('Could not find Skill Exam.');
            return false;
        }

        // create task map (legacy id key)
        $taskLegacyMap = Skilltask::whereNotNull('legacy_id')->lists('id', 'legacy_id')->all();

        // load all steps 
        // used to check if step already exists to protect against double import
        $allSteps = $this->_setupStepMatching();

        foreach ($items as $i => $item) {
            $data         = explode('|', $item);
            $legacyStepId = $data[0];
            $legacyTaskId = $data[1];
            $ordinal      = $data[2];
            $isKey        = $data[3];
            $weight       = $data[4];
            $outcome      = $data[5];
            $altDisplay   = $data[6];

            $stepInfo = [
                'weight'           => $weight,
                'is_key'           => $isKey,
                'ordinal'          => $ordinal,
                'expected_outcome' => $outcome,
                'alt_display'      => $altDisplay,
                'vinput_review'    => false
            ];

            // could we find a task by legacy_id? if not fail, a step must be attached to task
            if (! array_key_exists($legacyTaskId, $taskLegacyMap)) {
                Flash::warning('Skipping Step record - could not find Task by Legacy ID '.$legacyTaskId.': Line '.$i.'.');
                $records['invalid']['missingLegacyId'][] = $stepInfo;
                continue;
            }

            // get task from lookup map
            $taskId = $taskLegacyMap[$legacyTaskId];
            // add current task id to stepInfo before create
            $stepInfo['skilltask_id'] = $taskId;

            // assemble composite key for quick lookup if step already exists
            $compKey = implode('|', [$taskId, $weight, $ordinal]);

            // check if this step already exists (duplicate?)
            if (array_key_exists($compKey, $allSteps)) {
                // check outcome herewith levenshtein? 
                $records['invalid']['existing'][] = $stepInfo;
                $d++;    // duplicate step!
                continue;
            }

            // check for possible variable input
            if (strpos($item, '/') !== false ||
               strpos($item, '_') !== false ||
               strpos($item, 'Circle') !== false ||
               strpos($item, 'circle') !== false) {
                $records['review'][]       = $stepInfo;
                $stepInfo['vinput_review'] = true;
                $r++;
            }

            // create step
            $step = SkilltaskStep::create($stepInfo);

            $records['new'][] = $stepInfo;
            $c++;
        } // end FOREACH


        // send email?
        $params['sendTo']      = Config::get('workbench.email');
        $params['sendName']    = Config::get('workbench.name');
        $params['sendSubject'] = 'Import Skill Steps Report';
        $this->_sendReport($records, 'core::emails.imports.skills.steps.report', $params);
        
        // invalid records?
        if ($d > 0) {
            Flash::warning('Skipped '.$d.' duplicate Steps.');
        }

        // review steps? (possible unconverted variable input)
        if ($r > 0) {
            Flash::warning("Imported ".$r." Steps requiring your attention due to possible unconverted variable input. <a href='".route('steps.index')."'>Click here</a> to review these Steps.");
        }

        // created/new steps
        if ($c > 0) {
            Flash::success('Imported '.$c.' new Steps.');
        }

        return true;
    }

    /**
     * Parse Input datafile containing skill setups
     */
    public function importSkillSetups($skillId)
    {
        if (! $this->_testInputfile()) {
            return false;
        }

        // get exam
        $skillexam = Skillexam::find($skillId);
        if (is_null($skillexam)) {
            Flash::danger('Could not find Skill Exam.');
            return false;
        }

        // read datafile
        $file  = Input::file('file');
        $items = file($file->getRealPath());

        $n           = 0;
        $s           = 0;
        $r           = 0;
        $records     = [];
        $trimKeyword = "note to to";

        foreach ($items as $i => $item) {
            $data         = explode('|', $item);
            $setupId      = $data[0];
            $legacyTaskId = $data[1];
            // if NOTE begins with "note to to", trim
            $note         = substr(strtolower($data[2]), 0, 12) == $trimKeyword ? substr($data[2], 12) : $data[2];

            $setupInfo = [
                'legacy_id' => $legacyTaskId,
                'note'      => $note
            ];

            // could we find a task by legacy_id?
            $task = Skilltask::where('legacy_id', $legacyTaskId)->first();
            if (is_null($task)) {
                Flash::warning('Skipping Setup record - could not find Task by Legacy ID '.$legacyTaskId.': Line '.($i + 1).'.');
                $records['invalid']['missingLegacyId'][] = $setupInfo;
                continue;
            }

            // check for possible variable input
            /*if(strpos($item, 'write in the blank') !== false || 
               strpos($item, '_') !== false || 
               strpos($item, 'WebETest') !== false || 
               strpos($item, 'Circle') !== false || 
               strpos($item, 'circle') !== false ||
               strpos($item, 'left') !== false ||
               strpos($item, 'right') !== false)*/
            $reviewPattern = "/(webetest|circle|left|right)/i";
            if (preg_match($reviewPattern, $note) === true) {
                $records['review'][] = $setupInfo;
                $task->setup_review  = true;
                $r++;
            }

            // update task note to TestObserver
            $task->note = $note;
            $task->save();
            $n++;

            // check for setups
            if (array_key_exists(3, $data)) {
                $setups = explode(',', $data[3]);

                foreach ($setups as $i => $setup) {
                    $task->setups()->attach(['setup' => $setup]);
                    $s++;
                }
            }

            $records['new'][] = $setupInfo;
        }


        // send email?
        $params['sendTo']      = Config::get('workbench.email');
        $params['sendName']    = Config::get('workbench.name');
        $params['sendSubject'] = 'Import Skill Setups Report';
        $this->_sendReport($records, 'core::emails.imports.skills.setups.report', $params);

        Flash::success('Updated '.$n.' Notes to Test Observer for Skillexam '.$skillexam->name.'.');

        if ($s > 0) {
            Flash::success('Imported '.$s.' Setups for Skillexam '.$skillexam->name.'.');
        }

        // review tasks? (possible unconverted setup embedded in NOTE to TO)
        if ($r > 0) {
            Flash::warning("Imported ".$r." Setups requiring your attention due to possible unconverted Setup embedded in Note to Test Observer. <a href='".route('tasks.index')."'>Click here</a> to review these Tasks.");
        }


        return true;
    }

    /**
     * Import instructors
     */
    public function importInstructors($trainingId)
    {
        if (! $this->_testInputfile()) {
            return false;
        }

        $count     = 0;
        $duplicate = 0;
        $error     = 0;
        $records   = [];

        // get training
        $training = Training::find($trainingId);
        if (is_null($training)) {
            Flash::danger('Could not find Training.');
            return false;
        }

        // disable mass assignment guard for Instructor create
        \Eloquent::unguard();

        // read datafile
        $file = Input::file('file');
        $items = file($file->getRealPath());

        // get Instructor role
        $role = Role::where('name', '=', 'Instructor')->first();

        // 0 Training Program License
        // 1 Training Program Name
        // 2 RN License #
        // 3 Lastname
        // 4 Firstname
        // 5 Address
        // 6 Mailing Address
        // 7 City
        // 8 State
        // 9 Zip
        // 10 ???
        // 11 Email
        // 12 [D, A, R] Flag??
        // 13 expires?
        foreach ($items as $item) {
            $data = explode('|', $item);

            $trProgLicense = $data[0];
            $trProgName    = $data[1];
            $rnLicense     = $data[2];
            $last          = $data[3];
            $first         = $data[4];
            $address       = $data[5];
            $mailing       = $data[6];
            $city          = $data[7];
            $state         = $data[8];
            $zip           = $data[9];
            //$unknown     = $data[10];
            $email         = $data[11];
            $active        = $data[12] == 'R' || $data[12] == 'A' ? true : false;
            $expires       = ! empty($data[13]) ? date('Y-m-d', strtotime($data[13])) : null;

            $insInfo = [
                'first'     => $first,
                'middle'    => null,
                'last'      => $last,
                'birthdate' => null,
                'gender'    => null,
                'license'   => $rnLicense,
                'expires'    => $expires,
                'address'   => $address,
                'city'      => $city,
                'state'     => $state,
                'zip'       => $zip
            ];

            // find by this RN #
            $instructor = Instructor::with('teaching_trainings')->where('license', $rnLicense)->first();
            
            // instructor already exists
            if ($instructor) {
                // if not already teaching this training, add it
                if (! in_array($trainingId, $instructor->teaching_trainings->lists('id')->all())) {
                    $instructor->teaching_trainings()->attach($trainingId);
                }

                // update demographic info
                $instructor->first   = $first;
                $instructor->last    = $last;
                $instructor->address = $address;
                $instructor->city    = $city;
                $instructor->state   = $state;
                $instructor->zip     = $zip;
                $instructor->save();

                // set instructor email
                $instructor->user->email  = $email;
                $instructor->user->save();

                // is instructor active?
                if ($active !== true) {
                    // soft delete!
                    $instructor->user->delete();
                }

                $records['duplicate'][] = $insInfo;
                $duplicate++;
            }
            // new instructor
            else {

                // check required email
                if (empty($email)) {
                    $records['invalid']['missingEmail'][] = $insInfo;
                    $error++;
                    continue;
                }

                // check if existing email is already in use
                $existingUser = User::where('email', $email)->first();
                if (! is_null($existingUser)) {
                    $records['invalid']['exists'][] = $insInfo;
                    $error++;
                    continue;
                }

                // create user account
                $user           = new User;
                $username       = $user->unique_username($last, $first);
                $user->email    = $email;
                $user->username = $username;
                $newPwd                      = str_random(8);
                $user->password              = $newPwd;
                $user->password_confirmation = $newPwd;
                $user->confirmed = 1;
                $user->save();

                // check for any other problem with user account not created
                //   ie. malformed email  "TEST1@GMAIL.COM  OR  TEST2@YAHOO.COM"
                if (is_null($user->id)) {
                    $records['invalid']['error'][] = $insInfo;
                    $error++;
                    continue;
                }

                // Setup role
                $user->attachRole($role);

                // create instructor record
                $insInfo['user_id'] = $user->id;
                $instructor = Instructor::create($insInfo);

                // set polymorphic
                $user->userable_id   = $instructor->id;
                $user->userable_type = $instructor->getMorphClass();
                $user->save();

                // is instructor active?
                if ($active !== true) {
                    // soft delete!
                    $user->delete();
                }

                // attach teaching training
                $instructor->teaching_trainings()->attach($trainingId);

                $records['new'][] = $insInfo;
                $count++;
            }
        } // end FOREACH dataline


        // send email?
        $params['sendTo']      = Config::get('workbench.email');
        $params['sendName']    = Config::get('workbench.name');
        $params['sendSubject'] = 'Import Instructors Report';
        $this->_sendReport($records, 'core::emails.imports.instructors.report', $params);

        // records skipped due to error?
        if ($error > 0) {
            Flash::warning('Skipped '.$error.' invalid Instructors.');
        }
        // new instructors?
        if ($count > 0) {
            Flash::success('Imported '.$count.' new Instructors for Training '.$training->name.'.');
        }
        // duplicate instructors?
        if ($duplicate > 0) {
            Flash::success('Updated '.$duplicate.' existing Instructors for Training '.$training->name.'.');
        }
        
        return true;
    }


    /**
     * Import training facilities
     */
    public function importFacilities($trainingId)
    {
        if (! $this->_testInputfile()) {
            return false;
        }

        $count     = 0;
        $duplicate = 0;
        $error     = 0;
        $records   = [];

        // disable mass assignment guard for Facility create
        \Eloquent::unguard();

        // get training
        $training = Training::find($trainingId);
        if (is_null($training)) {
            Flash::danger('Could not find Training for Facility import.');
            return false;
        }

        // read datafile
        $file  = Input::file('file');
        $items = file($file->getRealPath());

        // Setup role
        $role = Role::where('name', '=', 'Facility')->first();

        // 0 Training Program License
        // 1 Training Program Name
        // 2 Administrator
        // 3 Address
        // 4 Mail Address ? [ie PO BOX 100]
        // 5 City
        // 6 State
        // 7 Zip
        // 8 Phone Num
        // 9 Email
        // 10 Expiration Date (review date, cirriculum must be review after this date) (staff editable)
        // 11 Duplicate License (ignore)
        // 12 [C, U, D, A] Flag
        foreach ($items as $item) {
            $data             = explode('|', $item);
            $license          = $data[0];
            $name             = $data[1];
            $admin            = $data[2];
            $address          = $data[3];
            $mailAddress      = $data[4];
            $city             = $data[5];
            $state            = $data[6];
            $zip              = $data[7];
            $phone            = $data[8];
            $email            = $data[9];
            $expires          = $data[10];
            $duplicateLicense = $data[11];
            // D = deactivate
            // C = create
            // U = update
            // A = activate
            $active = '';
            if ($data[12] == 'D') {
                $active = false;
            }    // deactivate! turn off user login
            elseif ($data['12'] == 'A' || $data['12'] == 'C') {
                $active = true;
            }        // activate! new accounts start as active
                                    // other option is U, update only!, dont change active/deactive flag

            // format expiration date
            $formatExp  = substr($expires, 0, 4)."/".substr($expires, 4, 2)."/".substr($expires, 6, 2);
            $formatDate = date('Y-m-d', strtotime($formatExp));

            // facility data
            $facilityInfo = [
                'name'          => $name,
                'license'       => '',            // testmaster license
                'actions'       => 'Training',
                'administrator' => $admin,
                'don'           => null,
                'phone'         => $phone,
                'fax'           => null,
                'timezone'      => null,
                'address'       => $address,
                'city'          => $city,
                'state'         => $state,
                'zip'           => $zip,
                'comments'      => 'Imported '.date('m/d/Y'),
                'expires'       => $formatDate,
                'mail_address'  => empty($mailAddress) ? null : $mailAddress
            ];

            // find Facility by license
            $facility = Facility::where('license', $license)->first();

            if ($facility) {
                // update this facility!
                $facility->name          = $name;
                $facility->administrator = $admin;
                $facility->phone         = $phone;
                $facility->address       = $address;
                $facility->city          = $city;
                $facility->state         = $state;
                $facility->zip           = $zip;
                $facility->expires       = $expires;

                // update user email
                $facility->user->email = $email;
                $facility->user->save();

                // is facility active?
                if ($active === false) {
                    // soft delete!
                    $facility->user->delete();
                }

                $records['duplicate'][] = $facilityInfo;
                $duplicate++;

                continue;
            } else {
                // check required email
                if (empty($email)) {
                    $records['invalid']['missingEmail'][] = $facilityInfo;
                    $error++;
                    continue;
                }
                // check if existing email is already in use
                $existingUser = User::where('email', $email)->first();
                if (! is_null($existingUser)) {
                    $records['invalid']['existing'][] = $facilityInfo;
                    $error++;
                    continue;
                }

                // create user account
                $user                        = new User;
                $username                    = $user->unique_username($name);
                $user->email                 = $email;
                $user->username              = $username;
                $newPwd                      = str_random(8);
                $user->password              = $newPwd;
                $user->password_confirmation = $newPwd;
                $user->confirmed             = 1;
                $user->save();

                // attach facility role
                $user->attachRole($role);

                // check for any other problem with user account not created
                if (is_null($user->id)) {
                    $records['invalid']['error'][] = $facilityInfo;
                    $error++;
                    continue;
                }

                // create new facility
                $facilityInfo['user_id'] = $user->id;
                $facility = Facility::create($facilityInfo);

                $user->userable_id   = $facility->id;
                $user->userable_type = $facility->getMorphClass();
                $user->save();

                // only change active flag if its not an UPDATE 
                if ($active === false) {
                    $user->delete();
                }

                $records['new'][] = $facilityInfo;
                $count++;
            }
        } // end FOREACH data line


        // send email?
        $params['sendTo']      = Config::get('workbench.email');
        $params['sendName']    = Config::get('workbench.name');
        $params['sendSubject'] = 'Import Facility Report';
        $this->_sendReport($records, 'core::emails.imports.facilities.report', $params);

        // facility records with a problem
        if ($error > 0) {
            Flash::warning('Skipped '.$error.' invalid Facilities.');
        }
        // newly created facilities
        if ($count > 0) {
            Flash::success('Imported '.$count.' new Facilities.');
        }
        // duplicate facility found, updated
        if ($duplicate > 0) {
            Flash::success('Updated '.$duplicate.' existing Facilities.');
        }

        return true;
    }


    /**
     * Parse Input datafile containing knowledge items for specific Exam (via $examId)
     */
    public function importTestitems($examId)
    {
        if (! $this->_testInputfile()) {
            return false;
        }

        $count      = 0;
        $duplicate  = 0;
        $allEnemies = array();

        // get exam
        $exam = Exam::find($examId);
        if (is_null($exam)) {
            Flash::danger('Could not find Exam.');
            return false;
        }

        // disable mass assignment guard for Testitem create
        \Eloquent::unguard();

        // read datafile
        $file = Input::file('file');
        $items = file($file->getRealPath());


        //idn,stem,# dist,correct,subject,4 distractors,comments,rationale,status,notwiths,pvalue,discrimination,difficulty,guessing,angoff,clients
        //  0,   1,   2,      3,      4,      5,6,7,8,       9,       10,    11,      12,    13,            14,        15,      16,    17      18
        foreach ($items as $item) {
            $data           = explode('|', $item);

            $itemNumber     = $data[0];                                    // num 51099 = [Item# 099, Cat# 51]
            $stem           = $data[1];
            $numDist        = $data[2];                                    // tells us how many distractors to expect
            $answer         = $data[3];                                // Answer -- by distractor # relative to single item
            $subject        = $data[4];
            $distractors    = array($data[5], $data[6], $data[7], $data[8]);
            $comments       = empty($data[9]) ? '' : $data[9];
            $rationale      = empty($data[10]) ? '' : $data[10];
            $status         = ($data[11] == 'M') ? 'active' : 'draft';    // status = [M: active, Z: inactive/omitted/draft]	
            $enemies        = explode(',', $data[12]);                    // array of item_numbers specifying enemies
            $pvalue         = $data[13];                                // Item PValue
            $discrimination = $data[14];                                // Discrimination Param
            $difficulty     = $data[15];                                // Difficulty Param
            $guessing       = $data[16];                                // Guessing Param
            $angoff         = $data[17];                                // Angoff			
            $oldClients     = empty($data[18]) ? '' : $data[18];        // comma separated string: AZ,MT,OH,ND
            $minPbs         = empty($data[19]) ? null : $data[19];        // Minimum PBS
            $clients        = $this->_mapClients($oldClients);
            //$insertExams    = $this->_mapClientsExams($oldClients);     // Old clients now map to an exam in our new system

            // attempt to find testitem by legacy id
            $testitem = Testitem::where('number', $itemNumber)->first();

            // existing testitem found! update existing
            if ($testitem) {
                // Not too much to do here, we just want to make sure that the exam_testitems table has a record for this item at least.
                // We do not want to mark any other exam id's if the item already exists because someone may have edited an 
                // item's exam relationships post-import from a previous import

                /*if( ! empty($insertExams) && is_array($insertExams))					
                    $this->_importAddExamItems($testitem->id, $insertExams, $subject);
                else
                    $this->_importAddExamItems($testitem->id, array($examId), $subject);*/


                $this->_importAddExamItems($testitem->id, array($examId), $subject);

                $duplicate++;

                continue;
            }
            // existing testitem not found! create new
            else {
                $newItem = [
                    'number'           => (int) $itemNumber,
                    'stem'             => $stem,
                    'answer'           => $answer,
                    'user_id'          => Auth::user()->id,
                    'derivative_of'    => 0,
                    'weight'           => 1,            // default weight 1
                    'status'           => $status,
                    'auth_source'      => '',
                    'comments'         => $comments,
                    'cognitive_domain' => 'Knowledge'    // Knowledge | Comprehension | Application
                ];

                // create knowledge testitem
                $testitem = Testitem::create($newItem);

                if ($testitem) {
                    // DISTRACTORS
                    // add distractors corresponding to new testitem and mark the answer in testitems field
                    $this->_importAddItemDistractors($testitem->id, $distractors, $answer);

                    // if the insertExams is an array we want to make sure this exam is included no matter what!					
                    /*if(is_array($insertExams))
                    {
                        if( ! in_array($examId, $insertExams))
                        {
                            $insertExams[] = $examId;
                        }
                    }*/

                    // EXAM_TESTITEMS
                    // add exam_testitems table record
                    /*if( ! empty($insertExams) && is_array($insertExams))					
                    {
                        $this->_importAddExamItems($testitem->id, $insertExams, $subject);
                    }
                    else
                    {
                        $this->_importAddExamItems($testitem->id, array($examId), $subject);
                    }*/


                    // connect testitem to client+exam
                    $this->_importAddExamItems($testitem->id, array($examId), $subject);

                    if (empty($oldClients)) {
                        continue;
                    }

                    // STATS
                    // add any stats corresponding to this testitem
                    if (! empty($pvalue)) {
                        Stat::create([
                            'testitem_id'    => $testitem->id,
                            'count'          => 0,
                            'difficulty'     => $difficulty,
                            'discrimination' => $discrimination,
                            'guessing'       => $guessing,
                            'pvalue'         => $pvalue,
                            'angoff'         => $angoff,
                            'pbs'            => $minPbs
                        ]);
                    }

                    // RATIONALE / VOCAB
                    if (! empty($rationale)) {
                        $vocabs = explode(',', $rationale);
                        
                        foreach ($vocabs as $vocabName) {
                            $vocab = Vocab::where('word', $vocabName)->first();

                            if (is_null($vocab)) {
                                $vocab = Vocab::create(['word' => $vocabName]);
                            }

                            $testitem->vocab()->attach($vocab->id);
                        }
                    }

                    // ENEMIES
                    // add enemies (or try!) to use later
                    if (! empty($enemies)) {
                        $allEnemies[$testitem->id] = $enemies;
                    }

                    // count newly created testitem
                    $count++;
                } // end IF testitem created
            } // end ELSE testitem doesnt exist
        } // end FOREACH item


        // we want to wait until this new batch of items have new item records created -- just so if an enemy appears later in this input file then 
        // Add enemy records!
        if (! empty($allEnemies)) {
            foreach ($allEnemies as $newEnemyId => $enemies) {
                foreach ($enemies as $itemNumber) {
                    // get testitem
                    $item = Testitem::where('number', $itemNumber)->first();

                    if (! is_null($item)) {
                        Enemy::firstOrCreate([
                            'testitem_id' => $item->id,
                            'enemy_id'    => $newEnemyId
                        ]);
                    }
                }
            }
        }


        // flash status here
        if ($duplicate > 0 && $count > 0) {
            $msg = 'Imported '.$count.' new and '.$duplicate.' existing testitems for Exam <strong>'.$exam->name.'</strong>.';
        } elseif ($count > 0) {
            $msg = 'Imported '.$count.' new testitems for Exam <strong>'.$exam->name.'</strong>.';
        } else {
            $msg = 'Imported '.$duplicate.' existing testitems for Exam <strong>'.$exam->name.'</strong>.';
        }
        Flash::success($msg);

        return true;
    }

    

    /**
     * Add distractors to for a testitem, also update testitem answer with distractor id
     */
    private function _importAddItemDistractors($itemId, $distractors, $answer)
    {
        if (! is_numeric($itemId) || ! is_array($distractors)) {
            return false;
        }

        // get testitem
        $testitem = Testitem::find($itemId);
        if (is_null($testitem)) {
            return false;
        }

        $answersLookup = array_flip(array('A', 'B', 'C', 'D', 'E'));
        $answer        = isset($answersLookup[$answer]) ? $answersLookup[$answer] : 0;
        $answerId      = 0;

        // we need to do this in a loop to check for the answer / get the correct distractor_id for marking the test item
        foreach ($distractors as $k => $d) {
            $newDistractor = [
                'testitem_id' => $testitem->id,
                'content'     => $d,
                'ordinal'     => $k
            ];

            $dist = Distractor::create($newDistractor);
            
            // this the answer? save this to update the test item after this
            if ($k === $answer) {
                $answerId = $dist->id;
            }
        }

        // Now that distractors in database, update the test items
        $testitem->answer = $answerId;
        $testitem->save();
    }


    private function _mapClients($clientStr)
    {
        if (empty($clientStr)) {
            return $clientStr;
        }

        $oldClients     = array_map('trim', explode(',', $clientStr));
        $newClientsMap = [];
        /*$newClientsMap = array(
            'IC' => 'IA',
            'IL' => 'IA',
            'IP' => 'IA'
        );*/


        return implode(',', array_unique($newClientsMap));
    }

    /**
     * Construct special lookup array with keys for quick lookup if step exists
     */
    private function _setupStepMatching()
    {
        $steps = SkilltaskStep::all();
        $f     = [];

        foreach ($steps as $i => $step) {
            $key     = implode('|', [$step->skilltask_id, $step->weight, $step->ordinal]);
            $f[$key] = $step;
        }

        return $f;
    }

    /**
     * Construct special lookup array with keys for quick lookup if task exists
     */
    private function _setupTaskMatching()
    {
        $tasks = SkilltaskStep::all();
        $f     = [];

        foreach ($tasks as $i => $task) {
            $key     = implode('|', [$step->skilltask_id, $step->weight, $step->ordinal]);
            $f[$key] = $task;
        }

        return $f;
    }

    /**
     * Map old testmaster clients to Exam (in our system)
     */
    /*private function _mapClientsExams($clientStr)
    {
        if(empty($clientStr))
        {
            return $clientStr;
        }

        $oldClients = array_map('trim', explode(',', $clientStr));
        
        $map_to_exams    = array(
            'IC' => 'HSP',
            'IL' => 'CLP',
            'IP' => 'PSP'
        );
        $mapToExams = array();

        $examsToInsert = array();

        foreach($oldClients as $client)
        {
            if(array_key_exists($client, $mapToExams))
            {
                // lookup knowledge exam with this client
                $exam = Exam::where('abbrev', $client)->first();

                if($exam)
                {
                    $examsToInsert[] = $exam->id;
                }	
            }
        }
        
        return $examsToInsert;
    }*/

    /**
     * Add a testitem to the exam_testitems lookup table
     */
    private function _importAddExamItems($itemId, $examIds, $oldSubjectNumber)
    {
        if (! is_numeric($itemId) || empty($examIds) || ! is_array($examIds)) {
            return false;
        }

        $subject = new Subject;
        $map     = $subject->clientSubjectMap();

        foreach ($examIds as $examId) {
            $examItem = [
                'subject_id' => null,
                'client'     => Config::get('core.client.abbrev')
            ];

            // try to map it to the new subject
            if (array_key_exists($examId, $map)) {
                if (in_array($oldSubjectNumber, $map[$examId])) {
                    // gets the (autoinc) subject_id from legacy subject_number
                    // map is keyed like [examId][subjectId] => oldNumber
                    $examItem['subject_id'] = array_search($oldSubjectNumber, $map[$examId]);
                }
            }

            // attach the testitem to exam
            if (isset($examItem['subject_id'])) {
                Exam::find($examId)->testitems()->attach($itemId, $examItem);
            }
        }
    }

    /**
     * Checks for correct input file
     */
    public function _testInputfile()
    {
        // make sure there is a posted file to work with!
        if (! Input::hasFile('file')) {
            Flash::danger('Missing datafile.');
            return false;
        }

        // uploaded datafile
        $file = Input::file('file');

        // get the file extension and make sure it is .txt
        $split = explode('.', $file->getClientOriginalName());
        $ext   = end($split);
        if (strtolower($ext) !== 'txt') {
            Flash::danger('Invalid datafile extension.');
            return false;
        }

        return true;
    }

    public function _sendReport($records, $view, $params)
    {
        if (! empty($records)) {
            // email report here
            $sendTo   = array_key_exists('sendTo', $params) ? $params['sendTo'] : Config::get('workbench.email');
            // use name to refer to email owner
            $sendName = array_key_exists('sendName', $params) ? $params['sendName'] : Config::get('workbench.name');
            // email subject
            $subject  = array_key_exists('sendSubject', $params) ? $params['sendSubject'] : 'Import Report';

            Mail::send($view, ['records' => $records], function ($message) use ($sendTo, $sendName, $subject) {
                $message
                    ->to($sendTo, $sendName)
                    ->subject($subject);
            });

            Flash::info('Sent Import report to '.$sendTo.'.');
        }
    }
}
