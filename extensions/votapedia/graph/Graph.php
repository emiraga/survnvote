<?php
if (!defined('MEDIAWIKI')) die();

/**
 * function vfCutEncode
 * Cut long messages and URL encode them
 *
 * @param $str String message
 * @param $maxlen Integer maximum length of the message
 * @param $elipsis String append to the end of long messages
 * @param $encode Boolean do URL encoding
 * @return String processed string
 * @package Graphing
 */
function vfCutEncode($str, $maxlen, $elipsis = '...', $encode = true)
{
    if(strlen($str) >= $maxlen)
        $str = substr($str,0,$maxlen-strlen($elipsis)).$elipsis; //@todo remove crazy characters
    if($encode)
        $str = urlencode($str);
    return $str;
}
/**
 * class Graph
 * Used for drawing charts. It provides an image with chart.
 *
 * @author Emir Habul <emiraga@gmail.com>
 * @package Graphing
 * @abstract
 */
abstract class Graph
{
    protected $width = 400;
    protected $height = 200;
    protected $graphvalues = array();
    /**
     * Add values object GraphValues to the graph.
     *
     * @param $val GraphValues
     */
    function addValues(GraphValues $val)
    {
        $this->graphvalues[] = $val;
    }
    /**
     * Get link to image;
     *
     * @return String link to the image of graph
     */
    public function getImageLink()
    {
        return "http://chart.apis.google.com/chart?".$this->getImageParams();
    }
    /**
     * Get image parameters
     *
     */
    abstract protected function getImageParams();
    /**
     * Get HTML code that contains graph image.
     *
     * @param $imgid String HTML value of ID for image
     */
    public function getHTMLImage($imgid)
    {
        return "<img id=\"$imgid\" width=\"{$this->width}\" height=\"{$this->height}\" "
                ."src=\"{$this->getImageLink()}\">";
    }
    /**
     * Get width of an image
     *
     * @return Integer
     */
    function getWidth()
    {
        return $this->width;
    }
    /**
     * Get height of an image.
     *
     * @return Integer
     */
    function getHeight()
    {
        return $this->height;
    }
    /**
     * Set width of an image.
     *
     * @param $w Integer width
     */
    function setWidth($w)
    {
        $this->width = $w;
    }
    /**
     * Set height of image
     *
     * @param $h Integer height
     */
    function setHeight($h)
    {
        $this->height = $h;
    }
}
/**
 * class GraphLine
 *
 * @package Graphing
 */
class GraphLine extends Graph
{
    public function getImageParams()
    {
        $imglink = "cht=lc&chs={$this->width}x{$this->height}";
        $values = $this->graphvalues[0];
        if($values->getCount())
        {
            $maxv = $values->getMaxValue();
            $imglink .= "&chd=t:".$values->getValuesFormat();
            $imglink .= "&chds=0,".($maxv);
            $imglink .= "&chxt=y&chxl=0:|RM0|RM$maxv";
        }
        return $imglink;
    }
}
/**
 * class GraphLineXY
 *
 * @package Graphing
 */
class GraphLineXY extends Graph
{
    public function getImageParams()
    {
        $imglink = "cht=lxy&chs={$this->width}x{$this->height}";
        $values = $this->graphvalues[0];
        if($values->getCount())
        {
            $imglink .= "&chd=t:".$values->getXFormat(',').'|'.$values->getYFormat(',');
            $imglink .= "&chds={$values->getYMin()},{$values->getYMax()}";
            $imglink .= "&chxt=y,x&chxl=0:|{$values->getYlabel()}|1:|{$values->getXlabel()}";
            $imglink .= "&chm=s,000000,0,-1,3";
        }
        return $imglink;
    }
}
/**
 * class GraphPie
 *
 * @package Graphing
 */
class GraphPie extends Graph
{
    public function getImageParams()
    {
        $values = $this->graphvalues[0];
        $imglink = "cht=p3&chs={$this->width}x{$this->height}";
        $colors = $values->getColorsFormat();
        if($colors)
            $imglink.="&chco=$colors";
        if($values->getCount())
        {
            $imglink.="&chd=t:{$values->getValuesFormat()}";
            if($values->getCount() <= 11)
                $imglink.="&chdl=".$values->getNamesFormat(30, true);
            else
                $imglink.="&chl=".$values->getNamesFormat(30, true);
        }
        return $imglink;
    }
}
/**
 * class GraphMultiPie
 *
 * @package Graphing
 */
