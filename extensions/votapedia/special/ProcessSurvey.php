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

/**
 * Special page Create Survey.
 *
 * @author Emir Habul
 * @package ControlSurvey
 */
class ProcessSurvey extends SpecialPage
{
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
        $wgOut->setArticleFlag(false);
        $page_id = intval(trim($wgRequest->getVal('id')));
        $action = $wgRequest->getVal( 'wpSubmit' );
        try
        {
            $pagedao = new PageDAO();
            $page = $pagedao->findByPageID($page_id);
            if(    $action == wfMsg('start-survey')
                || $action == wfMsg('start-questionnaire')
                || $action == wfMsg('start-quiz')
              )
            {
                if ( ! vfUser()->checkEditToken() )
                    die('Edit token is wrong, please try again.');
                if( ! vfUser()->canControlSurvey($page) )
                {
                    $wgOut->showErrorPage('notauthorized', 'notauthorized-desc', array($wgTitle->getPrefixedDBkey()) );
                    return;
                }
                if($page->getStatus() != 'ready')
                {
                    throw new SurveyException('Survey is either running or finished and cannot be started');
                }
                if($page->getPhoneVoting() != 'no')
                {
                    $tel = new Telephone();
                    try
                    {
                        //Setup receivers
                        $tel->setupReceivers($page);
                        $pagedao->updateReceiversSMS($page);
                        $pagedao->startPageSurvey($page);
                    }
                    catch(Exception $e)
                    {
                        // in case of an error
                        $tel->deleteReceivers($page);
                        throw $e; //continue throwing
                    }
                }
                //Redirect to the previous page
                $title = Title::newFromText($wgRequest->getVal('returnto'));
                $wgOut->redirect($title->escapeLocalURL(), 302);
                return;
            }
            elseif (   $action == wfMsg('edit-survey')
                    || $action == wfMsg('edit-questionnaire')
                    || $action == wfMsg('edit-quiz')
                )
            {
                $returnto = Title::newFromText($wgRequest->getVal('returnto'));
                if($page->getType() == vSIMPLE_SURVEY)
                    $title = Title::newFromText('Special:CreateSurvey');
                elseif($page->getType() == vQUESTIONNAIRE)
                    $title = Title::newFromText('Special:CreateQuestionnaire');
                elseif($page->getType() == vQUIZ)
                    $title = Title::newFromText('Special:CreateQuiz');
                else
                    throw new Exception('Unknown survey type.');
                $wgOut->redirect($title->escapeLocalURL()."?id=$page_id&returnto={$returnto->getFullText()}&vpAction=editstart", 302);
            }
            elseif ($action == wfMsg('view-details'))
            {
                $returnto = Title::newFromText($wgRequest->getVal('returnto'));
                $title = Title::newFromText('Special:ViewSurvey');
                $wgOut->redirect($title->escapeLocalURL()."?id=$page_id&returnto={$returnto->getFullText()}", 302);
            }
            elseif (   $action == wfMsg('vote-survey')
                    || $action == wfMsg('vote-questionnaire')
                    || $action == wfMsg('vote-quiz')
                )
            {
                if ( ! vfUser()->checkEditToken() )
                    die('Edit token is wrong, please try again.');
                $votedao = new VoteDAO($page, vfUser()->getName());

                if($page->getWebVoting() == 'no')
                        throw new Exception("Web voting is not allowed");
                if($page->getWebVoting() == 'yes' && vfUser()->isAnon())
                        throw new Exception("You must be logged in order to vote.");

                $s = $wgRequest->getArray('surveylist', array());
                foreach($s as $surveyid)
                {
                    $choiceid = intval($wgRequest->getVal('survey'.$surveyid));
                    if( $choiceid )
                    {
                        $votevo = $votedao->newFromPage('WEB', $page_id, $surveyid, $choiceid );
                        $votedao->vote($votevo);
                    }
                }
                $title = Title::newFromText($wgRequest->getVal('returnto'));
                $wgOut->redirect($title->escapeLocalURL(), 302);
                return;
            }
            elseif ($action ==  wfMsg('stop-survey')
                    || $action == wfMsg('stop-questionnaire')
                    || $action == wfMsg('stop-quiz')
                )
            {
                if ( ! vfUser()->checkEditToken() )
                    die('Edit token is wrong, please try again.');
                $pagedao->stopPageSurvey($page);
                //Redirect to the previous page
                $title = Title::newFromText($wgRequest->getVal('returnto'));
                $wgOut->redirect($title->escapeLocalURL(), 302);
                return;
            }
        }
        catch(Exception $e)
        {
            $wgOut->addHTML(vfErrorBox($e->getMessage()));
            $wgOut->addReturnTo(Title::newFromText($wgRequest->getVal('returnto')));
            return;
        }
    }
}

