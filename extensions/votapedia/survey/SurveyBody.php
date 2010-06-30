<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SurveyView
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/misc/Common.php");
require_once("$vgPath/graph/Graph.php");
require_once("$vgPath/DAO/VoteDAO.php");
require_once("$vgPath/DAO/PageDAO.php");
require_once("$vgPath/Survey/SurveyTimer.php");
require_once("$vgPath/misc/StatsCalc.php");

/**
 * SurveyBody shows the main part of the survey.
 *
 * @author Emir Habul
 * @package SurveyView
 */
class SurveyBody
{
    /** @var UserVO */   protected $user;
    /** @var PageVO */   protected $page;
    /** @var MwParser */ protected $parser;
    /** @var Integer */  protected $type;
    /** @var Boolean */  protected $show_voting = false;
    /** @var Boolean */  protected $show_phones = false;
    /** @var Boolean */  protected $show_graph = false;
    /** @var Boolean */  protected $has_control = false;
    /** @var Integer */  protected $presID;
    
    /**
     * 
     * @param UserVO $user
     * @param PageVO $page
     * @param MwParser $parser
     * @param Integer $presID which presentation to show
     */
    function  __construct(UserVO &$user, PageVO &$page, MwParser &$parser, $presentationID)
    {
        $this->user =& $user;
        $this->page =& $page;
        $this->parser =& $parser;
        $this->type = vSIMPLE_SURVEY;
        $this->presID = $presentationID;
        $this->votescount = VoteDAO::getNumVotes($this->page, $this->presID);
    }

