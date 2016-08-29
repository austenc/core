<?php namespace Hdmaster\Core\Pdfs;

use \ScanPdf;
use \Config;

class Scanform
{

    protected $pdf;
    public $initialized = false;

    public function __construct($vOff = null, $hOff = null)
    {
        $this->pdf = new ScanPdf('P', 'in', 'LETTER', true, 'UTF-8', false);

        // Set vertical / horizontal offsets
        if (is_numeric($vOff)) {
            $this->pdf->setVOffset($vOff);
        }
        if (is_numeric($hOff)) {
            $this->pdf->setHOffset($hOff);
        }
    }

    public function getVerticalOffset()
    {
        return $this->pdf->vOff;
    }

    public function getHorizontalOffset()
    {
        return $this->pdf->hOff;
    }

    public function initialize()
    {
        if (! $this->initialized) {
            $pdf = $this->pdf;
            $pdf->SetMargins(1.5, 1.5, 0); //L,T,R
            $pdf->SetHeaderMargin(0);
            $pdf->SetFooterMargin(0);
            $pdf->setFontSubsetting(false);

            $pdf->AddPage();
            // $pdf->DrawGrid(1/6); //to locate positions
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFillColor(0, 255, 0);
        }

        $this->initialized = true;

        return $this->pdf;
    }

    public function addPage()
    {
        $this->pdf->addPage();
        return $this->pdf;
    }


    public function slug($student, $info = [])
    {
        $info = array_merge([
            'knowledgeAttempt' => null,
            'skillAttempt'     => null,
            'event'            => null,
            'knowledgeTest'    => null,
            'skillTest'        => null
        ], $info);

        $knowledgeAttemptId = $info['knowledgeAttempt'];
        $knowledgeTest      = $info['knowledgeTest'];
        $skillAttemptId     = $info['skillAttempt'];
        $skillTest          = $info['skillTest'];
        $event              = $info['event'];

        $pdf = $this->initialize();

        $pdf->SetFont('courier', '', 10);

        $start = 0.35;
        $spacer = 0.15;

        // Event # up top
        if ($event) {
            $pdf->setXY(3.5, $start);
            $pdf->Write(0, 'Event #:   '.$event->id);
        }

        if ($knowledgeTest) {
            $pdf->setXY(3.5, $start+$spacer);
            $pdf->Write(0, 'Knowledge: '.$knowledgeTest);
        }

        if ($skillTest) {
            $pdf->setXY(3.5, $start+$spacer*2);
            $pdf->Write(0, 'Skill:     '.$skillTest);
        }

        // SLUG EVERYTHING BELOW
        // Last, First, MI
        $pdf->SlugHorizontalAlpha(4, 9, $student->last, 15, true, 'rect', 2);
        $pdf->SlugHorizontalAlpha(20, 9, $student->first, 10, true, 'rect', 2);
        $pdf->SlugHorizontalAlpha(31, 9, $student->middle, 1, true, 'rect', 2);

        // Identification #
        $pdf->SlugHorizontal(33, 15, $student->id, 9, true, 'rect', 2);

        // Special
        //$pdf->SlugHorizontal(43, 15, 12029, 5, true, 'rect', 2);

        // Test (client)
        $client = Config::get('core.scan.client_numbers.' . strtolower(Config::get('core.client.abbrev')));
        if (is_numeric($client)) {
            $pdf->SlugHorizontal(35, 30, $client, 2, true, 'rect', 3);
        }
    
        // Subjective Totals
        if ($knowledgeAttemptId !== null) {
            $pdf->SlugVerticalBinary(39, 30, $knowledgeAttemptId); // knowledge attempt #
        }

        if ($skillAttemptId !== null) {
            $pdf->SlugVerticalBinary(42, 30, $skillAttemptId); // skill attempt #
        }

        $pdf->MyBarCode('*OR12345678902112013*', 4, 35, 24, 2); //scan form
        // $pdf->MyBarCode('*MP1302001*', 10, 44, 18, 4); //admin
        // $pdf->write1DBarcode('*STnnnnnnnnnMMDDYYYY*', 'C39', 3+0.06,3*(1/6)+0.12, 4, 2/6,'', $style, 'N'); //scan form
        // $pdf->write1DBarcode('*STnnnnnnnnnMMDDYYAPw*', 'C39', 3+0.06,3*(1/6)+0.12, 4, 2/6,'', $style, 'N'); //cover sheet
        // $pdf->write1DBarcode('*STnnnnnnnnnMMDDYYAP*', 'C39', 3+0.06,3*(1/6)+0.12, 4, 2/6,'', $style, 'N'); //cover sheet
        // $pdf->write1DBarcode('*STYYppppp*', 'C39', 3+0.06,3*(1/6)+0.12, 4, 2/6,'', $style, 'N'); //admin report

        return $pdf;
    }

    public function pdf($student, $info = [], $outputType = 'I')
    {
        $info = array_merge([
            'knowledgeAttempt' => null,
            'skillAttempt'     => null,
            'event'            => null,
            'knowledgeTest'    => null,
            'skillTest'        => null,
            'title'            => 'Print Scanform | Testmaster'
        ], $info);

        $this->pdf = $this->slug($student, [
            'knowledgeAttempt' => $info['knowledgeAttempt'],
            'knowledgeTest'    => $info['knowledgeTest'],
            'skillAttempt'     => $info['skillAttempt'],
            'skillTest'        => $info['skillTest'],
            'event'            => $info['event']
        ]);

        $this->pdf->setTitle($info['title']);

        return $this->render($outputType);
    }

    public function render($outputType = 'I')
    {
        // ****************************
        // PRINT WITH PAGE SCALING=NONE
        // ****************************

        $this->pdf->SetTextColor(255, 0, 0);
        $mFont = 'courier';


        return $this->pdf->Output('newpdf.pdf', $outputType);
    }
}
