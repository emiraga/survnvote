<?php
if (!defined('MEDIAWIKI')) die();

global $vgPath;
require_once("$vgPath/MwAdapter.php");
require_once("$vgPath/VO/PageVO.php");
require_once("$vgPath/DAO/SurveyDAO.php");

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
        global $wgTitle, $wgOut, $wgRequest;
        $wgOut->setArticleFlag(false);
        $page_id = intval(trim($wgRequest->getVal('id')));
        $action = $wgRequest->getVal( 'wpSubmit' );

        if ( ! vfUser()->checkEditToken() )
            die('Edit token is wrong, please try again.');

        try
        {
            $surveydao = new SurveyDAO();
            $page = $surveydao->findByPageID($page_id);
            if($action == wfMsg('start-survey'))
            {
                if( ! vfUser()->canControlSurvey($page) )
                {
                    $wgOut->showErrorPage('notauthorized', 'notauthorized-desc', array($wgTitle->getPrefixedDBkey()) );
                    return;
                }

                if($page->getStatus() != 'ready')
                    throw new SurveyException('Survey is either running or finished and cannot be started');

                if($page->getPhoneVoting() != 'no')
                {
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
                }
                //Redirect to the previous page
                $title = Title::newFromText($wgRequest->getVal('returnto'));
                $wgOut->redirect($title->escapeLocalURL(), 302);
                return;
            }
            elseif ($action == wfMsg('edit-survey'))
            {
                $returnto = Title::newFromText($wgRequest->getVal('returnto'));
                if($page->getType() == vSIMPLE_SURVEY)
                    $title = Title::newFromText('Special:CreateSurvey');
                elseif($page->getType() == vQUESTIONNAIRE)
                    $title = Title::newFromText('Special:CreateQuestionnaire');
                $wgOut->redirect($title->escapeLocalURL()."?id=$page_id&returnto={$returnto->getFullText()}&wpEditButton=".wfMsg('edit-survey'), 302);
            }
            elseif ($action == wfMsg('view-details'))
            {
                $returnto = Title::newFromText($wgRequest->getVal('returnto'));
                $title = Title::newFromText('Special:ViewSurvey');
                $wgOut->redirect($title->escapeLocalURL()."?id=$page_id&returnto={$returnto->getFullText()}", 302);
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
     * @deprecated survey tags are no longer cache-able
     */
    static function maintenance()
    {
        die('@deprecated');

        wfLoadExtensionMessages('Votapedia');
        $s = new SurveyDAO();
        //stop expired surveys/pages
        $finished = $s->processFinished();
        $extra = '';
        //invalidate cache of all finished pages
        foreach($finished as $page_id)
        {
            vfAdapter()->purgeCategoryMembers(wfMsg('cat-survey-name', $page_id));
            $extra .= "Finished $page_id\n";
        }
        return "OK\n".$extra;
    }
}
