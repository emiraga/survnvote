<?php
/**
 * @package Graphing
 */
if (!defined('MEDIAWIKI')) die();

function vfCutEncode($str, $maxlen, $elipsis = '...')
{
    if(strlen($str) >= $maxlen)
        $str = substr($str,0,$maxlen-strlen($elipsis)).$elipsis; //@todo remove crazy characters
    return  urlencode($str);
}
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
            //$maxname = 30;
            $series = $this->series[0];

            $imglink = "cht=p3&chs={$this->width}x{$this->height}";
            $colors = $series->getColorsFormat();
            if($colors)
                $imglink.="&chco=$colors";
            if($series->getCount())
            {
                $imglink.="&chd=t:{$series->getValuesFormat()}";
                if($series->getCount() <= 11)
                    $imglink.="&chdl=".$series->getNamesFormat(30, true);
                else
                    $imglink.="&chl=".$series->getNamesFormat(30, true);
            }
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
                    $names[] = $series->getNamesFormat(30, true);
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
            $numseries = count($this->series);
            $labelLength = 3;
            if($numseries == 2)     $labelLength = 25;
            elseif($numseries == 3) $labelLength = 18;
            elseif($numseries == 4) $labelLength = 13;
            elseif($numseries == 5) $labelLength = 10;
            elseif($numseries == 6) $labelLength = 8;
            elseif($numseries == 7) $labelLength = 7;
            elseif($numseries == 8) $labelLength = 6;
            $xlabel = '';
            $chbh = intval(($this->width - 60) / count($this->series));
            $maxv = 0;
            $markers = array();
            foreach($this->series as &$series)
            {
                $xlabel .= '|'. vfCutEncode($series->getTitle(),$labelLength);
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
                        $barvalues[] = '0';
                        $barcolors[] = '000000';
                    }
                    else
                    {
                        $barvalues[] = vfCutEncode(100 * $val / $series->getSum(), 5, '');
                        $barcolors[] = $series->getColor($i);
                        $pos = $maxv-$i-1;
                        $markers[] = "t". vfCutEncode( $series->getName($i), $labelLength ).",000000,$pos,$s,12,,c";
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
            $imglink = "cht=bvs&chs={$this->width}x{$this->height}&chbh=$chbh";
            
            $imglink .="&chd=$data"
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
        $this->title = vfCutEncode($title, 50);
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
        if($value == 0)
            return;
        $this->sum += $value;

        if($this->maxValue < $value)
            $this->maxValue = $value;

        $this->names[] = $name;
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
            return '';
        else
            return $this->names[$index];
    }
    function getColorsFormat($glue = '|')
    {
        return join($glue, $this->colors);
    }
    function getValuesFormat($glue = ',')
    {
        return join($glue, $this->values);
    }
    function getNamesFormat($maxlen, $addvalues = false, $glue = '|')
    {
        $names = array();
        for($i=0;$i<$this->count;$i++)
        {
            $name = vfCutEncode($this->names[$i], $maxlen);
            if($addvalues)
                $name .= ' ('. vfCutEncode($this->values[$i], 10, '').')';
            $names[] = $name;
        }
        return join($glue, $names);
    }
    function sortOnlyTop($num)
    {
        for($i=0;$i<$this->count;$i++)
        {
            for($j=0;$j<$i;$j++)
            {
                if($this->values[$j] < $this->values[$i])
                {
                    swap( $this->values[$j] , $this->values[$i] );
                    swap( $this->names[$j] , $this->names[$i] );
                    swap( $this->colors[$j] , $this->colors[$i] );
                }
            }
        }
        $this->values = array_slice($this->values, 0, $num);
        $this->names = array_slice($this->names, 0, $num);
        $this->colors = array_slice($this->colors, 0, $num);
        $this->count = count($this->values);

        if($this->count)
        {
            $this->maxValue = $this->values[0];
            $this->sum = 0;
            foreach($this->values as $value)
            {
                $this->sum += $value;
                if($this->maxValue < $value)
                    $this->maxValue = $value;
            }
        }
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
