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
            $mwparser = new MwParser($parser);
            $mwparser->setTag();
            $buttons = new SurveyButtons();
            $tag = new SurveyView($page_id, $mwparser, $buttons);
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
     * @param $page_id Integer
     * @param $parser MwParser
     * @param $surveybuttons SurveyButtons
     */
    function __construct($page_id, MwParser &$parser, SurveyButtons &$surveybuttons)
    {
        wfLoadExtensionMessages('Votapedia');
        global $wgOut, $wgTitle, $wgRequest;

        $this->parser =& $parser;
        $this->page_id=$page_id;
        $this->buttons =& $surveybuttons;
        $this->username = vfUser()->getName();

        if(! $this->page_id)
            throw new Exception( wfMsg('id-not-present', htmlspecialchars($page_id)) );

        $surveydao = new SurveyDAO();
        $this->page = $surveydao->findByPageID( $page_id );

        if($wgRequest->getVal('returnto'))
            $this->wikititle = Title::newFromText($wgRequest->getVal('returnto'));
        else
            $this->wikititle = $wgTitle;

        if(! $this->wikititle)
            throw new Exception('SurveyView::__construct no page title, wgTitle is unavailable');

        if($this->wikititle->isSpecial('ViewSurvey'))
            $this->wikititle = Title::newMainPage();

        if($this->page->getStatus() != 'ended' )
            $this->parser->disableCache(); // for active and ready type of surveys

        //configure buttons
        $this->buttons->setPageAuthor($this->page->getAuthor());
        $this->buttons->setWikiTitle($this->wikititle->getFullText());
        $this->buttons->setPageID($this->page_id);
        $this->buttons->setPageStatus($this->page->getStatus());

        //Configure body and buttons for different types
        switch($this->page->getType())
        {
            case vSIMPLE_SURVEY:
                $this->body = new SurveyBody($this->page, $this->parser);
                break;
            case vQUESTIONNAIRE:
                $this->body = new QuestionnaireBody($this->page, $this->parser);
                break;
            default:
                throw new Exception('Unknown survey type');
        }
        //Should we enable web voting?
        if($this->page->getStatus() == 'active' 
                && $this->page->getWebVoting() != 'no'
                && ! vfUser()->isAuthor( $this->page ))
        {
            //either user is not anonymous or it is allowed to vote anonymously
            if( !vfUser()->isAnon() || $this->page->getWebVoting() == 'anon' )
            {
                $this->buttons->setVoteButton(true);
                $this->body->setShowVoting(true);
            }
        }
        //control?.
        if( vfUser()->canControlSurvey($this->page) )
            $this->buttons->setHasControl(true);
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
        $output.= '<h2>'.$this->parser->run( wfMsg('survey-caption',  $this->page->getTitle() ) ).'</h2>';
        //$output.= '<tr><td valign="top" colspan="2"><img src="'.$vgScript.'/images/spacer.gif" />';
        $this->prosurv = Title::newFromText('Special:ProcessSurvey');
        $output .='<form action="'.$this->prosurv->escapeLocalURL().'" method="POST">'
                .'<input type="hidden" name="id" value="'.$this->page_id.'">'
                .'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle->getFullText()).'" />';
        if($this->page->getStatus() != 'ended')
            $output .='<input type="hidden" name="wpEditToken" value="'. vfUser()->editToken() .'">';
        $output .= $this->body->getHTML();
        $output .= '<br />';
        $output .= $this->buttons->getHTML();

        $output .= '</form>';

        if($this->page->getStatus() == 'ended')
        {
            $output .= "This survey has ended.";
        }
        return $output;
    }
    /**
     * AJAX call for choices preview
     *
     * @param $text String multiline choices wiki code
     * @return String HTML code for choices
     */
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
    /**
     * AJAX call to get preview of choioe
     *
     * @param  $line String wiki code for choice
     * @return String HTML code of preview of choice
     */
    static function getChoice($line)
    {
        $pars = new Parser();
        $p = new MwParser($pars);
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