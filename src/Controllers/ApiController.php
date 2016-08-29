<?php namespace Hdmaster\Core\Controllers;

use Input;
use Config;
use \Student;
use \Testattempt;
use \Testform;
use \Skillattempt;
use \Skilltest;
use \SkilltaskResponse;
use \Pendingscore;

class ApiController extends BaseController
{

    /* Parameters that should be sent:
        * login
        * pin
    
        // (below are from the scanform)
        * student_id
        * knowledge_attempt
        * knowledge_answers like    1,A|2,C|3,A|...
        * skill_attempt
        * skill_answers
     */
    
    public function index()
    {
        // internal function vars
        $messages = array(); // message(s) to return at the end

        // Variables from request
        $login              = Input::get('login');
        $pin                = Input::get('pin');
        $studentId          = Input::get('student_id');
        $knowledgeAttemptId = Input::get('knowledge_attempt');
        $knowledgeAnswers   = rtrim(Input::get('knowledge_answers'), '|');
        $skillAttemptId     = Input::get('skill_attempt');
        $skillAnswers       = rtrim(Input::get('skill_answers'), '|');
        
        // models
        $student            = empty($studentId) ? null : Student::find($studentId);
        $knowledgeAttempt   = empty($knowledgeAttemptId) ? null : Testattempt::withRescheduled()->find($knowledgeAttemptId);
        $skillAttempt       = empty($skillAttemptId) ? null : Skillattempt::withRescheduled()->find($skillAttemptId);

        // Make sure they're authenticated
        if (! $this->authenticate($login, $pin)) {
            return 'ERROR - incorrect API login info.';
        }

        // check that we have a valid student
        if (! $student) {
            $messages[] = 'ERROR - student with identification # '.$studentId.' does not exist.';
        }

        // check that we have at least one type of attempt
        if ((! $knowledgeAttempt) && (! $skillAttempt)) {
            $messages[] = 'ERROR - test attempt(s) on scanform do not exist.';
        }

        // If we have any messages, assume we've got an error and return them now
        if (! empty($messages)) {
            return implode('|', $messages);
        }

        // Handle KNOWLEDGE attempt
        if ($knowledgeAttempt && $knowledgeAnswers) {
            $messages = array_merge($messages, $this->updateKnowledge($student, $knowledgeAttempt, $knowledgeAnswers));
        }

        // Handle SKILL attempt
        if ($skillAttempt && $skillAnswers) {
            $messages = array_merge($messages, $this->updateSkill($student, $skillAttempt, $skillAnswers));
        }

        // return the list of messages as a pipe-delimited string 
        return implode('|', $messages);
    }


    /**
     * Update a knowledge attempt and create a pending score record for it
     * @param   Student         $student
     * @param   Testattempt     $attempt
     * @param   string          $answers
     * @return  array
     */
    private function updateKnowledge($student, $attempt, $answers)
    {
        $dbKnowledge = array();

        // do final check to ensure student.id === testattempt.student_id
        if ($student->id != $attempt->student_id) {
            return ['ERROR - student ID does not match given test attempt ID.'];
        }

        // has this test been rescheduled?
        if ($attempt->status == 'rescheduled') {
            return ['ERROR - attempt #'.$attempt->id.' for student #'.$attempt->student_id.' has been re-scheduled.'];
        }

        \Log::info($answers);
        \Log::info('Scanned paper test answers for attempt #' . $attempt->id);

        // update the test attempt / answers
        $updated = $attempt->updateAnswersFromPiped($answers, true, true);
        
        // make a pendingscore record
        $record = Pendingscore::updateOrCreate([
            'scoreable_type' => $attempt->getMorphClass(),
            'scoreable_id'   => $attempt->id
        ]);

        if ($record) {
            return ['SUCCESS - Pending knowledge score added for '.$student->commaName];
        }

        return ['ERROR - could not process knowledge test for '.$student->commaName];
    }

    /**
     * Update a knowledge attempt and create a pending score record for it
     * @param   Student             $student
     * @param   Skillattempt        $attempt
     * @param   string              $answers
     * @return  array
     */
    private function updateSkill($student, $attempt, $answers)
    {
        // messages to return
        $messages = [];

        // do final check to ensure student.id === skillattempt.student_id
        if ($student->id != $attempt->student_id) {
            return ['ERROR - student ID does not match given skill test attempt ID.'];
        }
        
        // has this test been rescheduled? nothing to do here!
        if ($attempt->status == 'rescheduled') {
            return ['ERROR - skill attempt #'.$attempt->id.' for student #'.$attempt->student_id.' has been re-scheduled.'];
        }

        \Log::info($answers);
        \Log::info('Scanned paper test answers for skill attempt #' . $attempt->id);

        // update the test attempt / answers
        $updated = $attempt->updateAnswersFromPiped($answers, true);

        // score the skill attempt
        $updatedAttempt = Skillattempt::findOrFail($attempt->id);
        $updatedAttempt->score(true);

        // make a pendingscore record
        $record = Pendingscore::updateOrCreate([
            'scoreable_type' => $attempt->getMorphClass(),
            'scoreable_id'   => $attempt->id
        ]);

        if ($record) {
            $messages[] = 'SUCCESS - Pending skill score added for '.$student->commaName;
            return $messages;
        }

        $messages[] ='ERROR - could not process skill test for '.$student->commaName;
        return $messages;
    }

    /**
     * Compares a given login and pin value to the credentials in config
     * @param  $login 
     * @param  $pin   
     * @return boolean       
     */
    private function authenticate($login, $pin)
    {
        return ($login == Config::get('core.scan.login') && $pin == Config::get('core.scan.pin'));
    }
}
