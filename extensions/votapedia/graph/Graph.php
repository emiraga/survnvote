<?php
/**
 * @package Graphing
 */
if (!defined('MEDIAWIKI')) die();

/**
 * Graph class for drawing charts
 *
 * @author Emir Habul <emiraga@gmail.com>
 */
class Graph
{
    /** @var GraphSeries */ protected $series = array();
    /** @var GraphSeries */ protected $width = 400;
    /** @var GraphSeries */ protected $height = 200;

    public function __construct($type)
    {
        $this->type = $type;
    }
    public function setType($type)
    {
        $this->type = $type;
    }
    public function addSeries($series)
    {
        $this->series[] = $series;
    }
    public function getHTMLImage($id = 'graph')
    {
        return "<img id=\"$id\" width=\"{$this->width}\" height=\"{$this->height}\" "
        ."src=\"{$this->getImageLink()}\">";
    }
    public function getImageLink()
    {
        return "http://chart.apis.google.com/chart?".$this->getImageParams();
    }
    public function getImageParams()
    {
        if($this->type == 'line')
        {
            $imglink = "cht=lc&chs={$this->width}x{$this->height}";
            $series = $this->series[0];
            if($series->getCount())
            {
                $maxv = $series->getMaxValue();
                $imglink .= "&chd=t:".$series->getValuesFormat();
                $imglink .= "&chds=0,".($maxv);
                $imglink .= "&chxt=y&chxl=0:|RM0|RM$maxv";
            }
            return $imglink;
        }
        if($this->type == 'linexy')
        {
            $imglink = "cht=lxy&chs={$this->width}x{$this->height}";
            $series = $this->series[0];
            if($series->getCount())
            {
                $imglink .= "&chd=t:".$series->getXFormat(',').'|'.$series->getYFormat(',');
                $imglink .= "&chds={$series->getYMin()},{$series->getYMax()}";
                $imglink .= "&chxt=y,x&chxl=0:|{$series->getYlabel()}|1:|{$series->getXlabel()}";
                $imglink .= "&chm=s,000000,0,-1,3";
            }
            return $imglink;
        }
        if($this->type == 'pie')
        {
            $series = $this->series[0];

            $imglink = "cht=p3&chd=t:{$series->getValuesFormat()}"
                    ."&chs={$this->width}x{$this->height}";
            $colors = $series->getColorsFormat();
            if($colors)
                $imglink.="&chco=$colors";
            $names = $series->getNamesFormat();
            if($names)
                $imglink.="&chdl={$series->getNamesFormat()}";

            return $imglink;
        }
        if($this->type == 'multipie')
        {
            $data = array();
            $colors = array();
            $names = array();
            foreach($this->series as $series)
            {
                $v = $series->getValuesFormat();
                if($v)
                {
                    if($series->getCount() == 1)
                        $v .= ',0';
                    $data[] = $v;
                    $colors[] = $series->getColorsFormat();
                    $names[] = $series->getNamesFormat();
                }
            }
            $data = 't:'.join('|',$data);
            $colors = join(',', $colors);
            $names = join('|', $names);
            echo "$data<br>$colors<br>$names<br>";
            $imglink = "cht=pc"
                    ."&chs={$this->width}x{$this->height}&chd=$data";
            if($colors)
                $imglink .= "&chco=$colors";
            if($names)
                $imglink .= "&chdl=$names";
            return $imglink;
        }
        if($this->type == 'stackpercent')
        {
            $xlabel = '';
            $chbh = intval(($this->width - 50) / count($this->series));
            $maxv = 0;
            $markers = array();
            foreach($this->series as &$series)
            {
                $xlabel .= '|'.$series->getTitle();
                if($series->getCount() > $maxv)
                    $maxv = $series->getCount();
            }
            $data = array();
            $colors = array();
            for($i = $maxv-1; $i>=0; $i--)
            {
                $barvalues = array();
                $barcolors = array();
                $s = 0;
                foreach($this->series as &$series)
                {
                    $val = $series->getValue($i);
                    if($val === false)
                    {
                        $barvalues[] = 0;
                        $barcolors[] = '000000';
                    }
                    else
                    {
                        $barvalues[] = intval(100 * $val / $series->getSum());
                        $barcolors[] = $series->getColor($i);
                        $pos = $maxv-$i-1;
                        $markers[] = "t".$series->getName($i).",000000,$pos,$s,12,,c";
                    }
                    $s++;
                }
                $data[] = join(',', $barvalues);
                $colors[] = join('|', $barcolors);
            }
            /*$s = 0;
            foreach($this->series as &$series)
            {
                $pos = $maxv-1;
                $markers[] = "t".$series->getTitle().",000000,$pos,$s,12,,e::15";
                $s++;
            }*/
            $data = 't:'.join('|',$data);
            $colors = join(',', $colors);
            $imglink = "cht=bvs"
                    ."&chs={$this->width}x{$this->height}&chd=$data&chbh=$chbh"
                    ."&chco=$colors&chxt=x,y&chxl=0:$xlabel&chxs=1N**%&chds=0";
            $maxsize = 1024;

            $imglink .= "&chm=".join('|', $markers);
            return $imglink;
        }
        throw new Exception("Unknown graph type");
    }
}

