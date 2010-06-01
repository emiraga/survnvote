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
    /** @var Boolean */  protected $show_voting = false;

    function  __construct(PageVO &$page, MwParser &$parser)
    {
        $this->page =& $page;
        $this->parser =& $parser;
        $this->type = vSIMPLE_SURVEY;
    }
    function setShowVoting($show)
    {
        $this->show_voting = $show;
    }
    /**
     *
     * @param $choice String value of choice
     * @param $image String path to image used as bullet
     * @param $addtext String Put Extra HTML after this choice
     * @return String HTML code
     */
    static private function getChoiceHTML($choice, $image, $addtext='', $vote='', $voteid='', $style='')
    {
        $output = "<div style=''>".$addtext."<div  style=\"display: block; width: 200px;$style\">";

        if($vote)
            $output .= "<li STYLE=\"list-style: none;\">$vote ";
        else
            $output .= "<li STYLE=\"list-style-image: url(".$image.");\"> ";

        $output .= "<label for=\"$voteid\">$choice</label> </li>";

        return $output.'</div>'.'</div>';
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
                $output .= '<h5>'. wfMsg( 'survey-question', $this->parser->run( $survey->getQuestion() ) ).'</h5>';
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
                global $vgDB, $vgDBPrefix;
                $sql ="select choiceID from {$vgDBPrefix}surveyrecord where voterID = ? and surveyID = ? and presentationID = ? order by voteDate desc";
                $prev_vote = $vgDB->GetOne($sql, array(vfUser()->getName(), $survey->getSurveyID(), 0 ));
                foreach ($choices as &$choice)
                {
                    /* @var $choice ChoiceVO */
                    $name = $this->parser->run($choice->getChoice());
                    $extra='';
                    if($this->page->getPhoneVoting() != 'no')
                    {
                        $extra='<span style="background-color: #E9F3FE; float: right; margin-right: 400px;">'
                                .'<font color="#AAAAAA">Number:</font> '
                                .$this->colorizePhone( $choice->getReceiver() )
                                .'</span>';
                    }
                    $vote = '';
                    $voteid = '';
                    if($prev_vote == $choice->getChoiceID())
                    {
                        $style = "border:1px dashed gray; background-color:#F5F5F5;";
                        $checked = ' checked';
                    }
                    else
                    {
                        $style = '';
                        $checked = '';
                    }

                    if($this->show_voting)
                    {
                        $voteid = "q{$this->page->getPageID()}-{$survey->getSurveyID()}-{$choice->getChoiceID()}";
                        $vote = "<input id=\"$voteid\" type=radio name=\"survey{$survey->getSurveyID()}\" value=\"{$choice->getChoiceID()}\" $checked/>";
                    }
                    $output.=SurveyBody::getChoiceHTML($name, vfGetColorImage(), $extra, $vote, $voteid, $style);
                }
                $output.="<input type=hidden name='surveylist[]' value='{$survey->getSurveyID()}' />";
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
     * @param $title String title of question
     * @param $text String multiline string
     *
     * @return String HTML code
     */
    static function getChoices($text, $title='')
    {
        $pars = new Parser();
        $p = new MwParser($pars);
        $lines = preg_split("/\n/",$text);
        $output = '';
        if($title)
        {
            $output .= $p->run($title, true);
        }
        $output .= '<ul style="margin: 0.2em;">';
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
class
QuestionnaireBody extends SurveyBody
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