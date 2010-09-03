<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SurveyView
 */

/**
 * Display a javascript timer/countdown.
 * @package SurveyView
 */
class SurveyTimer
{
    /* Boolean */ private $refresh  = true;

    /**
     * Should current webpage be refreshed after timer has expired?
     *
     * @param Boolean $refresh
     */
    function setRefresh($refresh)
    {
        $this->refresh = $refresh;
    }
    /**
     *
     * @param Integer $timeleft seconds
     * @param String $id HTML id of timer
     * @return String javascript code
     */
    function getJavascript($timeleft, $id)
    {
        $timeleftstr = ($timeleft%60) .' seconds';
        if(intval($timeleft/60))
            $timeleftstr = intval($timeleft/60) . ' minutes '.$timeleftstr;
        $output= "<span id=\"$id\">".$timeleftstr.'</span>';
        $script=
                "<script type=\"text/javascript\">
            var vTimeleft$id=$timeleft;
            function updateTimeLeft$id(){
                if(vTimeleft$id<=0)";

        if($this->refresh)
            $script .= 'location.reload(true);';
        else
            $script .= "{ document.getElementById(\"$id\").innerHTML=\"finished\";return; }";

        $script .= "c=vTimeleft$id%60+' seconds';
                if(Math.floor(vTimeleft$id/60))
                    c=Math.floor(vTimeleft$id/60) + ' minutes ' + c;
                document.getElementById(\"$id\").innerHTML=c;
                setTimeout(\"updateTimeLeft$id()\",999);
                vTimeleft$id--;
            };
            updateTimeLeft$id();
            </script>";
        $script = preg_replace('/^\s+/m', '', $script);
        $output.= str_replace("\n", "", $script); //Mediawiki will otherwise ruin this script
        return $output;
    }
}