    /**
     * Change value of current presentation ID.
     *
     * @param Integer $presID
     */
    function setPresentationID($presID)
    {
        if($presID != $this->presID)
        {
            $this->presID = $presID;
            $this->votescount = VoteDAO::getNumVotes($this->page, $this->presID);
        }
    }
    /**
     * Should show voting options
     *
     * @param Boolean $show
     */
    function setShowVoting($show)
    {
        $this->show_voting = $show;
    }
    /**
     * Should show phone numbers
     *
     * @param Boolean $show
     */
    function setShowNumbers($show)
    {
        $this->show_phones = $show;
    }
    /**
     * Does this user have control.
     *
     * @param Boolean $control
     */
    function setHasControl($control)
    {
        $this->has_control = $control;
    }
    /**
     * Should show the graph on the survey.
     *
     * @param Boolean $show
     */
    function setShowGraph($show)
    {
        $this->show_graph = $show;
    }
    /**
     * Get HTML code for one choice
     *
     * @param String $choice value of choice
     * @param String $addtext Put Extra HTML after this choice
     * @return String HTML code
     */
    static protected function getChoiceHTML($choice, $color, $addtext='', $vote='', $voteid='', $style='')
    {
        //width: 280px;
        //width: 340px;
        $output = "<div class='surChoice' style=\"display: block; $style\">";

        if($vote)
            $output .= "<li STYLE=\"list-style: square inside none; color: #$color\">$vote";
        else
            $output .= "<li STYLE=\"list-style: square inside none; color: #$color\">";

        $output .= "<label style=\"color: black\" for=\"$voteid\">$choice $addtext</label></li>";

        return $output.'</div>';
    }
    /**
     * Get Body HTML
     *
     * @return String html code
     */
    function getHTML()
    {
        $output = '';
        $this->userhasvoted = false;
        $surveys =& $this->page->getSurveys();
        $this->pagestatus = $this->page->getStatus($this->presID);

        $colorindex = 1;
        $numsurvey = 1;

        $divid = 'div'.rand().'Q';
        if($this->pagestatus == 'active')
        {
            $output .= $this->slideSurveys( $divid, count($surveys) );
        }
        
        foreach ($surveys as &$survey)
        {
            $output .= '<div id="'.$divid.$numsurvey.'">';
            $output .= $this->getOneSurvey($survey, $colorindex);
            $output .= '</div>';
            
            $numsurvey++;
        }

        if($this->pagestatus == 'active')
        {
            $output .= $this->slideSurveysBottom( $divid, count($surveys) );
        }
        
        //Show help message
        if($this->pagestatus == 'active' && $this->has_control)
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
        else
        {
            $output .= '<br>';
        }

        //Display how much time has left
        if($this->pagestatus == 'active')
        {
            $timeleft = strtotime($this->page->getEndTime()) - time();
            $divid='tl_'.$this->page->getPageID().'_'.rand();
            $output.= "Time Left: ";

            $timer = new SurveyTimer();
            $output .= $timer->getJavascript($timeleft, $divid);
        }
        
        #echo $this->pagestatus.$this->userhasvoted.'<br>';

        if($this->pagestatus == 'active' && $this->userhasvoted == false)
            $this->show_graph = false;

        if($this->has_control)
            $this->show_graph = true;

        if($this->pagestatus == 'ready')
            $this->show_graph = false;

        if($this->page->getShowGraphEnd() && $this->pagestatus != 'ended')
            $this->show_graph = false;

        if($this->show_graph)
        {
            //insert graph image at the beginning
            $imgid = 'gr'.$this->page->getPageID().'_'.$this->presID.'_'.rand();
            //Prepend this image!
            $tmpvar = 1;
            $output = '<div style="float:right; text-align: center;">'
                    .$this->getGraphHTML($tmpvar, $this->page->getSurveys(), $this->page->getPageID(), $imgid)
                    .'</div>' . $output;
            global $vgImageRefresh;
            if($this->pagestatus == 'active' && $vgImageRefresh)
            {
                $output .= $this->refreshImage($imgid, 1, $this->page->getPageID());
            }
        }
        return $output;
    }
    /**
     * Get a code for collapsing/expanding survey questions (above questions).
     *
     * @param Integer $id previx of html div id
     * @param Integer $num number of questions
     * @return String HTML code
     */
    function slideSurveys( $id, $num)
    {
        if($num < 2)
            return '';
        global $wgOut, $vgScript;

        $out = '<div>';
        $out .= "<div id=\"btn_collapse\" style=\"display:none\"><img src='$vgScript/icons/collapse.png' /> <a href=\"#\" onclick=\"sur_collapse('$id',$num);return false;\">collapse</a> </div>";
        $out .= "<div id=\"btn_expand\" style=\"display:none\"><img src='$vgScript/icons/expand.png' /> <a href=\"#\" onclick=\"sur_expand('$id',$num);return false;\">expand</a> </div>";
        $out .= '</div>';

        vfAdapter()->addScript($vgScript. '/survey.js');

        $script = "<script>document.getElementById('btn_collapse').style.display = 'inline';</script>";
        $script = preg_replace('/^\s+/m', '', $script);
        $out.= str_replace("\n", "", $script); //Mediawiki will otherwise ruin this script
        return $out;
    }
    /**
     * Get a code for collapsing/expanding survey questions (bellow of questions).
     *
     * @param Integer $id previx of html div id
     * @param Integer $num number of questions
     * @return String HTML code
     */
    function slideSurveysBottom( $id, $num)
    {
        if($num < 2)
            return '';
        
        global $vgScript;
        $out = '&nbsp;';
        $out .= "<div id=\"btn_prev\" style=\"background-color: white; display:none; left:150px; position: absolute; \"><a href=\"#\" onclick=\"sur_prev('$id',$num);return false;\"><img src='$vgScript/icons/arrow_left.png' /> prev</a> </div>";
        $out .= "<div id=\"btn_next\" style=\"background-color: white; display:none; left:250px; position: absolute; \"><a href=\"#\" onclick=\"sur_next('$id',$num);return false;\">next <img src='$vgScript/icons/arrow_right.png' /></a> </div>";
        return $out;
    }
    /**
     * Get HTML code for one question of survey
     *
     * @param SurveyVO $survey
     * @param Integer $colorindex
     */
    function getOneSurvey(SurveyVO &$survey, &$colorindex)
    {
        $choices = $survey->getChoices();
        $output = '';

        if($this->type != vSIMPLE_SURVEY)
        {
            if($survey->getPoints())
            {
                $output .= '<h5>'. wfMsg('survey-question-p',
                        $this->parser->run( $survey->getQuestion() ), $survey->getPoints() ).'</h5>';
            }
            else
            {
                $output .= '<h5>'. wfMsg('survey-question',
                        $this->parser->run( $survey->getQuestion() ) ).'</h5>';
            }
        }

        $output.='<ul>';
        if($this->pagestatus == 'ready')
        {
            foreach ($choices as &$choice)
            {
                /* @var $choice ChoiceVO */
                $name = $this->parser->run($choice->choice);
                $output.=SurveyBody::getChoiceHTML($name, vfGetColor($colorindex) );
            }
        }
        elseif($this->pagestatus == 'active')
        {
            $prev_vote = VoteDAO::getPrevVote($this->user->userID, $survey->getSurveyID(), $this->presID );
            
            if($prev_vote)
                $this->userhasvoted=true;

            foreach ($choices as &$choice)
            {
                /* @var $choice ChoiceVO */
                $color = vfGetColor($colorindex);
                $name = $this->parser->run($choice->choice);
                $extra='';
                if($this->show_phones)
                {
                    //background-color: #E9F3FE; 
                    $extra='<div style="text-align: right; color: black">'
                            //.'<font color="#AAAAAA">Phone number:</font> '
                            .''.vfColorizePhone( $choice->receiver, true ).''
                            .'</div>';
                }
                $vote = '';
                $voteid = '';

                if($prev_vote == $choice->choiceID)
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
                    $voteid = "q{$this->page->getPageID()}-{$survey->getSurveyID()}-{$choice->choiceID}";
                    $vote = "<input id=\"$voteid\" type=radio name=\"survey{$survey->getSurveyID()}\" value=\"{$choice->choiceID}\" $checked/>";
                }
                $output.=SurveyBody::getChoiceHTML($name, $color, $extra, $vote, $voteid, $style);
            }
            $output.="<input type=hidden name='surveylist[]' value='{$survey->getSurveyID()}' />";
        }
        elseif($this->pagestatus == 'ended')
        {
            $prev_vote = VoteDAO::getPrevVote($this->user->userID, $survey->getSurveyID(), $this->presID );
            if($prev_vote)
                $this->userhasvoted=true;

            $numvotes = 0;
            foreach ($choices as &$choice)
            {
                /* @var $choice ChoiceVO */
                $numvotes += $this->votescount->get($survey->getSurveyID(), $choice->choiceID);
            }
            if($numvotes == 0)
                $numvotes = 1;
            $choicesout = array();
            $votesout = array();
            global $vgScript;
            foreach ($choices as &$choice)
            {
                /* @var $choice ChoiceVO */
                $color = vfGetColor($colorindex);
                $votes = $this->votescount->get($survey->getSurveyID(), $choice->choiceID);
                $percent = substr(100.0 * $votes / $numvotes, 0, 5);
                $width = 270.0 * $votes / $numvotes;
                $name = $this->parser->run($choice->choice);
                if($percent)
                    $extra = "<br><div style=\"background-color:#$color; width: {$width}px; height: 10px; display:inline-block\"> </div> $percent% ({$votes})";
                else
                    $extra = '';
                if($survey->getAnswer() == $choice->choiceID)
                {
                    /*if(! $prev_vote || $prev_vote == $choice->choiceID())
                        {
                            $name = "<u>" . $name . "</u> <img src='$vgScript/icons/correct.png' />";
                        }*/
                    $name = "<u>" . $name . "</u> <img src='$vgScript/icons/correct.png' />";
                    $style = "border:0px dashed gray; background-color:#CFFFCF; padding-left: 9px;";
                }
                else
                {
                    /*if($prev_vote == $choice->choiceID)
                        {
                            $name .= " <img src='$vgScript/icons/wrong.png' />";
                            $style = "border:0px dashed gray; background-color:#FFCFCF; padding-left: 9px;";
                        }
                        else
                        {
                            $style = '';
                        }*/
                    $style = '';
                }
                $choicesout[] = SurveyBody::getChoiceHTML($name, $color, $extra, '', '', $style);
                $votesout[] = $votes;
            }
            // uncomment this line to sort by the number of votes
            // array_multisort($votesout, SORT_DESC, SORT_NUMERIC, $choicesout);
            $output .= join('',$choicesout);
        }
        $output.='</ul>';

