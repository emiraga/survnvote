<?php
if (!defined('MEDIAWIKI')) die();

global $gvPath;
require_once("$gvPath/VO/PageVO.php");
require_once("$gvPath/DAO/SurveyDAO.php");

/**
 * Special page Create Survey
 *
 * @author Emir Habul
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
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param $par
     */
    function execute( $par = null )
    {
        global $wgUser, $wgTitle, $wgOut, $wgRequest;
        $wgOut->setArticleFlag(false);
        if ( $wgUser->isAnon() )
        {
            $wgOut->showErrorPage( 'surveynologin', 'surveynologin-desc', array($wgTitle->getPrefixedDBkey()) );
            return;
        }
        $page_id = intval(trim($wgRequest->getVal('id')));
        $action = $wgRequest->getVal( 'wpSubmit' );

        if ( !$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) ) )
        {
            die('Edit token is wrong, please try again.');
        }
        try
        {
            $surveydao = new SurveyDAO();
            $page = $surveydao->findByPageID($page_id);
            if( !$page->getAuthor() == $wgUser->getName() )
            {
                $wgOut->showErrorPage('notauthorized', 'notauthorized-desc', array($wgTitle->getPrefixedDBkey()) );
            }

            if($action == wfMsg('start-survey'))
            {
                if($page->getStatus() != 'ready')
                    throw new SurveyException('Survey is either running or finished and cannot be started');

                $tel = new Telephone();
                try
                {
                    //Setup receivers
                    $tel->setupReceivers($page);
                    $surveydao->updateReceiversSMS($page);
                    $surveydao->startSurvey($page);
                }
                catch(Exception $e)
                {
                    // in case of an error
                    $tel->deleteReceivers($page);
                    throw $e; //continue throwing
                }

                //Purge all pages that have this survey included.
                vfAdapter()->purgeCategoryMembers(wfMsg('cat-survey-name', $page_id));
                //Redirect to the previous page
                $title = Title::newFromText($wgRequest->getVal('returnto'));
                $wgOut->redirect($title->escapeLocalURL(), 302);
                return;
            }
        }
        catch(Exception $e)
        {
            $wgOut->addHTML(vfErrorBox($e->getMessage()));
            return;
        }
    }
    /**
     * Perform maintenace operations related to Surveys
     * 1. Mark surveys as finished
     * 2. Invalidate cache of pages which include these surveys
     * 
     */
    static function maintenance()
    {
        wfLoadExtensionMessages('Votapedia');
        $s = new SurveyDAO();
        //stop expired surveys/pages
        $finished = $s->processFinished();

        //invalidate cache of all finished pages
        foreach($finished as $page_id)
        {
            vfAdapter()->purgeCategoryMembers(wfMsg('cat-survey-name', $page_id));
        }
    }
}