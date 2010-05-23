<?php
if (!defined('MEDIAWIKI')) die();

global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/DAO/SurveyDAO.php");
require_once("$vgPath/survey/SurveyButtons.php");
require_once("$vgPath/survey/SurveyBody.php");

/**
 * Class used to display parts of HTML related to the viewing of survey
 *
 * @author Emir Habul
 */
class SurveyView
{
    /** @var MwParser */      protected $parser;
    /** @var PageVO */        protected $page;
    /** @var String */        protected $username;
    /** @var Integer */       protected $page_id;
    /** @var Title */         protected $wikititle;
    /** @var SurveyButtons */ protected $buttons;
    /** @var SurveyBody */    protected $body;

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
        vfGetColorImage(true);
        $page_id = intval(trim($input));
        try
        {
            #var_dump($parser);
            $mwparser =& new MwParser($parser);
            $mwparser->setTag();
            $buttons =& new SurveyButtons();
            $tag =& new SurveyView($page_id, $mwparser, $buttons);
            return $tag->getHTML();
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
    protected function __construct($page_id, MwParser &$parser, SurveyButtons &$surveybuttons)
    {
        wfLoadExtensionMessages('Votapedia');
        global $wgUser, $wgOut, $wgTitle;

        $this->parser =& $parser;
        $this->page_id=$page_id;
        $this->buttons =& $surveybuttons;
        $this->username = $wgUser->getName();

        if(! $this->page_id)
            throw new Exception( wfMsg('id-not-present', htmlspecialchars($page_id)) );

        $surveydao = new SurveyDAO();
        $this->page = $surveydao->findByPageID( $page_id );

        if($wgTitle)
            $this->wikititle = $wgTitle;
        else
            throw new Exception('SurveyView::__construct no page title, wgTitle is unavailable');

        if($this->page->getStatus() != 'ended' )
            $this->parser->disableCache(); // for active and ready type of surveys

        //configure buttons
        $this->buttons->setPageAuthor($this->page->getAuthor());
        $this->buttons->setWikiTitle($this->wikititle->getDBKey());
        $this->buttons->setPageID($this->page_id);
        $this->buttons->setPageStatus($this->page->getStatus());

        //Configure body and buttons for different types
        switch($this->page->getType())
        {
            case vSIMPLE_SURVEY:
                $this->body =& new SurveyBody($this->page, $this->parser);
                break;
            case vQUESTIONNAIRE:
                $this->body =& new QuestionnaireBody($this->page, $this->parser);
                $this->buttons->setCreateTitle( 'Special:CreateQuestionnaire' );
                break;
            default:
                throw new Exception('Unknown survey type');
        }
    }
    /**
     * Get HTML of survey
     *
     * @return String html code
     */
    function getHTML()
    {
        global $vgScript;
        $output = '';

        $output.= '<a name="survey_id_'.$this->page_id.'"></a>';

        $output.= '<h2>'.wfMsg('survey-caption', htmlspecialchars($this->page->getTitle())).'</h2>';
        $output.='<table cellspacing="0" style="font-size:large">';

        $output.= '<tr><td valign="top" colspan="2"><img src="'.$vgScript.'/images/spacer.gif" />';

        $output .= $this->body->getHTML();
        $output .= $this->buttons->getHTML();

        #$output.='<td valign="top"><div style="margin:0px 0px 0px 40px">
        #<img src="./utkgraph/displayGraph.php?pageTitle='.$encodedTitle.'&background='.$background.'"
        #alt="sample graph" /></div></td></tr>';

        $output .= '</table>';

        if($this->page->getStatus() == 'ended')
        {
            $output .= "This survey has ended.";
        }
        $output .= " Survey status:".$this->page->getStatus();
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