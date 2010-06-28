<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ControlSurvey
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/MwAdapter.php");
require_once("$vgPath/DAO/VoteDAO.php");
require_once("$vgPath/DAO/PageDAO.php");
require_once("$vgPath/UserPermissions.php");

/**
 * Special page Create Survey.
 *
 * @author Emir Habul
 * @package ControlSurvey
 */
class ProcessSurvey extends SpecialPage
{
    /** @var UserVO */ protected $user;
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('ProcessSurvey');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( false );
        $this->setGroup('ProcessSurvey', 'votapedia');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        /* @var $wgRequest WebRequest */
        global $wgTitle, $wgOut, $wgRequest;
        
        if ( wfReadOnly() ) {
            $wgOut->readOnlyPage();
            return;
        }
        $wgOut->setArticleFlag(false);
        $this->page_id = intval(trim($wgRequest->getVal('id')));
        $userdao = new UserDAO();
        
        if($wgRequest->getCheck('liveshow'))
        {
            global $vgScript;
            $liveshow = $wgRequest->getVal('liveshow');
            $wgOut->addStyle($vgScript.'/liveshow.css');
            $wgRequest->setVal('printable', true);
            $wgOut->setPageTitle( '' );
            $userID = $wgRequest->getInt('userID');
            if($userID == 0)
                throw new Exception('UserID not specified.');

            $this->user =& $userdao->findByID($userID);
            if(! $this->user)
                throw new Exception('User does not exist.');

            if($liveshow != $this->user->getTemporaryKey($this->page_id))
                throw new Exception('Wrong key.');
            $this->user->isTemporary = true;
        }
        else
        {
            $this->user = vfUser()->getUserVO();
        }
        
        $action = $wgRequest->getVal( 'wpSubmit' );
        $userperm = new UserPermissions( $this->user );
        
