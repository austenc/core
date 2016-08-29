<?php namespace Hdmaster\Core\Pdfs;

use \Config;
use \FPDI;

class ScanPdf extends FPDI
{
    //scanform is 6 lpi and 6 cpi
    //scale is in inches
    //thus the spacing contant 1/6
    public $_tplIdx;
    private $vOff;
    private $hOff;
    
    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        $this->vOff = Config::get('core.scan.offset_v'); //tweak for vertical alignment
        $this->hOff = Config::get('core.scan.offset_h'); //tweak for horizontal alignment
    }

    public function setVOffset($value)
    {
        $base       = Config::get('core.scan.offset_v');
        $this->vOff = $base += $value;
    }
    public function setHOffset($value)
    {
        $base       = Config::get('core.scan.offset_h');
        $this->hOff = $base += $value;
    }

    public function Header()
    {
    }

    public function Footer()
    {
    }
    
    public function PopulateField($x, $y, $spc, $font, $string)
    {
        $this->SetFont($font, '', 12);
        for ($i = 0; $i < strlen($string); $i++) {
            $this->setxy($x, $y);
            $this->Write(0, $string[$i]);
            $x += $spc;
        }
    }

    public function SlugHorizontal($x, $y, $s, $l, $echo, $mstyle, $textoffset)
    {
        $this->SetFont('helvetica', '', 10);
        $string = str_pad($s, $l, "0", STR_PAD_LEFT);
        for ($i = 0; $i < strlen($string); $i++) {
            $p = $string[$i];

            switch ($mstyle) {
                case "circle":
                    $this->Circle(($x+$i)*(1/6)+(1/12)+$this->hOff, ($y+$p)*(1/6)+(1/12)+$this->vOff, 1/15, 0, 360, 'DF', '', array(0, 0, 0));
                break;
                case "rect":
                    $this->RoundedRect(($x+$i)*(1/6)+1/36+$this->hOff, ($y+$p)*(1/6)+1/36+$this->vOff, 1/9, 1/9, 1/32, '1111', 'DF', null, array(0, 0, 0));
                break;
                case "oval":
                    $this->Ellipse(($x+$i)*(1/6)+(1/12)+$this->hOff, ($y+$p)*(1/6)+(1/12)+$this->vOff, 1/15, 1/20, 0, 0, 360, 'DF', '', array(0, 0, 0));
                break;
            }
            if ($echo) {
                $this->setxy(($x+$i)*(1/6)+$this->hOff, ($y-$textoffset)*(1/6)+$this->vOff);
                $this->Write(0, $string[$i]);
            }
        }
    }

    public function SlugHorizontalAlpha($x, $y, $s, $l, $echo, $mstyle, $textoffset)
    {
        //$string = str_pad($s,$l," ",STR_PAD_RIGHT);

        $this->SetFont('helvetica', '', 10);
        $string = str_limit(strtoupper($s), $l, '');
        for ($i = 0; $i < strlen($string); $i++) {
            $p = ord($string[$i])-65;
            switch ($mstyle) {
                case "circle":
                    $this->Circle(($x+$i)*(1/6)+(1/12)+$this->hOff, ($y+$p)*(1/6)+(1/12)+$this->vOff, 1/15, 0, 360, 'DF', '', array(0, 0, 0));
                break;
                case "rect":
                    $this->RoundedRect(($x+$i)*(1/6)+1/36+$this->hOff, ($y+$p)*(1/6)+1/36+$this->vOff, 1/9, 1/9, 1/32, '1111', 'DF', null, array(0, 0, 0));
                break;
                case "oval":
                    $this->Ellipse(($x+$i)*(1/6)+(1/12)+$this->hOff, ($y+$p)*(1/6)+(1/12)+$this->vOff, 1/15, 1/20, 0, 0, 360, 'DF', '', array(0, 0, 0));
                break;
            }
            if ($echo) {
                $this->setxy(($x+$i)*(1/6)+$this->hOff, ($y-$textoffset)*(1/6)+$this->vOff);
                $this->Write(0, $string[$i]);
            }
        }
    }

    /**
     * Slugs a column using binary in a vertical fashion
     * @param $x    start X position
     * @param $y    start Y position
     * @param $s    the value to slug
     * @param $rows total rows
     * @param $cols total cols
     */
    public function SlugVerticalBinary($x, $y, $s, $cols = 30, $textoffset = 3)
    {
        $p   = 0;
        $col = 0;

        // convert string to binary and pad it to 10 to match # of columns
        $string     = str_pad(base_convert($s, 10, 2), $cols, "0", STR_PAD_LEFT);
        
        for ($i = strlen($string)-1; $i >=0; $i--) {
            $theX = $x;

            if ($p >= 10) {
                $theX = $x + 1;
                $col = $p === 10 ? 0 : $col;
            }

            if ($p >= 20) {
                $theX = $x + 2;
                $col = $p === 20 ? 0 : $col;
            }

            if ($string[$i] == "1") {
                $this->RoundedRect($theX*(1/6)+1/36+$this->hOff, ($y+$col)*(1/6)+1/36+$this->vOff, 1/9, 1/9, 1/32, '1111', 'DF', null, array(0, 0, 0));
            }

            $p++;
            $col++;
        }

        // USED FOR DEBUGGING, PRINTS VALUE
        $this->SetFont('helvetica', '', 6);
        $this->setxy($x*(1/6)+$this->hOff, ($y-$textoffset)*(1/6)+$this->vOff);
        $this->Write(0, $s);
    }

    public function MyBarCode($s, $x, $y, $w, $h)
    { //relative to grid of 1/6 inch cells
        // define barcode style
        $style = array(
            'position'     => '',
            'align'        => 'C',
            'stretch'      => false,
            'fitwidth'     => false,
            'cellfitalign' => '',
            'border'       => false,
            'hpadding'     => 0, //auto
            'vpadding'     => 0, //auto
            'fgcolor'      => array(0,0,0),
            'bgcolor'      => false, //array(255,0,255),
            'text'         => false,
            'font'         => 'helvetica', //text
            'fontsize'     => 8, //appears to be points
            'stretchtext'  => 4
        );
        $this->write1DBarcode($s, 'C39', $x*(1/6)+$this->hOff, $y*(1/6)+$this->vOff, $w*(1/6), $h*(1/6), '', $style, 'N'); //scan form
    }

    public function DrawGrid($spc)
    {
        $this->SetDrawColor(0xF0, 0xF0, 0xF0);
        $this->SetFont('helvetica', '', 6);
        for ($i = 0; $i < 8.5; $i+=$spc) { //vertical
            $this->Line($i+$this->hOff, 0+$this->vOff, $i+$this->hOff, 11.0-$spc+$this->vOff);
            $this->setxy($spc+$this->hOff, $i+$this->vOff);
            $this->Write(0, $i/$spc);
        }
        for ($i = 0; $i < 11.0; $i+=$spc) { //horizontal
            $this->Line(0+$this->hOff, $i+$this->vOff, 8.5-$spc+$this->hOff, $i+$this->vOff);
            $this->setxy($i+$this->hOff, $spc+$this->vOff);
            $this->Write(0, $i/$spc);
        }
    }
}
