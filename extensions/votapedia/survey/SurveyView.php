<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SurveyView
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/misc/Common.php");
require_once("$vgPath/survey/SurveyButtons.php");
require_once("$vgPath/survey/SurveyBody.php");
require_once("$vgPath/Graph/Graph.php");
require_once("$vgPath/misc/FormControl.php");
require_once("$vgPath/DAO/PageDAO.php");
require_once("$vgPath/misc/UserPermissions.php");

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
    /** @var UserVO */        protected $user;
    /** @var Title */         protected $wikititle;
    /** @var SurveyButtons */ protected $buttons;
    /** @var SurveyBody */    protected $body;

    /**
     * Function called for the SurveyChoice tag
     *
     * @param String $input text between tags
     * @param Array $args tag arguments
     * @param Parser $parser of Mediawiki
     * @param Unknown $frame
     */
    static function executeTag( $input, $args, $parser, $frame = NULL )
    {
        wfProfileIn( __METHOD__ );
        
        wfLoadExtensionMessages('Votapedia');
        $page_id = intval(trim($input));
        try
        {
            $mwparser = new MwParser($parser);
            $mwparser->setTag();

            if(! $page_id)
                throw new Exception( wfMsg('id-not-present', htmlspecialchars($page_id)) );

            $pagedao = new PageDAO();
            $page =& $pagedao->findByPageID( $page_id );
            $user =& vfUser()->getUserVO();

            $buttons = new RealSurveyButtons();
            $bodyfactory = new SurveyBodyFactory($page, $user, $mwparser);
            $tag = new SurveyView($user, $page, $mwparser, $buttons, $bodyfactory->getBody());

            wfProfileOut( __METHOD__ );
            return $tag->getHTML();
        }
        catch(Exception $e)
        {
            wfProfileOut( __METHOD__ );
            return vfErrorBox($e->getMessage());
        }
    }
    /**
     * Function called for the Survey magic tag.
     *
     * @param Parser $parser mediawiki type
     * @param Integer $page_id page identifier
     */
    static function executeMagic($parser, $page_id)
    {
        wfProfileIn( __METHOD__ );
        
        wfLoadExtensionMessages('Votapedia');
        $page_id = intval(trim($page_id));
        $output =  "<SurveyChoice>$page_id</SurveyChoice>[[".wfMsg('cat-survey-name',$page_id)."]]";

        wfProfileOut( __METHOD__ );
        return array($output, 'noparse' => false);
    }
    /**
     * Constructor for the SurveyView
     * 
     * @param UserVO $user
     * @param PageVO $page
     * @param MwParser $parser
     * @param SurveyButtons $surveybuttons
     * @param SurveyBody $body
     */
    function __construct(UserVO &$user, PageVO &$page, MwParser &$parser, SurveyButtons &$surveybuttons, SurveyBody &$body)
    {
        wfProfileIn( __METHOD__ );
        
        global $wgOut, $wgTitle, $wgRequest;

        $this->user =& $user;
        $this->parser =& $parser;
        $this->page =& $page;
        $this->buttons =& $surveybuttons;
        $this->body =& $body;

        $this->parser->disableCache(); // disable caching of pages with surveys

        if($wgRequest->getVal('returnto'))
            $this->wikititle = Title::newFromText($wgRequest->getVal('returnto'));
        else
            $this->wikititle = $wgTitle;

        if(! $this->wikititle)
            throw new Exception('SurveyView::__construct no page title, wgTitle is unavailable');

        if($this->wikititle->isSpecial('ViewSurvey'))
            $this->wikititle = Title::newMainPage();

        $pagestatus = $this->page->getStatus( $this->page->getCurrentPresentationID() );

        //configure buttons
        $this->buttons->setPageID($this->page->getPageID());
        $this->buttons->setType($this->page->getTypeName());
        
        if($pagestatus == 'ended' )
        {
            //page is no longer cached, we can control the survey even when it is ended.
            $this->buttons->setRenewButton(true);
        }
        
        //has control?.
        $this->userperm = new UserPermissions( $this->user );
        if( $this->userperm->canControlSurvey($this->page) )
        {
            $this->buttons->setHasControl(true);
            $this->body->setHasControl(true);

            //Show phone numbers
            if( $this->page->getPhoneVoting() != 'no')
            {
                $this->body->setShowNumbers(true);
            }
        }

        wfProfileOut( __METHOD__ );
    }
    /**
     * Get HTML of a one page of survey
     * 
     * @param Boolean $show_details
     * @return String html code
     */
    function getHTML($show_details = false)
    {
        wfProfileIn( __METHOD__ );
        
        $runs = $this->page->getCurrentPresentationID();
        if($runs == 1)
        {
            $this->body->setShowGraph(true);

            $output = $this->getHTMLOnePage(1);
            if($show_details)
                $output .= $this->getDetailsHTML(1);
        }
        else
        {
            $items = array();
            $form = new FormControl($items);
            $form->getScriptsIncluded(false);
            
            $output = '';
            $output .= $form->StartFormLite();

            $this->body->setShowGraph(true);

            //Show current run of the survey
            $contents = $this->getHTMLOnePage($runs);
            if($show_details)
            {
                $contents .= $this->getDetailsHTML($runs);
            }
            $output .= $form->pageContents('Current', $contents);

            $presentations =& $this->page->getPresentations();
            for($i=count($presentations);$i;$i--)
            {
                //Show previous runs
                $this->body->setShowGraph(true);
                
                $contents = $this->getHTMLOnePage($i);
                if($show_details)
                {
                    $contents .= $this->getDetailsHTML($i);
                }
                $output .= $form->pageContents( $presentations[$i-1]->getName(), $contents );
            }
            $output .= $form->EndFormLite();
        }
        wfProfileOut( __METHOD__ );
        return $output;
    }
    /**
     * Get HTML of a one page of survey
     *
     * @return String html code
     */
    function getHTMLOnePage($presID)
    {
        wfProfileIn( __METHOD__ );
        
        global $vgScript;
        $output = '';
        $pagestatus = $this->page->getStatus($presID);
        $this->body->setPresentationID($presID);

        //Should we enable web voting?
        $this->buttons->setPageStatus($pagestatus);
        if($pagestatus == 'active' && $this->userperm->canVote($this->page, 'web'))
        {
            $this->body->setShowVoting(true);
            $this->buttons->setVoteButton(true);
        }
        else
        {
            $this->body->setShowVoting(false);
            $this->buttons->setVoteButton(false);
        }
        
        $this->prosurv = Title::newFromText('Special:ProcessSurvey');
        
        if($this->page->getCurrentPresentationID() == $presID)
        {
            $output .='<form action="'.$this->prosurv->escapeLocalURL().'" method="POST">'
                    .'<input type="hidden" name="id" value="'.$this->page->getPageID().'">'
                    .'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle->getFullText()).'" />';
            $output.= '<a name="survey_id_'.$this->page->getPageID().'"></a>';
            if($this->user->isTemporary)
            {
                $output .= '<input type="hidden" name="liveshow" value="'.$this->user->getTemporaryKey($this->page->getPageID()).'" />';
                $output .= '<input type="hidden" name="userID" value="'.$this->user->userID.'" />';
            }
        }
        
        $output.= '<font size="4" class="vpTitle">'.$this->parser->run( wfMsg('survey-caption',  $this->page->getTitle() ) ).'</font>';
        
        $output .='<input type="hidden" name="wpEditToken" value="'. vfUser()->editToken() .'">';
        if($this->user->isTemporary)
        {
            $output .='<input type="hidden" name="userID" value="'. $this->user->userID .'">';
            $output .='<input type="hidden" name="presID" value="'. $presID .'">';
            $output .='<input type="hidden" name="tempKey" value="'. $this->user->getTemporaryKey($this->page->getPageID().'_'.$presID) .'">';
        }
        
        $output .= $this->body->getHTML();

        if($this->page->getCurrentPresentationID() == $presID)
        {
            $output .= $this->buttons->getHTML($presID);
            $output .= '</form>';
            if($pagestatus == 'ended')
            {
                $output .= '<p><b>Run started</b>: '.vfPrettyDate( $this->page->getStartTime() , 'l').'</p>';
                $output .= '<p><b>Run ended</b>: '.vfPrettyDate( $this->page->getEndTime() , 'l').'</p>';
            }
        }
        else
        {
            $p =& $this->page->getPresentationByNum($presID);
            $output .= '<p><b>Run started</b>: '. vfPrettyDate( $p->getStartTime() , 'l' ).'</p>';
            $output .= '<p><b>Run ended</b>: '. vfPrettyDate( $p->getEndTime() , 'l').'</p>';
        }

        wfProfileOut( __METHOD__ );
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
        wfProfileIn( __METHOD__ );
        $out = $this->body->getDetailsHTML($presID);
        wfProfileOut( __METHOD__ );
        return $out;
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
}

