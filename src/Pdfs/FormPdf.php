<?php namespace Hdmaster\Core\Pdfs;

use \Lang;
use \Config;
use \TCPDF;

/**
 * Prints administrative forms using TCPDF
 */
class FormPdf extends TCPDF
{

    public function __construct($orientation='P', $unit='mm', $format='LETTER', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        $this->SetAuthor('Headmaster');
        $this->SetKeywords('pdf, Headmaster');
        $this->SetHeaderMargin(0);
        $this->SetFooterMargin(0);
    }

    // Get rid of header and footer
    public function Header()
    {
    }
    public function Footer()
    {
    }

    /**
     * Verification report used for paper tests
     */
    public function verification($event)
    {
        $students = $event->students;

        $this->SetTitle('Verification Report');
        $this->AddPage();
        $this->SetMargins(10, 10);
        $this->SetFont('Courier', 'B', 11);
        $this->Cell(0, 0, Config::get('core.client.name').' Test Verification Report', 'B', 1);
        $this->SetFont('Courier', '', 11);
        $this->Cell(0, 10, Lang::choice('core::terms.student', 1).' Information');
        $this->Ln(5);
        $this->Cell(0, 6, '-------------------', 0, 1);

        // List all the students + demographics
        foreach ($students as $s) {
            $this->Cell(40, 0, $s->phone, 0, 0);
            $this->MultiCell(
                100,
                0,
                $s->commaName."\n"
                . $s->address."\n"
                . $s->city.', '.$s->state.' '.$s->zip."\n"
                . 'Email: '.$s->user->email,
                0,
                'L',
                false,
                0
            );
            $this->Cell(56, 26, 'X______________________', 0, 1, '', false, '', 0, false, 'T', 'T');
        }

        return $this->Output();
    }