class GraphMultiPie extends Graph
{
    public function getImageParams()
    {
        $data = array();
        $colors = array();
        $names = array();
        foreach($this->graphvalues as $values)
        {
            $v = $values->getValuesFormat();
            if($v)
            {
                if($values->getCount() == 1)
                    $v .= ',0';
                $data[] = $v;
                $colors[] = $values->getColorsFormat();
                $names[] = $values->getNamesFormat(30, true);
            }
        }
        $data = 't:'.join('|',$data);
        $colors = join(',', $colors);
        $names = join('|', $names);

        $imglink = "cht=pc"
                ."&chs={$this->width}x{$this->height}&chd=$data";
        if($colors)
            $imglink .= "&chco=$colors";
        if($names)
            $imglink .= "&chdl=$names";
        return $imglink;
    }
}
/**
 * class GraphStackPercent
 *
 * @package Graphing
 */
class GraphStackPercent extends Graph
{
    public function getImageParams()
    {
        $numvalues = count($this->graphvalues);
        $labelLength = 3;
        if($numvalues == 2)     $labelLength = 25;
        elseif($numvalues == 3) $labelLength = 18;
        elseif($numvalues == 4) $labelLength = 13;
        elseif($numvalues == 5) $labelLength = 10;
        elseif($numvalues == 6) $labelLength = 8;
        elseif($numvalues == 7) $labelLength = 7;
        elseif($numvalues == 8) $labelLength = 6;
        $xlabel = '';
        $chbh = intval(($this->width - 60) / $numvalues);
        $maxv = 0;
        $markers = array();
        foreach($this->graphvalues as &$values)
        {
            $xlabel .= '|'. vfCutEncode($values->getTitle(),$labelLength, '...', false);
            if($values->getCount() > $maxv)
                $maxv = $values->getCount();
        }
        $data = array();
        $colors = array();
        for($i = $maxv-1; $i>=0; $i--)
        {
            $barvalues = array();
            $barcolors = array();
            $s = 0;
            foreach($this->graphvalues as &$values)
            {
                $val = $values->getValue($i);
                if($val === false)
                {
                    $barvalues[] = '0';
                    $barcolors[] = '000000';
                }
                else
                {
                    $barvalues[] = vfCutEncode(100 * $val / $values->getSum(), 5, '');
                    $barcolors[] = $values->getColor($i);
                    $pos = $maxv-$i-1;
                    $markers[] = "t". vfCutEncode( $values->getName($i), $labelLength, '' )
                            .' ('.$val.')'.",000000,$pos,$s,12,,c";
                }
                $s++;
            }
            $data[] = join(',', $barvalues);
            $colors[] = join('|', $barcolors);
        }
        /*$s = 0;
        foreach($this->graphvalues as &$values)
        {
            $pos = $maxv-1;
            $markers[] = "t".$values->getTitle().",000000,$pos,$s,12,,e::15";
            $s++;
        }*/
        $data = 't:'.join('|',$data);
        if($data == 't:')
            return '';
        $colors = join(',', $colors);
        $imglink = "cht=bvs&chs={$this->width}x{$this->height}&chbh=$chbh";
        $imglink .= "&chd=$data&chco=$colors&chxt=x,y&chxl=0:$xlabel&chxs=1N**%&chds=0";

        $imglink2 = $imglink . "&chm=".join('|', $markers);
        if(strlen($imglink2) < 1500)
            $imglink = $imglink2;

        return $imglink;
    }
}
/**
 * class GraphValues
 * 
 * @package Graphing
 * @abstract
 */
abstract class GraphValues
{
    protected $title;
    protected $count = 0;

    public function __construct($title)
    {
        $this->title = vfCutEncode($title, 50);
    }
    function getTitle()
    {
        return $this->title;
    }
    function getCount()
    {
        return $this->count;
    }
}
/**
 * class GraphSeries
 * Stores data for graphing used by Graph class
 *
 * @author Emir Habul <emiraga@gmail.com>
 * @package Graphing
 */
class GraphSeries extends GraphValues
{
    protected $names = array();
    protected $values = array();
    protected $colors = array();
    protected $sum = 0;
    protected $maxValue = 0;

    function reverseValues()
    {
        $this->names = array_reverse($this->names);
        $this->values = array_reverse($this->values);
        $this->colors = array_reverse($this->colors);
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
    function sort()
    {
        array_multisort($this->values, SORT_NUMERIC, SORT_DESC, $this->names, $this->colors);
    }
    function sortOnlyTop($num)
    {
        $this->sort();
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
/**
 * class GraphXY
 * Stores a (X, Y) coordinates of points that are plotted by Graph class.
 *
 * @author Emir Habul <emiraga@gmail.com>
 * @package Graphing
 */
class GraphXY extends GraphValues
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
        return $this->count;
    }
    public function addPoint($x, $y)
    {
        if($this->count == 0)
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
        $this->count++;
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

/**
 * class GraphXYdate
 * Stores a X,Y coordinates of points where X coordinate is a datetime value.
 * These points are plotted by Graph class.
 *
 * @author Emir Habul <emiraga@gmail.com>
 * @package Graphing
 */
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