        try
        {
            $pagedao = new PageDAO();
            $page = $pagedao->findByPageID($this->page_id);
            if(    $action == wfMsg('start-survey')
                || $action == wfMsg('start-questionnaire')
                || $action == wfMsg('start-quiz')
              )
            {
                if ( ! vfUser()->checkEditToken() )
                    die('Edit token is wrong, please try again.');
                if( ! $userperm->canControlSurvey($page) )
                {
                    $wgOut->showErrorPage('notauthorized', 'notauthorized-desc', array($wgTitle->getPrefixedDBkey()) );
                    return;
                }
                if($page->getStatus( $page->getCurrentPresentationID() ) != 'ready')
                {
                    throw new SurveyException('Survey is either running or finished and cannot be started');
                }
                $tel = new Telephone();
                try
                {
                    if($page->getPhoneVoting() != 'no')
                    {
                        //Setup receivers
                        $tel->setupReceivers($page);
                        $pagedao->updateReceiversSMS($page);
                    }
                    $pagedao->startPageSurvey($page);
                    SurveysList::purgeCache();
                }
                catch(Exception $e)
                {
                    // in case of an error
                    $tel->deleteReceivers($page);
                    throw $e; //continue throwing
                }
                //Redirect to the previous page
                $this->redirect();
                return;
            }
            if(    $action == wfMsg('renew-survey')
                || $action == wfMsg('renew-questionnaire')
                || $action == wfMsg('renew-quiz')
              )
            {
                if ( ! vfUser()->checkEditToken() )
                    die('Edit token is wrong, please try again.');

                if( ! $userperm->canControlSurvey($page) )
                {
                    $wgOut->showErrorPage('notauthorized', 'notauthorized-desc', array($wgTitle->getPrefixedDBkey()) );
                    return;
                }
                
                if($page->getStatus( $page->getCurrentPresentationID() ) != 'ended')
                {
                    throw new SurveyException('Survey is not finished and cannot be renewed.');
                }
                
                if(true)
                {
                    $tel = new Telephone();
                    $tel->releaseReceivers(); //in case this survey has unreleased receivers
                    
                    $pagedao->renewPageSurvey($page);
                    SurveysList::purgeCache();
                    
                    //Purge all pages that have this survey included.
                    vfAdapter()->purgeCategoryMembers(wfMsg('cat-survey-name', $this->page_id));
                    //Redirect to the previous page
                    $this->redirect();
                }
                return;
            }
            elseif (   $action == wfMsg('edit-survey')
                    || $action == wfMsg('edit-questionnaire')
                    || $action == wfMsg('edit-quiz')
                )
            {
                //EditToken checking is not needed here, since we don't modify the datbase.
                
                $returnto = Title::newFromText($wgRequest->getVal('returnto'));
                if($page->getType() == vSIMPLE_SURVEY)
                    $title = Title::newFromText('Special:CreateSurvey');
                elseif($page->getType() == vQUESTIONNAIRE)
                    $title = Title::newFromText('Special:CreateQuestionnaire');
                elseif($page->getType() == vQUIZ)
                    $title = Title::newFromText('Special:CreateQuiz');
                else
                    throw new Exception('Unknown survey type.');
                $wgOut->redirect($title->escapeLocalURL()."?id={$this->page_id}&returnto={$returnto->getFullText()}&vpAction=editstart", 302);
            }
            elseif ($action == wfMsg('view-details'))
            {
                $returnto = Title::newFromText($wgRequest->getVal('returnto'));
                $title = Title::newFromText('Special:ViewSurvey');
                $wgOut->redirect($title->escapeLocalURL()."?id={$this->page_id}&returnto={$returnto->getFullText()}", 302);
            }
            elseif ($action == wfMsg('view-liveshow'))
            {
                $returnto = Title::newFromText($wgRequest->getVal('returnto'));
                $title = Title::newFromText('Special:ViewSurvey');
                $wgOut->redirect($title->escapeLocalURL()."?id={$this->page_id}&getliveshow=1&returnto={$returnto->getFullText()}", 302);
            }
            elseif (   $action == wfMsg('vote-survey')
                    || $action == wfMsg('vote-questionnaire')
                    || $action == wfMsg('vote-quiz')
                )
            {
                if ( ! vfUser()->checkEditToken() )
                    die('Edit token is wrong, please try again.');
                
                $votedao = new VoteDAO($page, vfUser()->userID());

                if( ! $userperm->canVote($page, 'web') )
                {
                    global $wgTitle;
                    $wgOut->showErrorPage('notauthorized', 'notauthorized-desc', array($wgTitle->getPrefixedDBkey()) );
                    return;
                }
                
                $s = $wgRequest->getArray('surveylist', array());
                foreach($s as $surveyid)
                {
                    $choiceid = intval($wgRequest->getVal('survey'.$surveyid));
                    if( $choiceid )
                    {
                        $votevo = $votedao->newFromPage('WEB', $this->page_id, $surveyid,
                                $choiceid, $page->getCurrentPresentationID() );
                        $votedao->vote($votevo);
                    }
                }
                $this->redirect();
                return;
            }
            elseif ($action ==  wfMsg('stop-survey')
                    || $action == wfMsg('stop-questionnaire')
                    || $action == wfMsg('stop-quiz')
                )
            {
                if ( ! vfUser()->checkEditToken() )
                    die('Edit token is wrong, please try again.');
                if( ! $userperm->canControlSurvey($page) )
                {
                    $wgOut->showErrorPage('notauthorized', 'notauthorized-desc', array($wgTitle->getPrefixedDBkey()) );
                    return;
                }
                $pagedao->stopPageSurvey($page);
                SurveysList::purgeCache();
                
                //Redirect to the previous page
                $this->redirect();
                return;
            }
        }
        catch(Exception $e)
        {
            $wgOut->addHTML(vfErrorBox($e->getMessage()));
            $wgOut->addHTML( '<a href="'.$this->getRedirectLink().'">Return to survey page<a>' );
            return;
        }
    }
    /**
     * Get a link to be redirected to. It handles two cases of regular usage and liveshow.
     * @return String
     */
    function getRedirectLink()
    {
        global $wgRequest;
        if($this->user->isTemporary)
        {
            // User is viewing this from liveshow, means we have to redirect back to ViewSurvey page,
            // and not the wiki page.
            // In this case 'returnto' value does not mean anything, we know where to return.
            $t = Title::newFromText('Special:ViewSurvey');

            $url = $t->getLocalURL('liveshow='.$this->user->getTemporaryKey($this->page_id)
                    .'&id='.$this->page_id
                    .'&userID='.$this->user->userID).'#survey_id_'.$this->page_id;
            return $url;
        }
        else
        {
            $title = Title::newFromText($wgRequest->getVal('returnto'));
            return $title->getLocalURL();
        }
    }
    /**
     * Redirect user to the URL given by $this->getRedirectLink()
     */
    function redirect()
    {
        global $wgOut;
        $wgOut->redirect( $this->getRedirectLink(), 302);
    }
}

