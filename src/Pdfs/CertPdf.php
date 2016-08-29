<?php namespace Hdmaster\Core\Pdfs;

use \TCPDF;

// Extend the TCPDF class to create custom AddPage and eliminate Header and Footer
class CertPdf extends TCPDF
{

    public $marginLeft   = 36;
    public $marginTop    = 36;
    public $marginRight  = 36;
    public $marginHeader = 0;
    public $marginFooter = 0;
    public $pageFormat   = 'LETTER';

    public function __construct($orientation='P', $unit='pt', $format='LETTER', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        // set document information
        $this->SetAuthor('Headmaster');
        $this->SetTitle('Certificate of Completion');
        $this->SetKeywords('pdf, Headmaster');

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $this->SetMargins($this->marginLeft, $this->marginTop, $this->marginRight);
        $this->SetHeaderMargin($this->marginHeader);
        $this->SetFooterMargin($this->marginFooter);

        //set auto page breaks
        $this->SetAutoPageBreak(true, 0);

        //set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function Header()
    {
    }
    public function Footer()
    {
    }
    
    public function MyBarCode($s, $x, $y, $w, $h)
    { //relative to grid of 1/6 inch cells
        // define barcode style
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => true,
            'fitwidth' => false,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 0, //auto
            'vpadding' => 0, //auto
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,0,255),
            'text' => true,
            'font' => 'helvetica', //text
            'fontsize' => 8, //appears to be points
            'stretchtext' => 4
        );
        $this->write1DBarcode($s, 'C39', $x*72, $y*72, $w*72, $h*72, 0.5, $style, 'N');
        //$this->write1DBarcode($s, 'C39', '','', '','','', $style, 'N'); 
    }
    
    public function WaterMark($text)
    {
        $this->StartTransform();
        $this->Rotate(20, 0, 0); //degrees, x, y
        $this->SetFont('helvetica', 'B', 120);
        $this->SetTextColor(255, 127, 127);
        $this->SetXY(0.5*72, 3.5*72);
        $this->SetAlpha(0.5); //transparency
        $this->Write(0, $text);
        $this->SetAlpha(1);
        $this->StopTransform();
        $this->SetTextColor(0, 0, 0);
    }
    
    public function Logo($theimage, $alpha = 0.50)
    {
        if (file_exists($theimage)) {
            $this->StartTransform();
            $this->SetAlpha($alpha); //transparency
            $this->Image($theimage, '', 3.70*72, 1.25*72, '', 'GIF', '', 'T', true, 300, 'C', false, false, 0, false, false, false);
            //$this->Image($theimage, '', 1.0*72, 6.0*72, '', 'GIF', '', 'T', true, 300, 'C', false, false, 0, false, false, false);
            $this->SetAlpha(1);
            $this->StopTransform();
        }
    }
    
    public function DrawGrid($spc)
    {
        $this->SetDrawColor(255, 0, 0);
        $this->SetLineStyle(array('width' => 0.01, 'cap' => 'butt', 'join' => 'miter', 'dash' => 4, 'color' => array(200, 200, 200)));
        $this->SetFont('helvetica', '', 8);
        for ($i = $spc*2; $i < 8.5*72-$spc*2; $i+=$spc) {
            $this->Line($i, $spc*2, $i, 11.0*72-$spc*2);
            $this->setxy($i, $spc*2);
            $this->Write(0, $i/72);
        }
        for ($i = $spc*2; $i < 11.0*72-$spc*2; $i+=$spc) {
            $this->Line($spc*2, $i, 8.5*72-$spc*2, $i);
            $this->setxy($spc*2, $i);
            $this->Write(0, $i/72);
        }
    }
    public function Plain_Border($r, $g, $b)
    {
        $this->SetLineStyle(array('width' => 2.0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        $this->SetDrawColor($r, $g, $b);
        $this->Rect($this->marginLeft, $this->marginTop, 7.5*72, 10*72);
        $this->Rect($this->marginLeft+4, $this->marginTop+4, 7.5*72-8, 10*72-8);
        $this->Rect($this->marginLeft+8, $this->marginTop+8, 7.5*72-16, 10*72-16);
    }
    
    public function Bar($x, $y, $w, $h)
    {
        $this->SetDrawColor(0x00, 0x00, 0x88);
        $this->SetLineStyle(array('width' => 1.0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 127)));
        $this->Rect(72*$x, 72*$y, 72*$w, $h);
    }
    
    public function Default_Border()
    {
        $this->SetLineStyle(array('width' => 2.0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        //thin border
        //$this->SetDrawColor(2*32,2*32,2*32);
        //$this->SetDrawColor(0x88,0x00,0x00);
        //$this->SetDrawColor(0xFF,0xD7,0x00); //gold
        //$this->SetDrawColor(0x88,0x00,0x00); //maroon
        $this->SetDrawColor(0x00, 0x00, 0x88);
        $r=29;
        $this->PolyLine(array(    36, 36, 36+22, 36, 36+22, 36+$r, 36, 36+$r,
                                36, 756-$r, 36+22, 756-$r, 36+22, 756, 36, 756, 36, 756-22, 36+$r, 756-22, 36+$r, 756, //lowerleft
                                576-$r, 756, 576-$r, 756-22, 576, 756-22, 576, 756, 576-22, 756, 576-22, 756-$r, 576, 756-$r, //lowerright
                                576, 36+$r, 576-22, 36+$r, 576-22, 36, 576, 36, 576, 36+22, 576-$r, 36+22, 576-$r, 36, //topright
                                36+$r, 36, 36+$r, 36+22, 36, 36+22, 36, 35 //back to topleft
                        ));
        //thick border
        for ($i = 1; $i < 10; $i++) {
            $this->SetDrawColor($i*10, $i*10, $i*20); //gradient color
            $this->Rect($this->marginLeft+6+$i, $this->marginTop+6+$i, 7.5*72-12-$i*2, 10*72-12-$i*2);
        }
    }

    public function Body($Name, $Site, $training, $vCode, $DOB, $license, $trApproval)
    {
        $spc = 15; //break between sections
        $this->SetLineStyle(array('width' => 2.0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 127))); //for cell borders
        $this->SetTextColor(0, 0, 127); //navy
        $this->setxy(36, 72); //72 dpi
        $this->SetFont('helvetica', 'B', 14);
        $this->MultiCell(72*4.5, 72*.5, $Site, 'B', 'C', false, 1, 72*2, 72*0.5, true, false, false, true, 72, 'B', false);
        $this->SetFont('helvetica', 'I', 8);
        $this->MultiCell('', '', trans('core::certification.facility_title'), 0, 'C');
        $this->ln($spc);
        $this->SetFont('helvetica', 'B', 18);
        $this->MultiCell('', '', trans('core::certification.title'), 0, 'C');
        $this->ln($spc);
        $this->ln($spc);
        $this->ln($spc);
        $this->SetFont('helvetica', 'B', 12);
        $this->MultiCell('', '', trans('core::certification.awarded_to'), 0, 'C');
        $this->ln($spc);
        $this->SetFont('helvetica', 'B', 18);
        $this->MultiCell(72*4.5, 72*.5, stripslashes($Name), 'B', 'C', false, 1, 72*2, 72*2.5, true, false, false, true, 72, 'B', false);
        $this->SetFont('helvetica', 'I', 8);
        $this->MultiCell('', '', "Name of ".Lang::choice('core::terms.student', 1)." Completing Course", 0, 'C');
        $this->SetFont('helvetica', 'B', 14);
        $this->MultiCell(72*1.5, 72*0.5, $license, 'B', 'C', false, 1, 72*1.5, 72*3.0, true, false, false, true, 72, 'B', false);
        $this->MultiCell(72*1.5, 72*0.5, $DOB, 'B', 'C', false, 1, 72*5.5, 72*3.0, true, false, false, true, 72, 'B', false);
        $this->SetFont('helvetica', 'I', 8);
        $this->MultiCell(72*1.5, 72*0.5, Lang::choice('core::terms.student', 1)."'s ID", '', 'C', false, 1, 72*1.5, 72*4.0, true, false, false, true, 72, 'T', false);
        $this->MultiCell(72*1.5, 72*0.5, Lang::choice('core::terms.student', 1)."'s Date of Birth", '', 'C', false, 1, 72*5.5, 72*4.0, true, false, false, true, 72, 'T', false);
        $this->SetFont('helvetica', 'B', 12);
        $this->MultiCell('', '', trans('core::certification.for_completing'), 0, 'C');
        $this->ln($spc);
        $this->SetFont('helvetica', 'B', 18);
        $this->MultiCell('', '', $training->training->name, 0, 'C');
        $this->SetFont('helvetica', 'B', 16);
        $this->MultiCell(72*1.5, 72*0.5, $training->ended, 'B', 'C', false, 1, 72*3.5, 72*5.0, true, false, false, true, 72, 'B', false);
        $this->SetFont('helvetica', 'I', 8);
        $this->MultiCell('', '', "Completion Date", 0, 'C');
        $this->SetFont('helvetica', 'B', 12);
        $this->MultiCell(72*6.5, 72*0.5, "             $trApproval                    $training->classroom_hours                  $training->clinical_hours   ", '0', 'J', false, 1, 72*1.0, 72*6.0, true, false, false, true, 72, 'B', false);
        $this->MultiCell(72*6.5, 72*1.0, trans('core::certification.approval'), 'T', 'C', false, 1, 72*1.0, 72*7.0, true, false, false, true, 72, 'T', false);
        $this->MultiCell(72*6.5, 72*0.5, trans('core::certification.signature'), 'T', 'C', false, 1, 72*1.0, 72*8.0, true, false, false, true, 72, 'T', false);
        $this->SetFont('helvetica', '', 10);
        $this->MultiCell('', '', trans('core::certification.convey'), 0, 'C');
        $this->ln($spc);
        $this->ln($spc);
        $this->ln($spc);
        $this->ln($spc);
        $this->SetFont('helvetica', '', 6);
        $this->MultiCell('', '', "Validation Code: $vCode", 0, 'C');
        //multicell=w,h,txt,border,align,fill,ln,x,y,reseth,stretch,ishtml,autopadding,maxh,valign,fitcell
    }
    
    public function certificate($student, $training, $facility, $options = [])
    {
        $defaults = [
            'border'  => true,
            'barcode' => false,
            'logo'    => false,
            'alpha'   => 0.50, // logo alpha
            'grid'    => false
        ];

        foreach ($defaults as $k => $d) {
            $$k = array_key_exists($k, $options) ? $options[$k] : $d;
        }

        $this->AddPage();

        // Include a grid?
        if ($grid === true) {
            $this->DrawGrid(36);
        }

        // Logo?
        if (! empty($logo) && is_string($logo)) {
            $this->Logo($logo, $alpha);
        }

        // Border
        if ($border === true) {
            $this->Default_Border();
        }

        //name,parent,training,vcode,DOB,license,approval date
        $this->Body(
            $student->fullName, // name
            $facility->name, // facility
            $training, // completion
            date('M d, Y g:i'), // v code
            $student->birthdate, // birthdate
            $student->id, // license
            $facility->last_training_approval // approval date
        );

        if ($barcode === true) {
            $this->MyBarCode("*$student->id*", 3.0, 9, 2.5, 0.5); //x,y,w,h	
        }
    }

    /**
     * Output the PDF
     * @param  string $filename
     * @param  string $outputType 
     * @return pdf
     */
    public function show($filename = null, $outputType = 'I')
    {
        if ($filename === null) {
            $filename = date('h_i_s');
        }

        return $this->Output($filename . '.pdf', $outputType);
    }
}