class GraphSeries
{
    protected $title;
    protected $names = array();
    protected $values = array();
    protected $colors = array();
    protected $count = 0;
    protected $sum = 0;
    protected $maxValue = 0;

    public function __construct($title)
    {
        $maxtitle = 50;
        if(strlen($title) >= $maxtitle)
            $title = substr($title,0,$maxtitle-3).'...'; //@todo remove crazy characters
        $this->title = $title;
    }
    function reverseValues()
    {
        $this->names = array_reverse($this->names);
        $this->values = array_reverse($this->values);
        $this->colors = array_reverse($this->colors);
    }
    function getCount()
    {
        return $this->count;
    }
    function getSum()
    {
        if($this->sum)
            return $this->sum;
        return 1;
    }
    function getMaxValue()
    {
        return $this->maxValue;
    }
    function getTitle()
    {
        return $this->title;
    }
    function addItem($name, $value, $color)
    {
        $maxname = 30;
        if($value == 0)return;
        $this->sum += $value;

        if($this->maxValue < $value)
            $this->maxValue = $value;

        if(strlen($name) >= $maxname)
            $name = substr($name,0,$maxname-3).'...'; //@todo remove crazy characters
        $this->names[] = urlencode($name);

        $value = urlencode($value);
        $this->values[] = $value;
        $this->colors[] = $color;
        $this->count++;
    }
    function getValue($index)
    {
        if($index >= $this->count)
            return false;
        else
            return $this->values[$index];
    }
    function getColor($index)
    {
        if($index >= $this->count)
            return false;
        else
            return $this->colors[$index];
    }
    function getName($index)
    {
        if($index >= $this->count)
            return 'e';
        else
            return $this->names[$index];
    }
    function getNamesFormat($glue = "|")
    {
        return join($glue, $this->names);
    }
    function getColorsFormat()
    {
        return join("|", $this->colors);
    }
    function getValuesFormat()
    {
        return join(",", $this->values);
    }
}

class GraphXY
{
    protected $title;
    protected $x = array();
    protected $y = array();
    protected $xmin;
    protected $xmax;
    protected $ymin;
    protected $ymax;
    public function __construct($title)
    {
        $this->title = $title;
    }
    public function getCount()
    {
        return count($this->x);
    }
    public function addPoint($x, $y)
    {
        if(count($this->x) == 0)
        {
            $this->xmin = $this->xmax = $x;
            $this->ymin = $this->ymax = $y;
        }
        else
        {
            if($this->xmin > $x)   $this->xmin = $x;
            if($this->xmax < $x)   $this->xmax = $x;
            if($this->ymin > $y)   $this->ymin = $y;
            if($this->ymax < $y)   $this->ymax = $y;
        }
        $this->x[] = $x;
        $this->y[] = $y;
    }
    public function getX()
    {
        throw new Exception('not implemented');
    }
    public function getXFormat($glue = ',')
    {
        return join($glue, $this->getX());
    }
    public function getYFormat($glue = ',')
    {
        return join($glue, $this->getY());
    }
    public function getY()
    {
        return $this->y;
    }
    public function getYMax()
    {
        return $this->ymax;
    }
    public function getYMin()
    {
        return 0;
    }
    public function getXMax()
    {
        return $this->xmax;
    }
    public function getXMin()
    {
        return $this->xmin;
    }
    public function getYlabel()
    {
        $y2 = $this->ymax / 2;
        return "|$y2|{$this->getYMax()}";
    }
    public function getXlabel()
    {
        throw new Exception('not implemented');
    }
}

class GraphXYdate extends GraphXY
{
    public function __construct($title)
    {
        parent::__construct($title);
    }
    public function getX()
    {
        $result = array();
        foreach ($this->x as $x)
        {
            $xmin = strtotime($this->xmin);
            $xmax = strtotime($this->xmax);
            $x = strtotime($x);
            $x = ($x - $xmin)/($xmax - $xmin) * 5;
            $result[] = sprintf("%.2f", $x);
        }
        return $result;
    }
    public function getXlabel($glue = '|')
    {
        $tmin = strtotime($this->xmin);
        $tmax = strtotime($this->xmax);
        return date('Y-m-d',$tmin)
                .$glue.date('Y-m-d',$tmin+($tmax-$tmin)/2)
                .$glue.date('Y-m-d',$tmax);
    }
}
