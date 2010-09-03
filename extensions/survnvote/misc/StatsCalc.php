<?php

class StatsCalc
{
    private $sum;
    private $sumSq;
    private $num;

    public function __construct()
    {
        $this->sum = 0;
        $this->sumSq = 0;
        $this->num = 0;
    }
    function add($v, $n = 1)
    {
        $this->sum += $v * $n;
        $this->sumSq += $v * $v * $n;
        $this->num += $n;
    }
    function getNum()
    {
        return $this->num;
    }
    function getAverage()
    {
        if($this->num)
            return $this->sum / $this->num;
        else
            return 0;
    }
    function getVariance()
    {
        $n = $this->num;
        if($n)
            return ( $this->sumSq - (( $this->sum * $this->sum) / $n) ) / ($n - 1);
        else
            return 0;
    }
    function getStdDev()
    {
        return sqrt($this->getVariance());
    }
    function getStdError()
    {
        if($this->num)
            return $this->getStdDev() / sqrt( $this->num );
        else
            return 0;
    }
    function getConfidence95()
    {
        $a = $this->getAverage();
        $e = $this->getStdError();
        return array($a - 1.96 * $e, $a + 1.96 * $e);
    }
    function getHTML($title)
    {
        if($this->getNum() < 2)
            return '';
        $out = '<table class="wikitable">';
        $out .= '<caption>'.$title.'</caption>';
        $out .= sprintf("<tr><td width=\"140px\">Sample size<td>%d</tr>", $this->getNum());
        $out .= sprintf("<tr><td>Mean<td>%.3f</tr>", $this->getAverage());
        list($clow, $chigh) = $this->getConfidence95();
        $out .= sprintf("<tr><td>Confidence Interval<br/>@ 95%%<td>[%.3f - %.3f]<br/>n=%d</tr>", $clow, $chigh,$this->getNum());
        $out .= sprintf("<tr><td>Standard Deviation<td>%.3f</tr>", $this->getStdDev());
        $out .= sprintf("<tr><td>Standard Error<td>%.3f</tr>", $this->getStdError());
        $out .= '</table>';
        return $out;
    }
}

/*
$d = array(
    array(1,	84),
    array(2,	90),
    array(3,	172),
    array(4,	273),
    array(5,	241),
    array(6,	214),
    array(7,	191),
    array(8,	84),
);
$a = new StatsCalc();
foreach($d as $row)
{
    $a->add($row[0], $row[1]);
}
echo $a->getAverage()."\n";
echo $a->getVariance()."\n";
echo $a->getStdDev()."\n";
echo $a->getStdError()."\n";
var_dump($a->getConfidence95());

*/