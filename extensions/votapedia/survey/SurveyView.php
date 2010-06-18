<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SurveyView
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/survey/SurveyButtons.php");
require_once("$vgPath/survey/SurveyBody.php");
require_once("$vgPath/Graph/Graph.php");
require_once("$vgPath/FormControl.php");
require_once("$vgPath/DAO/PageDAO.php");

/**
 * Class used to display parts of HTML related to the viewing of survey.
 *
 * @author Emir Habul
 * @package SurveyView
 */
class SurveyView
{
    /** @var MwParser */      protected $parser;
    /** @var PageVO */        protected $page;
    /** @var Integer */       protected $page_id;
    /** @var Title */         protected $wikititle;
    /** @var SurveyButtons */ protected $buttons;
    /** @var SurveyBody */    protected $body;

    /**
     * Function called for the &lt;SurveyChoice&gt; tag
     *
     * @param String $input text between tags
     * @param Array $args tag arguments
     * @param Parser $parser of Mediawiki
     * @param Unknown $frame
     */
    static function executeTag( $input, $args, $parser, $frame = NULL )
    {
        $page_id = intval(trim($input));
        try
        {
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
     * @param Parser $parser mediawiki type
     * @param Integer $page_id page identifier
     */
    static function executeMagic($parser, $page_id) //do not change arguments

    {
        wfLoadExtensionMessages('Votapedia');
        $page_id = intval(trim($page_id));
        $output =  "<SurveyChoice>$page_id</SurveyChoice>[[".wfMsg('cat-survey-name',$page_id)."]]";
        return array($output, 'noparse' => false);
    }
    /**
     * @param Integer $page_id
     * @param MwParser $parser
     * @param SurveyButtons $surveybuttons
     */
    function __construct($page_id, MwParser &$parser, SurveyButtons &$surveybuttons)
    {
        wfLoadExtensionMessages('Votapedia');
        global $wgOut, $wgTitle, $wgRequest;

        $this->parser =& $parser;
        $this->page_id=$page_id;
        $this->buttons =& $surveybuttons;

        if(! $this->page_id)
            throw new Exception( wfMsg('id-not-present', htmlspecialchars($page_id)) );

        $pagedao = new PageDAO();
        $this->page = $pagedao->findByPageID( $page_id );

        if($wgRequest->getVal('returnto'))
            $this->wikititle = Title::newFromText($wgRequest->getVal('returnto'));
        else
            $this->wikititle = $wgTitle;

        if(! $this->wikititle)
            throw new Exception('SurveyView::__construct no page title, wgTitle is unavailable');

        if($this->wikititle->isSpecial('ViewSurvey'))
            $this->wikititle = Title::newMainPage();

        $pagestatus = $this->page->getStatus( $this->page->getCurrentPresentationID() );
        if($pagestatus != 'ended' )
        {
            $this->parser->disableCache(); // for active and ready type of surveys
        }

        //configure buttons
        $this->buttons->setPageAuthor($this->page->getAuthor());
        $this->buttons->setWikiTitle($this->wikititle->getFullText());
        $this->buttons->setPageID($this->page_id);
        $this->buttons->setType($this->page->getTypeName());
        if($pagestatus == 'ended' )
        {
            $this->buttons->setRenewButton(true);
            $this->buttons->setHasControl(true);
        }

        //Configure body and buttons for different types
        switch($this->page->getType())
        {
            case vSIMPLE_SURVEY:
                $this->body = new SurveyBody($this->page, $this->parser, $this->page->getCurrentPresentationID());
                break;
            case vQUESTIONNAIRE:
                $this->body = new QuestionnaireBody($this->page, $this->parser, $this->page->getCurrentPresentationID());
                break;
            case vQUIZ:
                $this->body = new QuizBody($this->page, $this->parser, $this->page->getCurrentPresentationID());
                break;
            default:
                throw new Exception('Unknown survey type');
        }
        
        $this->body->setShowGraph(true);
        
        //control?.
        if( vfUser()->canControlSurvey($this->page) )
        {
            $this->buttons->setHasControl(true);
            $this->body->setHasControl(true);

            //Show phone numbers
            if( $this->page->getPhoneVoting() != 'no')
            {
                $this->body->setShowNumbers(true);
            }
        }
    }
    /**
     * Get HTML of a one page of survey
     *
     * @param Boolean $show_details
     * @return String html code
     */
    function getHTML($show_details = false)
    {
        $runs = $this->page->getCurrentPresentationID();
        if($runs == 1)
        {
            $output = $this->getHTMLOnePage(1);
            if($show_details)
                $output .= $this->getDetailsHTML(1);
        }
        else
        {
            $items = array();
            $form = new FormControl($items);
            $output = $form->getScriptsIncluded(false);
            $output .= $form->StartFormLite();
            
            $contents = $this->getHTMLOnePage($runs);
            if($show_details)
                $contents .= $this->getDetailsHTML($runs);
            $output .= $form->pageContents('Current', $contents);

            $presentations =& $this->page->getPresentations();
            for($i=count($presentations);$i;$i--)
            {
                $contents = $this->getHTMLOnePage($i);
                if($show_details)
                    $contents .= $this->getDetailsHTML($i);
                $output .= $form->pageContents( $presentations[$i-1]->getName(), $contents );
            }
            $output .= $form->EndFormLite();
        }
        return $output;
    }
    /**
     * Get HTML of a one page of survey
     *
     * @return String html code
     */
    function getHTMLOnePage($presID)
    {
        global $vgScript;
        $output = '';
        $pagestatus = $this->page->getStatus($presID);
        $this->body->setPresentationID($presID);

        //Should we enable web voting?
        $this->buttons->setPageStatus($pagestatus);
        if($pagestatus == 'active'
                && $this->page->getWebVoting() != 'no'
                && ! vfUser()->isAuthor( $this->page ))
        {
            //either user is not anonymous or it is allowed to vote anonymously
            if( !vfUser()->isAnon() || $this->page->getWebVoting() == 'anon' )
            {
                $this->body->setShowVoting(true);
                $this->buttons->setVoteButton(true);
            }
        }

        $output.= '<a name="survey_id_'.$this->page_id.'"></a>';
        $this->prosurv = Title::newFromText('Special:ProcessSurvey');
        $output .='<form action="'.$this->prosurv->escapeLocalURL().'" method="POST">'
                .'<input type="hidden" name="id" value="'.$this->page_id.'">'
                .'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle->getFullText()).'" />';
        $output.= '<font size=4>'.$this->parser->run( wfMsg('survey-caption',  $this->page->getTitle() ) ).'</font>';
        
        if($pagestatus != 'ended')
        {
            $output .='<input type="hidden" name="wpEditToken" value="'. vfUser()->editToken() .'">';
        }

        $output .= $this->body->getHTML();
        $output .= '<br />';
        if($this->page->getCurrentPresentationID() == $presID)
        {
            $output .= $this->buttons->getHTML($presID);
        }
        $output .= '</form>';
        if($pagestatus == 'ended')
        {
            $output .= "This survey has ended.";
        }
        return $output;
    }
    /**
     * Get more details about Survey.
     *
     * @param Boolean $presID
     * @return String
     */
    private function getDetailsHTML($presID)
    {
        return $this->body->getDetailsHTML($presID);
    }
    /**
     * AJAX call for choices preview
     *
     * @param String $text multiline choices wiki code
     * @return String HTML code for choices
     */
    static function getChoices($text)
    {
        global $wgParser;
        $p = new MwParser($wgParser);
        $lines = preg_split("/\n/",$text);
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
     * @param String $line wiki code for choice
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

