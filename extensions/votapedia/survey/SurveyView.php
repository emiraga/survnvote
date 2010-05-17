<?php
if (!defined('MEDIAWIKI')) die();

global $gvPath;
require_once("$gvPath/Common.php");
require_once("$gvPath/DAO/SurveyDAO.php");
require_once("$gvPath/survey/SurveyButtons.php");

/**
 * Class used to display parts of HTML related to the viewing of survey
 *
 * @author Emir Habul
 */
class SurveyView
{
    private $parser;
    /** @var PageVO */          private $page;
    /** @var String */          private $username;
    /** @var Integer */         private $page_id;
    /** @var Title */           protected $wikititle;
    /** @var SurveyButtons */   protected $buttons;
    
    /**
     * Function called for the &lt;SurveyChoice&gt; tag
     *
     * @param  $input String text between tags
     * @param  $args Array tag arguments
     * @param  $parser Parser of Mediawiki
     * @param  $frame
     */
    static function executeTag( $input, $args, $parser, $frame = NULL ) //do not change arguments
    {
        $page_id = intval(trim($input));
        try
        {
            #var_dump($parser);
            $mwparser = new MwParser($parser);
            $mwparser->setTag();
            $buttons = new SurveyButtons();
            $tag = new SurveyView($page_id, $mwparser, $buttons);
            return $tag->getHTMLBody();
        }
        catch(Exception $e)
        {
            return vfErrorBox($e->getMessage());
        }
    }
    /**
     * Function called for the Survey magic tag.
     *
     * @param $parser Parser mediawiki type
     * @param $page_id Integer page identifier
     */
    static function executeMagic($parser, $page_id) //do not change arguments

    {
        wfLoadExtensionMessages('Votapedia');
        $page_id = intval(trim($page_id));
        $output =  "<SurveyChoice>$page_id</SurveyChoice>[[".wfMsg('cat-survey-name',$page_id)."]]";
        return array($output, 'noparse' => false);
    }

    /**
     *
     * @global $wgUser User
     * @global $wgOut OutputPage
     * @global $wgTitle Title
     * @param $page_id Integer
     * @param $parser MwParser
     * @param $surveybuttons SurveyButtons
     */
    protected function __construct($page_id, $parser, SurveyButtons $surveybuttons)
    {
        wfLoadExtensionMessages('Votapedia');
        global $wgUser, $wgOut, $wgTitle;
        $this->parser = $parser;
        $this->page_id=$page_id;
        $this->buttons = $surveybuttons;
        $this->username = $wgUser->getName();

        if(! $this->page_id)
            throw new Exception( wfMsg('id-not-present', htmlspecialchars($page_id)) );

        $surveydao = new SurveyDAO();
        $this->page = $surveydao->findByPageID( $page_id );

        if($wgTitle)
            $this->wikititle = $wgTitle;
        else
            throw new Exception('SurveyView::__construct no page title, wgTitle is unavailable');

        //configure buttons
        $this->buttons->setPageAuthor($this->page->getAuthor());
        $this->buttons->setWikiTitle($this->wikititle->getDBKey());
        $this->buttons->setPageID($this->page_id);
        $this->buttons->setPageStatus($this->page->getStatus());
    }
    /**
     * Get body of survey
     *
     * @return html code
     */
    function getHTMLBody()
    {
        global $gvScript;
        $output = '';

        $output.= '<a name="survey_id_'.$this->page_id.'"></a>';

        $output.= '<h2>'.wfMsg('survey-question', htmlspecialchars($this->page->getTitle())).'</h2>';
        $output.='<table cellspacing="0" style="font-size:large">';

        $output.= '<tr><td valign="top" colspan="2"><img src="'.$gvScript.'/images/spacer.gif" />';

        $survey = $this->page->getSurveys();
        $choices = $survey[0]->getChoices();

        if($this->page->getStatus()=='ready')
        {
            $output.='<tr><td colspan="2">';
            $output.='<ul>';
            $i=0;
            foreach ($choices as $choice)
            {
                $i++;
                global $wgOut;
                $choice = $this->parser->run($choice->getChoice());
                if($choice)
                {
                    $output.="<li STYLE=\"list-style-image: url(".vfGetColorImage().
                            ")\"><label id=\"q$i\">$i. $choice</label></li>";
                }
            }
            $output.='</ul>';
        }
        elseif($this->page->getStatus() == 'active')
        {
            ;
        }
        elseif($this->page->getStatus() == 'ended')
        {
            ;
        }
        $output .= $this->buttons->getHTML();
        
        #$output.='<td valign="top"><div style="margin:0px 0px 0px 40px">
        #<img src="./utkgraph/displayGraph.php?pageTitle='.$encodedTitle.'&background='.$background.'"
        #alt="sample graph" /></div></td></tr>';

        $output .= '</table>';
        return $output;
    }
    static function getChoices($text)
    {
        global $wgParser;
        $p = new MwParser($wgParser);
        $lines = split("\n",$text);
        $output = '';
        foreach($lines as $line)
        {
            $line = trim($line);
            if($line)
            {
                $output .= $p->run('&bull; '.$line, true);
            }
        }
        return $output;
    }
    static function getChoice($line)
    {
        global $wgParser;
        $p = new MwParser($wgParser);
        return $p->run(trim($line), false);
    }
    /**
     * Current PageVO object
     *
     * @return PageVO
     */
    function &getPage()
    {
        return $this->page;
    }
}

?>