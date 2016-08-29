<?php namespace Hdmaster\Core\Pdfs;

use Hdmaster\Notifications\Flash;
use \TCPDF;

/**
 * Prints skill test forms using TCPDF
 */
class TaskPdf extends TCPDF
{

    private $blank = '_______________';

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
     * Prints out a skill task pdf
     */
    public function task($task)
    {
        // Set some initial PDF settings and add a page
        $this->setTitle('Task: ' . $task->title);
        $this->SetFont('Courier', 'B', '11');
        $this->addPage();

        // Task Title
        $this->Cell(0, 0, $task->title, 'B', 1, 'C');
        $this->SetFont('Courier', '', '11');
        // ---------------------------------------------------------------------------

        $colWidth = 30;

        // Scenario
        $this->MultiCell(0, 0, '', 0, 'L');
        $this->setFont('Courier', 'B', 11);
        $this->Cell($colWidth, 0, 'Scenario: ', 0, 0);
        $this->setFont('Courier', '', 11);
        $this->MultiCell(0, 0, $task->scenario, 0, 'L');
        $this->MultiCell(0, 0, '', 0, 'L');

        // Note to TO
        if ($task->note) {
            $this->setFont('Courier', 'B', 11);
            $this->Cell($colWidth, 0, 'Note to TO: ', 0, 0);
            $this->setFont('Courier', '', 11);
            $this->MultiCell(0, 0, $task->note, 0, 'L');
            $this->Cell(0, 0, '', 'B', 1, 'L');
            $this->MultiCell(0, 0, '', 0, 'L');
        }

        // Setups
        if ($task->setups) {
            $count = 1;
            foreach ($task->setups as $setup) {
                // add spacing if more than 1 setup
                if ($count > 1) {
                    $this->MultiCell(0, 0, '', 0, 'L');
                }

                $this->setFont('Courier', 'B', 11);
                $this->Cell($colWidth, 0, 'Setup ' . $count . ': ', 0, 0);
                $this->setFont('Courier', '', 11);
                $this->MultiCell(0, 0, $setup->setup, 0, 'L');
                $count++;
            }

            // Line + Spacing
            $this->Cell(0, 0, '', 'B', 1, 'L');
            $this->MultiCell(0, 0, '', 0, 'L');
        }

        // Task Steps
        if (empty($task->steps)) {
            $this->Cell(0, 0, 'This task has no steps currently defined.');
        } else {
            foreach ($task->steps as $step) {
                $key = $step->is_key ? 'X' : 'A';
                // set the background color if it's a key
                if ($step->is_key) {
                    $this->SetFillColor(255, 251, 204);
                } else {
                    $this->SetFillColor(255, 255, 255);
                }

                $this->Cell(15, 7, $key . '  ' . $step->ordinal . '.  ', 0, 0, '', true);
                $this->MultiCell(0, 7, $step->paper, 0, 'L', true, 1, '', '', true, 0, true);
                $this->Ln(2);
            }
        }

        // return the PDF output
        return $this->Output();
    }
}
