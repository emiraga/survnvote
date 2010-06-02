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
class Graph {
    /** @var GraphSeries */ protected $series;
    
    public function __construct($type)
    {
        $this->type = $type;
    }
    public function addSeries($series)
    {
        $this->series = $series;
    }
    public function getHTMLImage()
    {
        if($this->type == 'pie')
            return "<img src=\"http://chart.apis.google.com/chart?cht=p3&chd={$this->series->getValuesFormat()}&chs=400x200&chdl={$this->series->getNamesFormat()}&chco={$this->series->getColorsFormat()}\" />";
        throw new Exception("Unknown graph type");
    }
}

class GraphSeries
{
    protected $title;
    protected $names = array();
    protected $values = array();
    protected $colors = array();
    protected $count;
    
    public function __construct($title)
    {
        $this->title = $title;
        $this->count = 0;
    }
    function getTitle()
    {
        return $this->title;
    }
    function addItem($name, $value)
    {
        $name = urlencode(substr($name,0,30));
        $this->names[] = $name;
        $value = urlencode($value);
        $this->values[] = $value;

        global $vgColors;
        $this->colors[] = $vgColors[++$this->count];
    }
    function getValuesFormat()
    {
        return "t:".join(",", $this->values);
    }
    function getNamesFormat()
    {
        return join("|", $this->names);
    }
    function getColorsFormat()
    {
        return join("|", $this->colors);
    }
}
