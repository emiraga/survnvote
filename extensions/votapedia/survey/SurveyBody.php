<?php
if (!defined('MEDIAWIKI')) die();
global $gvPath;
require_once("$gvPath/Common.php");

/**
 * SurveyBody shows the main part of the survey
 *
 * @author Emir Habul
 */
class SurveyBody
{
    /** @var PageVO */   protected $page;
    /** @var MwParser */ protected $parser;
    /** @var Integer */  protected $type;
    function  __construct(PageVO &$page, MwParser &$parser)
    {
        $this->page =& $page;
        $this->parser =& $parser;
        $this->type = vSIMPLE_SURVEY;
    }
    /**
     *
     * @global $wgOut OutputPage
     * @return String html code
     */
    function getHTML()
    {
        global $wgOut;
        $output = '';
        $surveys =& $this->page->getSurveys();
        foreach ($surveys as &$survey)
        {
            /* @var $survey SurveyVO */
            $choices = $survey->getChoices();

            $output.='<tr><td colspan="2">';
            if($this->type != vSIMPLE_SURVEY)
            {
                $output .= '<h5>'. wfMsg( 'survey-question', $survey->getQuestion() ) .'</h5>';
            }

            if($this->page->getStatus()=='ready')
            {
                $output.='<ul>';
                foreach ($choices as &$choice)
                {
                    /* @var $choice ChoiceVO */
                    $name = $this->parser->run($choice->getChoice());
                    $output.=SurveyBody::getChoiceHTML($name, vfGetColorImage());
                }
                $output.='</ul>';
            }
            elseif($this->page->getStatus() == 'active')
            {
                $output.='<ul>';
                foreach ($choices as &$choice)
                {
                    /* @var $choice ChoiceVO */
                    $name = $this->parser->run($choice->getChoice());
                    $output.=SurveyBody::getChoiceHTML($name, vfGetColorImage());
                    if($this->page->getPhoneVoting() != 'no')
                    {
                        $output.='<font color=#AAA>Phone Number: </font>';
                        $output.='<span style="background-color: #E9F3FE">';
                        $output.=''. $this->colorizePhone( $choice->getReceiver() );
                        $output.='</span>';
                    }
                }
                $output.='</ul>';
                $timeleft = strtotime($this->page->getEndTime()) - time();
                $id='timeleft_'.$this->page->getPageID().'_'.rand();
                $output.= "Time Left: ";
                $output.= "<noscript>" . $timeleft .'</noscript>';
$script=
"<script>
document.write('<span id=\"$id\">Loading...</span>');
var vTimeleft;
function updateTimeLeft(){
    if(vTimeleft<=0)
    {
        sajax_do_call('ProcessSurvey::maintenance',[{$this->page->getPageID()}], function(o)
        {
            javascript:location.reload(true);
        }
        return;
    }
    c=vTimeleft%60+' seconds';
    if(Math.floor(vTimeleft/60))
        c=Math.floor(vTimeleft/60) + ' minutes ' + c;
    document.getElementById(\"$id\").innerHTML=c;
    setTimeout(\"updateTimeLeft()\",999); vTimeleft--;
};
sajax_do_call('SurveyBody::ajaxTimeLeft',[{$this->page->getPageID()}], function(o)
{
    vTimeleft=parseInt(o.responseText);
    updateTimeLeft();
})</script>";
                $output.= str_replace("\n", "", $script);
            }
            elseif($this->page->getStatus() == 'ended')
            {
                $output.='<ul>';
                $numvotes = 0;
                foreach ($choices as &$choice)
                {
                    /* @var $choice ChoiceVO */
                    $numvotes += $choice->getVote();
                }
                if($numvotes == 0) $numvotes = 1;
                foreach ($choices as &$choice)
                {
                    /* @var $choice ChoiceVO */
                    $image = vfGetColorImage();
                    $percent = 100.0 * $choice->getVote() / $numvotes;
                    $name = $this->parser->run($choice->getChoice());
                    $output.=SurveyBody::getChoiceHTML($name, '');
                    $output .= "<img src='$image' align=top border=1 height=10 width='$percent' /> $percent% ({$choice->getVote()})";
                }
                $output.='</ul>';
            }
        }
        return $output;
    }
    static function ajaxTimeLeft($page_id)
    {
        global $gvPath;
        require_once("$gvPath/DAO/SurveyDAO.php");
        
        $s = new SurveyDAO();
        $page = $s->findByPageID($page_id);
        $timeleft = strtotime($page->getEndTime()) - time();
        return strval($timeleft);
    }
    function colorizePhone($phone)
    {
        global $vgSmsChoiceLen;
        return substr($phone, 0, -$vgSmsChoiceLen)
                . '<font color=red>'.substr($phone,-$vgSmsChoiceLen,$vgSmsChoiceLen).'</font>';
    }
    static private function getChoiceHTML($choice, $image, $addtext='')
    {
        return "<li STYLE=\"list-style-image: url(".$image.")\"> <label>$choice</label> $addtext</li>";
    }
    static function ajaxChoice($line)
    {
        global $wgParser;
        $p = new MwParser($wgParser);
        return SurveyBody::getChoiceHTML( $p->run(trim($line), false) , vfGetColorImage());
    }
    static function getChoices($text)
    {
        global $wgParser;
        $p = new MwParser($wgParser);
        $lines = split("\n",$text);
        $output = '';
        $output .= '<div>';

        foreach($lines as $line)
        {
            $line = trim($line);
            if($line)
            {
                $output .= SurveyBody::getChoiceHTML( $p->run($line, false) , vfGetColorImage());
            }
        }
        $output .= '</div>';
        return $output;
    }
}

class QuestionnaireBody extends SurveyBody
{
    /**
     *
     * @param  $page PageVO
     * @param  $parser MwParser
     */
    function  __construct(PageVO &$page, MwParser &$parser)
    {
        parent::__construct($page, $parser);
        $this->type = vQUESTIONNAIRE;
    }
}
?>