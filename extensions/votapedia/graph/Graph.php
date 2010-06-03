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
    public function addSeries(GraphSeries $series)
    {
        $this->series[] = $series;
    }
    public function getHTMLImage($id = 'graph')
    {
        if($this->type == 'pie' || count($this->series) <= 1)
        {
            $series = $this->series[0];

            $imglink = "http://chart.apis.google.com/chart?cht=p3&chd=t:{$series->getValuesFormat()}"
                    ."&chs={$this->width}x{$this->height}";
            $colors = $series->getColorsFormat();
            if($colors)
                $imglink.="&chco=$colors";
            $names = $series->getNamesFormat();
            if($names)
                $imglink.="&chdl={$series->getNamesFormat()}";

            return "<img id=\"$id\" src=\"$imglink\" />";
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
            $imglink = "http://chart.apis.google.com/chart?cht=pc"
                    ."&chs={$this->width}x{$this->height}&chd=$data";
            if($colors)
                $imglink .= "&chco=$colors";
            if($names)
                $imglink .= "&chdl=$names";
            return "<img id=\"$id\" src=\"$imglink\">";
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
            $imglink = "http://chart.apis.google.com/chart?cht=bvs"
                    ."&chs={$this->width}x{$this->height}&chd=$data&chbh=$chbh"
                    ."&chco=$colors&chxt=x,y&chxl=0:$xlabel&chxs=1N**%&chds=0";
            $maxsize = 1024;

            $imglink .= "&chm=".join('|', $markers);
            return "<img id=\"$id\" src=\"$imglink\">";
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

    public function __construct($title)
    {
        $maxtitle = 50;
        if(strlen($title) >= $maxtitle)
            $title = substr($title,0,$maxtitle-3).'...'; //@todo remove crazy characters
        $this->title = $title;
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
    function getTitle()
    {
        return $this->title;
    }
    function addItem($name, $value, $color)
    {
        $maxname = 30;
        if($value == 0)return;
        $this->sum += $value;

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
    function getNamesFormat()
    {
        return join("|", $this->names);
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
