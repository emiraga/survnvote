<?php
if (!defined('MEDIAWIKI')) die();
global $vgPath;
require_once("$vgPath/Common.php");

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

            if($this->type != vSIMPLE_SURVEY)
            {
                $output .= '<h5>'. wfMsg( 'survey-question', $survey->getQuestion() ) .'</h5>';
            }

            $output.='<ul>';
            if($this->page->getStatus()=='ready')
            {
                foreach ($choices as &$choice)
                {
                    /* @var $choice ChoiceVO */
                    $name = $this->parser->run($choice->getChoice());
                    $output.=SurveyBody::getChoiceHTML($name, vfGetColorImage());
                }
            }
            elseif($this->page->getStatus() == 'active')
            {
                foreach ($choices as &$choice)
                {
                    /* @var $choice ChoiceVO */
                    $name = $this->parser->run($choice->getChoice());
                    $extra='';
                    if($this->page->getPhoneVoting() != 'no')
                    {
                        $extra='<font color=#AAA>Phone Number: </font>'
                        .'<span style="background-color: #E9F3FE">'
                        .$this->colorizePhone( $choice->getReceiver() )
                        .'</span>';
                    }
                    $output.=SurveyBody::getChoiceHTML($name, vfGetColorImage(), $extra);
                }
            }
            elseif($this->page->getStatus() == 'ended')
            {
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

                    $extra = "<img src='$image' align=top border=1 height=10 width='$percent' /> $percent% ({$choice->getVote()})";
                    $output.=SurveyBody::getChoiceHTML($name, '', "<br />$extra");
                }
            }
            $output.='</ul>';
        } // foreach Survey
        
        //Display how much time has left
        if($this->page->getStatus() == 'active')
        {
            $timeleft = strtotime($this->page->getEndTime()) - time();
            $id='timeleft_'.$this->page->getPageID().'_'.rand();
            $output.= "Time Left: ";
            $timeleftstr = ($timeleft%60) .' seconds';
            if(intval($timeleft/60))
                $timeleftstr = intval($timeleft/60) . ' minutes '.$timeleftstr;
            $output.= "<span id=\"$id\">".$timeleftstr.'</span>';
            $script=
                    "<script>
            var vTimeleft=$timeleft;
            function updateTimeLeft(){
                if(vTimeleft<=0)
                    document.location.search = 'action=purge';
                c=vTimeleft%60+' seconds';
                if(Math.floor(vTimeleft/60))
                    c=Math.floor(vTimeleft/60) + ' minutes ' + c;
                document.getElementById(\"$id\").innerHTML=c;
                setTimeout(\"updateTimeLeft()\",999);
                vTimeleft--;
            };
            updateTimeLeft();
            </script>";
            $output.= str_replace("\n", "", $script); //Mediawiki will otherwise ruin this script
        }

        return $output;
    }
    /**
     * @param $phone String phone number
     * @return String HTML code with modified phone
     */
    function colorizePhone($phone)
    {
        global $vgSmsChoiceLen;
        return substr($phone, 0, -$vgSmsChoiceLen)
                . '<font color=red>'.substr($phone,-$vgSmsChoiceLen,$vgSmsChoiceLen).'</font>';
    }
    /**
     *
     * @param $choice String value of choice
     * @param $image String path to image used as bullet
     * @param $addtext String Put Extra HTML after this choice
     * @return String HTML code
     */
    static private function getChoiceHTML($choice, $image, $addtext='')
    {
        return "<li STYLE=\"list-style-image: url(".$image.")\"> <label>$choice</label> $addtext</li>";
    }
    /**
     * Parse text with wiki code
     *
     * @param $line string
     * @return String HTML code
     */
    static function ajaxChoice($line)
    {
        global $wgParser;
        $p = new MwParser($wgParser);
        return SurveyBody::getChoiceHTML( $p->run(trim($line), false), vfGetColorImage());
    }
    /**
     * Parse multiline wiki code
     *
     * @param $text string multiline string
     * @return String HTML code
     */
    static function getChoices($text)
    {
        global $wgParser;
        $p = new MwParser($wgParser);
        $lines = split("\n",$text);
        $output = '';
        $output .= '<ul  style="margin: 0.2em;">';
        foreach($lines as $line)
        {
            $line = trim($line);
            if($line)
            {
                $output .= SurveyBody::getChoiceHTML( $p->run($line, false) , vfGetColorImage());
            }
        }
        $output .= '</ul>';
        return $output;
    }
}
/**
 *
 * Body of a questionnaire
 *
 */
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