        return $output;
    }
    /**
     * Get HTML code that will refresh graph image every $vgImageRefresh seconds
     *
     * @param String $imgid
     * @param Integer $colorindex
     * @param Integer $page_id
     * @param Integer $survey_id
     * @return String
     */
    function refreshImage($imgid, $colorindex, $page_id, $survey_id = null)
    {
        if($survey_id)
        {
            $page_id .= ", $survey_id";
        }
        global $vgImageRefresh;

        $lastchoiceid = 0; //@todo get proper value here

        $script = "<script>
        function refresh$imgid()
        {
            sajax_do_call('SurveyBody::ajaxgraph', [time$imgid, $colorindex, {$this->presID}, $page_id],function(o) {
                graph=document.getElementById('$imgid');
                if(o.responseText.length)
                {
                    resp = o.responseText.split('@');
                    if(graph.src!=resp[0])
                    {
                        document.getElementById('totalvotes$imgid').innerHTML = resp[2];
                        time$imgid = resp[1];
                        graph.src = resp[0];
                    }
                }
                setTimeout(\"refresh$imgid()\",$vgImageRefresh*1000);
            });
        }
        var time$imgid = \"$lastchoiceid\";
        setTimeout(\"refresh$imgid()\",$vgImageRefresh*1000);
        </script>";
        /*o.responseText*/
        $script = preg_replace('/^\s*/m', '', $script);
        return str_replace("\n", "", $script);
    }
    /**
     * Get more details about Survey.
     *
     * @return String
     */
    public function getDetailsHTML()
    {
        $output = '';
        $pagestatus = $this->page->getStatus($this->presID);
        $colorindex = 1; /* for showing items */
        $surveys =& $this->page->getSurveys();
        if($this->show_graph)
        {
            foreach($surveys as &$survey)
            {
                /* @var $survey SurveyVO */
                $tmpcolor = $tmpcolor2 = $colorindex;
                if($this->page->getType() != vSIMPLE_SURVEY)
                {
                    $question = wfMsg( 'survey-question', $this->parser->run( $survey->getQuestion() ));
                    $output .= "<h2>$question</h2>";
                }
                //insert graph image at the beginning
                $imgid = 'gr'.$this->page->getPageID().'_'.$this->presID.'_'.$survey->getSurveyID().'_'.rand();
                if( count($surveys) > 1 )
                {
                    //Prepend this image!
                    $output .= '<div style="text-align: center; width: 400px;">'.$this->getGraphHTML($colorindex, array($survey), $this->page->getPageID(), $imgid).'</div>';
                }
                global $vgImageRefresh;
                if($pagestatus == 'active' && $vgImageRefresh)
                {
                    $output .= $this->refreshImage($imgid, $tmpcolor, $this->page->getPageID(), $survey->getSurveyID());
                }
                //$tmpcolor = $colorindex;
                $output .= $this->getSurveyStats($survey, $tmpcolor2, $tmpcolor);
            }
        }
        return $output;
    }
    /**
     * Get HTML code that holds a graph
     *
     * @param Integer $colorindex
     * @param Array $surveys
     * @param Integer $pageID
     * @param Integer $presentationID
     * @param String $imgid
     * @return String
     */
    public function getGraphHTML(&$colorindex, $surveys, $pageID, $imgid = '')
    {
        /* @var $graph Graph */
        if(count($surveys) > 1)
            $graph = new GraphStackPercent();
        else
            $graph = new GraphPie();

        if(vfAdapter()->isMobile())
        {
            $graph->setWidth(290);
        }

        $usetransp = false;
        if($this->page->bgimage && !vfAdapter()->isMobile())
        {
            $img = vfAdapter()->filePath($this->page->bgimage);
            if($img)
            {
                $graph->setBackgroungImage($img);
                $usetransp = true;
            }
        }
        $totalvotes = 0;
        foreach($surveys as &$survey)
        {
            /* @var $survey SurveyVO */
            $graphseries = new GraphSeries( vfWikiToText($survey->getQuestion()) );
            if($usetransp)
            {
                $graphseries->setTransparent('DD');
            }
            $choices = &$survey->getChoices();
            foreach($choices as &$choice)
            {
                /* @var $choice ChoiceVO */
                $color = vfGetColor($colorindex);
                $votes = $this->votescount->get($survey->getSurveyID(), $choice->choiceID);
                $totalvotes += $votes;
                $graphseries->addItem(vfWikiToText($choice->choice), $votes, $color);
            }
            if($this->page->getDisplayTop())
            {
                $graphseries->sortOnlyTop($this->page->getDisplayTop());
            }
            else //if($this->page->getStatus($this->presID) == 'ended')
            {
                $graphseries->sort();
            }
            $graph->addValues($graphseries);
        }
        if($imgid)
        {
            $out = $graph->getHTMLImage($imgid);
            $out .= "<br>Number of votes: <span id='totalvotes$imgid'>$totalvotes</span>";
            return $out;
        }
        else
        {
            return array($graph->getImageLink(), $totalvotes);
        }
    }
    /**
     * Get link to graph from ajax
     *
     * @param Integer $last_choiceID
     * @param Integer $colorindex
     * @param Integer $page_id
     * @param Integer $presID
     * @param Integer $survey_id
     * @return String
     */
    static function ajaxgraph($last_choiceID, $colorindex, $presID, $page_id, $survey_id = null)
    {
        $page_id = intval($page_id);
        $presID = intval($presID);
        $last_choiceID = intval($last_choiceID);
        
        list($newcount, $newchoiceid) = VoteDAO::countNewVotes($page_id, $presID, $last_choiceID);
        
        if($newcount == 0)
            return ''; // there are no new votes

        //@todo check permisions
        $pagedao = new PageDAO();
        $page = $pagedao->findByPageID($page_id);
        $surveybody = new SurveyBody(vfUser()->getUserVO(), $page, new MwParser(new Parser()), $presID);
        
        if($survey_id)
        {
            list($link, $totalvotes) = $surveybody->getGraphHTML($colorindex, array($page->getSurveyBySurveyID($survey_id)),$page_id);
        }
        else
        {
            list($link, $totalvotes) = $surveybody->getGraphHTML($colorindex, $page->getSurveys(),$page_id);
        }
        return $link."@".$newchoiceid.'@'.$totalvotes;
    }
    /**
     * Parse multiline wiki code
     * 
     * @param String $title title of question
     * @param String $text multiline string
     *
     * @return String HTML code
     */
    static function getChoices($text, $title='')
    {
        $pars = new Parser();
        $p = new MwParser($pars);
        $lines = preg_split("/\n/",$text);
        $output = '';
        $colorindex = 1;
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
                $output .= SurveyBody::getChoiceHTML( $p->run($line, false) , vfGetColor($colorindex));
            }
        }
        $output .= '</ul>';
        return $output;
    }
    /**
     *
     * @param SurveyVO $survey
     * @param Integer $colorindex
     * @return <type> 
     */
    function getSurveyStats(SurveyVO &$survey, &$colorindex)
    {
        $choices = $survey->getChoices();
        $output = '';

        $statscals = new StatsCalc();
        $numvotes = 0;
        $highest = -1;
        $chnum = 1;
        foreach ($choices as &$choice)
        {
            /* @var $choice ChoiceVO */
            $votes = $this->votescount->get($survey->getSurveyID(), $choice->choiceID);
            $numvotes += $votes;
            $highest = max($highest, $votes);

            //add the choice number and number of votes to the statistics calculator
            $statscals->add($chnum, $votes);
            $chnum++;
        }
        if($numvotes == 0)
            $numvotes = 1;
        $output .= '<table width=100% class="sortable surtablestat" style="text-align:center; background-color: white">';
        $output .= "<tr><th width=\"35px\">#<th class=unsortable>Choice<th>Votes<th class=unsortable>%</tr>";
        global $vgScript;
        $chnum = 1;
        foreach ($choices as &$choice)
        {
            /* @var $choice ChoiceVO */
            $color = vfGetColor($colorindex);
            $votes = $this->votescount->get($survey->getSurveyID(), $choice->choiceID);
            $percent = substr(100.0 * $votes / $numvotes, 0, 5);
            $width = 270.0 * $votes / $numvotes;
            $name = $this->parser->run($choice->choice);
            $class = '';
            if($highest && $votes == $highest)
            {
                $class .= 'votehighest';
            }
            $extra = '';
            if($survey->getAnswer() == $choice->choiceID)
            {
                $extra = "<td><img src='$vgScript/icons/correct.png' />";
            }
            $colorpatch = "<div style=\"width: 25px; background-color: #$color\">$chnum</div>";
            $output .= "<tr class=\"$class\"><td>$colorpatch<td align=left> &nbsp; $name<td>$votes<td>$percent%$extra</tr>";
            $chnum++;
        }
        $output .= '</table>';
        if($highest)
        {
            $output .= '<table class="wikitable">';
            $output .= sprintf("<tr><td width=\"200px\">Sample size<td>%d</tr>", $statscals->getNum());
            $output .= sprintf("<tr><td width=\"200px\">Mean<td>%.3f</tr>", $statscals->getAverage());
            list($clow, $chigh) = $statscals->getConfidence95();
            $output .= sprintf("<tr><td>Confidence Interval<br>@ 95%%<td>[%.3f - %.3f]<br>n=%d</tr>", $clow, $chigh,$statscals->getNum());
            $output .= sprintf("<tr><td>Standard Deviation<td>%.3f</tr>", $statscals->getStdDev());
            $output .= sprintf("<tr><td>Standard Error<td>%.3f</tr>", $statscals->getStdError());
            $output .= '</table>';
        }

        return $output;
    }
}

/**
 *
 * Body of a questionnaire
 * @package SurveyView
 */
class QuestionnaireBody extends SurveyBody
{
    /**
     * @param UserVO $user
     * @param PageVO $page
     * @param MwParser $parser
     * @param Integer $presentationID
     */
    function  __construct(UserVO &$user, PageVO &$page, MwParser &$parser, $presentationID)
    {
        parent::__construct($user, $page, $parser, $presentationID);
        $this->type = vQUESTIONNAIRE;
    }
}
/**
 *
 * Body of a questionnaire
 * @package SurveyView
 */
class QuizBody extends QuestionnaireBody
{
    /**
     * Construct QuizBody
     *
     * @param UserVO $user
     * @param PageVO $page
     * @param MwParser $parser
     * @param Integer $presentationID
     */
    function  __construct(UserVO &$user, PageVO &$page, MwParser &$parser, $presentationID)
    {
        parent::__construct($user, $page, $parser, $presentationID);
        $this->type = vQUIZ;
    }
}

