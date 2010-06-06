<?php
if (!defined('MEDIAWIKI')) die();
global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/DAO/SurveyDAO.php");
require_once("$vgPath/graph/Graph.php");
require_once("$vgPath/DAO/VoteDAO.php");

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
    /** @var Boolean */  protected $show_phones = false;
    /** @var Boolean */  protected $show_graph = false;
    /** @var Boolean */  protected $has_control = false;

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
    function setShowNumbers($show)
    {
        $this->show_phones = $show;
    }
    function setHasControl($control)
    {
        $this->has_control = $control;
    }
    function setShowGraph($show)
    {
        $this->show_graph = $show;
    }
    /**
     *
     * @param $choice String value of choice
     * @param $image String path to image used as bullet
     * @param $addtext String Put Extra HTML after this choice
     * @return String HTML code
     */
    static private function getChoiceHTML($choice, $color, $addtext='', $vote='', $voteid='', $style='')
    {
        $output = "<div style=''>".''."<div style=\"display: block; width: 340px;$style\">";

        if($vote)
            $output .= "<li STYLE=\"list-style: square inside none; color: #$color\">$vote";
        else
            $output .= "<li STYLE=\"list-style: square inside none; color: #$color\">";

        $output .= "<label style=\"color: black\" for=\"$voteid\">$choice $addtext</label></li>";

        return $output.'</div></div>';
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
        $userhasvoted = false;
        $surveys =& $this->page->getSurveys();
        $pagestatus = $this->page->getStatus();

        foreach ($surveys as &$survey)
        {
            /* @var $survey SurveyVO */
            $choices = $survey->getChoices();

            if($this->type != vSIMPLE_SURVEY)
            {
                $output .= '<h5>'. wfMsg( 'survey-question', $this->parser->run( $survey->getQuestion() ) ).'</h5>';
            }

            $output.='<ul>';
            if($pagestatus == 'ready')
            {
                foreach ($choices as &$choice)
                {
                    /* @var $choice ChoiceVO */
                    $name = $this->parser->run($choice->getChoice());
                    $output.=SurveyBody::getChoiceHTML($name, vfGetColor());
                }
            }
            elseif($pagestatus == 'active')
            {
                global $vgDB, $vgDBPrefix;
                $sql ="select choiceID from {$vgDBPrefix}surveyrecord where voterID = ? and surveyID = ? and presentationID = ? order by voteDate desc";
                $prev_vote = $vgDB->GetOne($sql, array(vfUser()->getName(), $survey->getSurveyID(), 0 ));
                if($prev_vote)
                    $userhasvoted=true;
                foreach ($choices as &$choice)
                {
                    /* @var $choice ChoiceVO */
                    $color = vfGetColor();
                    $name = $this->parser->run($choice->getChoice());
                    $extra='';
                    if($this->show_phones)
                    {
                        $extra='<span style="background-color: #E9F3FE">'
                                //.'<font color="#AAAAAA">Phone number:</font> '
                                .''.vfColorizePhone( $choice->getReceiver(), true ).''
                                .'</span>';
                    }
                    $vote = '';
                    $voteid = '';

                    if($prev_vote == $choice->getChoiceID())
                    {
                        $style = "border:1px dashed gray; background-color:#F5F5F5; padding-left: 9px;";
                        $checked = ' checked';
                    }
                    else
                    {
                        $style = '';
                        $checked = '';
                    }

                    if($this->show_voting && $prev_vote == false)
                    {
                        $voteid = "q{$this->page->getPageID()}-{$survey->getSurveyID()}-{$choice->getChoiceID()}";
                        $vote = "<input id=\"$voteid\" type=radio name=\"survey{$survey->getSurveyID()}\" value=\"{$choice->getChoiceID()}\" $checked/>";
                    }
                    $output.=SurveyBody::getChoiceHTML($name, $color, $extra, $vote, $voteid, $style);
                }
                $output.="<input type=hidden name='surveylist[]' value='{$survey->getSurveyID()}' />";
            }
            elseif($pagestatus == 'ended')
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
                    $color = vfGetColor();
                    $percent = substr(100.0 * $choice->getVote() / $numvotes, 0, 5);
                    $width = 290.0 * $choice->getVote() / $numvotes;
                    $name = $this->parser->run($choice->getChoice());
                    if($percent)
                        $extra = "<br><div style=\"background-color:#$color; width: {$width}px; height: 10px; display:inline-block\"> </div> $percent% ({$choice->getVote()})";
                    else
                        $extra = '';
                    $output.=SurveyBody::getChoiceHTML($name, $color, $extra);
                }
            }
            $output.='</ul>';
        } // foreach Survey

        //Show help message
        if($pagestatus == 'active' && $this->has_control)
        {
            global $vgSmsNumber;
            global $vgScript;
            $output .= "<div class=\"successbox\" style=\"margin: 1em; float: none; clear: both;\">"
                    ."In order to vote: <ul>";
            if($this->page->getPhoneVoting() != 'no')
            {
                $output .= "<li><img src=\"$vgScript/icons/phone.png\"> Ring a <u>number above</u>; (you will hear a busy tone).</li>";
                global $vgEnableSMS;
                if($vgEnableSMS)
                {
                    $output .= "<li><img src=\"$vgScript/icons/mobile.png\"> Sent SMS to <b>".vfColorizePhone( $vgSmsNumber )."</b> with <font color=red>red digits</font> corresponding to your choice.</li>";
                }
            }
            if($this->page->getWebVoting() != 'no')
            {
                global $wgServer;
                $output .= "<li><img src=\"$vgScript/icons/laptop.png\"> Visit our webpage <code>$wgServer</code></li>";
            }
            $output .= "</ul></div><div class=\"visualClear\"></div>";
        }

        //Display how much time has left
        if($pagestatus == 'active')
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
                    location.reload(true);
                c=vTimeleft%60+' seconds';
                if(Math.floor(vTimeleft/60))
                    c=Math.floor(vTimeleft/60) + ' minutes ' + c;
                document.getElementById(\"$id\").innerHTML=c;
                setTimeout(\"updateTimeLeft()\",999);
                vTimeleft--;
            };
            updateTimeLeft();
            </script>";
            $script = preg_replace('/^\s+/m', '', $script);
            $output.= str_replace("\n", "", $script); //Mediawiki will otherwise ruin this script
        }

        if($pagestatus == 'active' && $userhasvoted == false)
            $this->show_graph = false;

        if($this->has_control)
            $this->show_graph = true;

        if($pagestatus == 'ready')
            $this->show_graph = false;

        if($this->page->getShowGraphEnd() && $pagestatus != 'ended')
            $this->show_graph = false;

        if($this->show_graph)
        {
            //insert graph image at the beginning
            $imgid = 'gr'.$this->page->getPageID().'_'.rand();
            //Prepend this image!
            $output = '<div style="float:right">'.$this->getGraphHTML($imgid).'</div>' . $output;
            global $vgImageRefresh;
            if($pagestatus == 'active' && $vgImageRefresh)
            {
                $output .= $this->refreshImage($imgid, $this->page->getPageID());
            }
        }
        return $output;
    }
    function refreshImage($imgid, $page_id)
    {
        global $vgImageRefresh;
        $now = time();
        $script = "<script>
        function refresh$imgid()
        {
            sajax_do_call('SurveyBody::ajaxgraph', [time$imgid, $page_id],function(o) {
                graph=document.getElementById('$imgid');
                if(o.responseText.length)
                {
                    resp = o.responseText.split('@');
                    if(graph.src!=resp[0])
                    {
                        time$imgid = resp[1];
                        graph.src = resp[0];
                    }
                }
                setTimeout(\"refresh$imgid()\",$vgImageRefresh*1000);
            });
        }
        var time$imgid = \"$now\";
        setTimeout(\"refresh$imgid()\",$vgImageRefresh*1000);
        /*alert(document.getElementById('$imgid').src);*/
        </script>";
        /*o.responseText*/
        $script = preg_replace('/^\s*/m', '', $script);
        return str_replace("\n", "", $script);
    }
    public function getGraphHTML($imgid = '')
    {
        $surveys =& $this->page->getSurveys();
        $graph = new Graph('pie');
        if(count($surveys) > 1)
            $graph->setType('stackpercent');

        foreach($surveys as &$survey)
        {
            /* @var $survey SurveyVO */
            $graphseries = new GraphSeries( vfWikiToText($survey->getQuestion()) ); //@todo remove extra things
            $choices = &$survey->getChoices();
            foreach($choices as &$choice)
            {
                /* @var $choice ChoiceVO */
                $color = vfGetColor();
                $graphseries->addItem(vfWikiToText($choice->getChoice()), $choice->getVote(), $color);
            }
            if($this->page->getDisplayTop())
            {
                $graphseries->sortOnlyTop($this->page->getDisplayTop());
            }
            $graph->addSeries($graphseries);
        }
        if($imgid)
            return $graph->getHTMLImage($imgid);
        return $graph->getImageLink();
    }
    static function ajaxgraph($last_refresh, $page_id)
    {
        if(VoteDAO::countNewVotes($page_id, $last_refresh) == 0)
            return ''; // there are no new votes

        //@todo check permisions
        $now = time();
        $sdao = new SurveyDAO();
        $page = $sdao->findByPageID($page_id);
        $surveybody = new SurveyBody($page, new MwParser(new Parser()));
        return $surveybody->getGraphHTML()."@".$now;
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
        return SurveyBody::getChoiceHTML( $p->run(trim($line), false), vfGetColor());
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
            $output .= $p->run($title, false);
        }
        $output .= '<ul style="margin: 0.2em;">';
        foreach($lines as $line)
        {
            $line = trim($line);
            if($line)
            {
                $output .= SurveyBody::getChoiceHTML( $p->run($line, false) , vfGetColor());
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