    /**
     * Prints out form 1250 (admin report), listing all candidates in an event
     * 
     * @param   Testevent $event
     * @return  void
     */
    public function adminReport($event)
    {
        // Need:
        // * facility
        // * observer
        // * event

        // Set the title
        $this->SetTitle('Form 1250');

        // format text
        $headmaster_property_text = 'These materials are the property of HEADMASTER. Unauthorized use or distribution of the content is prohibited. If found, please call 800-393-8664 for return instructions.';
        $instructions_text = 'Before mailing back this 1250 form, record at right of your signature, the actual date that you tested your candidate(s). Also, have each candidate sign next to their name to verify the spelling of their name, SS#, and Phone #. Thank you';
        $affidavit_text = 'I hereby swear to and verify that all security measures were followed and all the candidates listed above completed their tests without any assistance from any outside source. (except as listed as an irregularity above) Further, I declare that all testing materials were at all times in my sight or securely locked and exclusively in my control and no copies, in any form, were made of any of the testing materials.';

        // turn borders on/off
        $border = 0;
        
        // start pdf doc        
        $this->AddPage();
        $this->SetMargins(10, 0, 0);
        $this->SetFont('Courier', 'B', '11');
        
        // Examiners Report title
        $this->Cell(0, 8, Config::get('core.client.name')." Examiner's Report - " . $event->discipline->name, $border, 1);
        $this->SetFont('Courier', '', '11');

        // Testing Facility info
        $this->Cell(90, 0, "Event ID: ".$event->id, $border, 1);
        $this->Cell(90, 3, "-------------------------", $border, 1, 'L');
        $this->Cell(120, 5, strtoupper($event->facility->name), $border, 1, 'L');
        $this->Cell(120, 5, strtoupper($event->facility->address), $border, 1, 'L');
        $this->Cell(120, 5, strtoupper($event->facility->city).' '.strtoupper($event->facility->state).', '.$event->facility->zip, $border, 1);
        
        // Testing Facility phone/fax
        $rowY = $this->getY();
        $this->SetXY(130, $rowY - 15);
        $this->Cell(20, 5, "Phone #1", $border);
        $this->Cell(50, 5, ": ".$event->facility->phone, $border, 1);
        $this->SetX(130);
        $this->Cell(20, 5, "Phone #2", $border);
        $this->Cell(50, 5, ": ", $border, 1);
        $this->SetX(130);
        $this->Cell(20, 5, "Fax #", $border);
        $this->Cell(50, 5, ": ".$event->facility->fax, $border, 1);
        
        // Test Observer
        $this->setY($rowY);
        $this->SetFont('Courier', 'B', '11');
        $this->Cell(90, 8, "Test Administrator", $border, 1);
        $this->SetFont('Courier', '', '11');

        $this->Cell(85, 5, $event->observer->fullName, $border, 1);
        $this->Cell(85, 5, $event->observer->address, $border, 1);
        $this->Cell(85, 5, $event->observer->city.', '.$event->observer->state, $border, 1);
        
        // Testing Date/Time
        $this->SetXY(95, $rowY);
        $this->Cell(115, 8, "Testing Date/Time ---> ".$event->test_date . ' ' . $event->start_time, $border, 1);
        $this->SetFont('Courier', 'B', '11');
        $this->SetX(95);
        $this->Cell(115, 5, "************************************************", $border, 1);
        $this->SetX(95);
        $this->MultiCell(115, 5, $headmaster_property_text, $border, 'L');
        $this->SetX(95);
        $this->Cell(115, 0, "************************************************", $border, 1);

        // ------------------------------------------------------------------


        // Candidate Info
        $this->SetFont('Courier', 'B', '11');
        $this->Cell(75, 8, "Candidate Information", $border, 0);
        $this->Cell(25, 8, "Signature", $border, 0);
        $this->Cell(10, 8, "ORL", $border, 0);
        $this->Cell(10, 8, "ADA", $border, 0);
        $this->Cell(20, 8, "Written", $border, 0);
        $this->Cell(15, 8, "Skill", $border, 0);
        $this->Cell(23, 8, "Photo ID", $border, 0);
        $this->Cell(20, 8, "Confirm", $border, 1);
        $this->Cell(75, 2, "---------------------", $border, 0);
        $this->Cell(25, 2, "---------", $border, 0);
        $this->Cell(10, 2, "---", $border, 0);
        $this->Cell(10, 2, "---", $border, 0);
        $this->Cell(20, 2, "-------", $border, 0);
        $this->Cell(15, 2, "-----", $border, 0);
        $this->Cell(23, 2, "--------", $border, 0);
        $this->Cell(20, 2, "--------", $border, 1);
        $this->SetFont('Courier', '', '11');

        // loop through all candidates, printing their info
        if (! empty($event->students)) {
            foreach ($event->students as $student) {
                $knowledge = $student->knowledge ? $student->knowledge->testform_id : null;
                $skill     = $student->skill ? $student->skill->skilltest_id : null;
                $oral      = $student->pivot->is_oral ? 'Y' : '';
                $ada       = ! $student->acceptedAdas->isEmpty() ? implode('<br>', $student->acceptedAdas->lists('abbrev')->all()) : '';

                // List students with test(s)
                // first line
                $this->Cell(75, 5, $student->commaName, $border, 0);    // candidate name
                $this->Cell(25, 5, '', $border, 0);                     // signature
                $this->Cell(10, 5, $oral, $border, 0, 'C');             // Oral
                $this->Cell(10, 5, $ada, $border, 0, 'C');              // ADA
                $this->Cell(20, 5, $knowledge, $border, 0, 'C');        // knowledge form id
                $this->Cell(15, 5, $skill, $border, 0, 'C');            // skill form id
                $this->Cell(23, 5, 'Yes - No', $border, 0, 'C');        // photo id
                $this->Cell(20, 5, 'NS - RE', $border, 1, 'C');         // confirm (no-show, reschedule)
                // second line
                $this->Cell(35, 5, $student->phone, $border, 0);        // phone number
                $this->Cell(45, 5, $student->city, $border, 1);         // city, state
                $this->Line($this->getX() + 1, $this->getY(), 208, $this->getY());
            }
        }

        // Irregularities Report
        $this->setY($this->getY() + 6);
        $this->Cell(60, 8, 'Irregularities Report:', $border, 0);
        $this->Cell(90, 8, '(Candidate name and irregularity)', $border, 1);
        // $this->Cell(60, 5, '----------------------', $border, 1);
        $this->Cell(190, 7, '', 'B', 1);
        $this->Cell(190, 7, '', 'B', 1);
        $this->Cell(190, 7, '', 'B', 1);
        $this->Cell(190, 7, '', 'B', 1);
        $this->Cell(190, 7, '', 'B', 1);

        // packet, checker, oral, initials
        $this->setY($this->getY() + 4);
        $this->Cell(28, 8, 'PKT PRINTER', $border, 0);
        $this->Cell(15, 8, '', 'B', 0);
        $this->Cell(13, 8, 'Date:', $border, 0);
        $this->Cell(15, 8, '', 'B', 0);
        $this->SetX(90);
        $this->Cell(27, 8, 'DBL CHECKER', $border, 0);
        $this->Cell(15, 8, '', 'B', 1);
        $this->Ln(4);
        $this->MultiCell(195, 0, $instructions_text, $border, 'L');

        // affidavit
        $this->Ln(2);
        $this->Cell(20, 0, 'AFFIDAVIT:', $border, 1);
        $this->MultiCell(195, 0, $affidavit_text, $border);

        // examiners signature
        $this->SetX(100);
        $this->Cell(100, 10, '', 'B', 1, '', false, '', 0, false, 'T', 'B');
        $this->SetX(123);
        $this->Cell(60, 5, '(Examiner\'s Signature)', $border, 1);
        
        // show pdf
        return $this->Output();
    }
}
