<?php namespace Hdmaster\Core\Pdfs;

use \SkilltaskResponse;
use Hdmaster\Notifications\Flash;
use \Auth;
use \Config;
use \Lang;
use \TCPDF;

/**
 * Prints skill test forms using TCPDF
 */
class SkillPdf extends TCPDF
{

    private $clientName;
    private $blank = '_______________';

    public function __construct($orientation='P', $unit='mm', $format='LETTER', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        $this->SetAuthor('Headmaster');
        $this->SetKeywords('pdf, Headmaster');
        $this->SetHeaderMargin(0);
        $this->SetFooterMargin(0);

        $this->clientName = Config::get('core.client.name');
    }

    // Get rid of header and footer
    public function Header()
    {
    }
    public function Footer()
    {
        $this->setY($this->getY() - 16);
        $this->setFont('Courier', '', 11);
        $this->Cell(0, 0, 'Mark A for correct, B for incorrect and explain all B marks.', 0, 1, 'C');
    }

    /**
     * Prints out skill forms for a given event, expects event to have 
     * skill students already loaded
     */
    public function skills($event, $studentId = null)
    {
        $responses = null;
        $this->setTitle($this->clientName.' Skill Test');

        // if there is no skill attempts, show a warning
        if (empty($event->skillattempts)) {
            Flash::warning('No students have skills assigned for this event');
            return Redirect::route('events.edit', $event->id);
        }
        
        $this->setMargins(10, 10);

        // for each student in the event, add a page and print out task / steps
        foreach ($event->skillattempts as $attempt) {
            // only print for a single student?
            if (! empty($studentId) && ($studentId != $attempt->student_id)) {
                continue;
            }

            $stepCount      = 121;
            $taskCount      = 1;
            $responseSetups = $attempt->responses->lists('setup_id', 'skilltask_id')->all();
            $totalTasks     = $attempt->skilltest->tasks->count();
            $taskNames      = $attempt->skilltest->tasks->lists('title')->all();
            reset($taskNames);
            unset($taskNames[key($taskNames)]);

            // Display each task
            foreach ($attempt->skilltest->tasks as $task) {
                // grab a list of the task's setup_ids
                $taskSetups = $task->setups->lists('id')->all();

                // if any of this task's setups already are marked in a response
                if (! empty(array_intersect($taskSetups, $responseSetups)
                    && array_key_exists($task->id, $responseSetups))) {
                    // there's already a setup, we'll use it
                    $setupId = $responseSetups[$task->id];
                    $setup   = $task->setups->find($setupId);
                } else {
                    $setup = $task->setups->random(1);
                }

                // Add this to our $responses array in order to 
                // to insert into the database after the loop
                $responses[] = [
                    'skillattempt_id' => $attempt->id,
                    'skilltask_id'    => $task->id,
                    'student_id'      => $attempt->student_id,
                    'setup_id'        => $setup ? $setup->id : null,
                    'status'          => 'pending'
                ];

                $this->SetFont('Courier', 'B', '11');

                // Student and Test they're taking
                $this->addPage();
                $this->Cell(0, 0, $this->clientName.' Skill Test #'.$attempt->skillexam_id.' - '.' Version #'.$attempt->skilltest_id);
                $this->Ln();
                $this->Cell(0, 5, Lang::choice('core::terms.student', 1).': '.$attempt->student->commaName, 0, 1, '', false, '', 0, false, 'T', 'T');

                // Task Title
                $this->Cell(0, 0, $task->title, 'B', 1, 'C');
                $this->SetFont('Courier', '', '11');
                // ---------------------------------------------------------------------------

                $colWidth = 30;

                // Scenario
                $this->setFont('Courier', 'B', 11);
                $this->Cell($colWidth, 0, 'Scenario: ', 0, 0);
                $this->setFont('Courier', '', 11);
                $this->MultiCell(0, 0, $task->scenario, 0, 'L');

                // Note to TO
                if ($task->note) {
                    $this->setFont('Courier', 'B', 11);
                    $this->Cell($colWidth, 0, 'Note to TO: ', 0, 0);
                    $this->setFont('Courier', '', 11);
                    $this->MultiCell(0, 0, $task->note, 0, 'L');
                }

                // show other tasks
                if ($taskCount == 1) {
                    $this->setFont('Courier', 'B', 11);
                    $this->Cell($colWidth, 0, 'Other Tasks: ', 0, 0);
                    $this->setFont('Courier', '', 11);
                    $this->MultiCell(0, 0, implode(', ', $taskNames), 0, 'L');
                }

                // Setup
                if (! $task->setups->isEmpty()) {
                    $this->setFont('Courier', 'B', 11);
                    $this->Cell($colWidth, 0, 'Setup: ', 0, 0);
                    $this->setFont('Courier', '', 11);
                    $this->MultiCell(0, 0, $setup->setup, 0, 'L');
                }

                // Only show this stuff for the first task
                if ($taskCount == 1) {
                    // Date / Start Time / End Time
                    $this->Cell(0, 14, 'Date:'.$this->blank.'  Test Start Time:'.$this->blank.'  Test End Time:'.$this->blank);

                    // Suggested Closure
                    $this->Ln(8);
                    $this->setFont('Courier', 'B', 11);
                    $this->Cell(0, 10, 'Suggested closure at the end of the '.$totalTasks.' tasks', 0, 1, 'C');
                    $this->setFont('Courier', '', 11);
                    $this->MultiCell(0, 10, 'Less than 45 minutes -- "You have ___ minutes remaining. You just completed the tasks of '.$task->title.', and ... (read from tasks above.) Are you finished?" When you get the yes, say, "Thank you for coming." Direct the '.Lang::choice('core::terms.student', 1).' to the holding area.', 0, 'L');
                    $this->MultiCell(0, 0, 'When the 45 minute audible buzzer sounds stop the test by saying, "Your alotted time has elapsed. Thank you for showing us your skill demonstrations today." Direct the '.Lang::choice('core::terms.student', 1).' to the holding area.', 0, 'L');
                }

                // ---------------------------------------------------------------------------
                $this->setY($this->getY() - 4);
                $this->Cell(0, 0, '', 'B', 0, 'C');
                $this->Ln(8);

                // Task Steps
                foreach ($task->steps as $step) {
                    $this->Cell(15, 7, '__'.$stepCount.'.  ', 0, 0, '', false, '', 0, false, 'T', 'T');
                    $this->MultiCell(0, 7, $step->paper, 0, 'L', false, 1, '', '', true, 0, true);
                    $this->Ln(2);
                    $stepCount++;
                }

                $taskCount++;
            } // foreach tasks

            // Mark this skill attempt as printed
            $attempt->printed_by = Auth::id();
            $attempt->save();
        } // foreach attempts

        // Create or update skilltask_response records
        if ($responses) {
            foreach ($responses as $response) {
                SkilltaskResponse::updateOrCreate($response);
            }
        }

        // show pdf
        return $this->Output();
    }
}
