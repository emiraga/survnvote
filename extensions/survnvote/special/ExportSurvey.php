<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ControlSurvey
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/misc/Common.php" );
require_once("$vgPath/DAO/UserDAO.php" );
require_once("$vgPath/DAO/PageDAO.php" );
require_once("$vgPath/DAO/VoteDAO.php" );
require_once("$vgPath/misc/DataWriter.php" );

/**
 * Special page View Survey
 *
 * @author Emir Habul
 * @package ControlSurvey
 */
class ExportSurvey extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('ExportSurvey');
        wfLoadExtensionMessages('Survnvote');
        $this->includable( false );
        $this->setGroup('ExportSurvey', 'survnvote');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        wfProfileIn( __METHOD__ );

        global $wgOut, $wgParser, $wgRequest, $vgScript;
        $wgOut->setArticleFlag(false);
        try
        {
            $page_id = intval($wgRequest->getVal('id',0));
            $presID = intval($wgRequest->getVal('presid',0));
            $surveyid = intval($wgRequest->getVal('surveyid',0));

            $parser = new MwParser($wgParser, $wgOut->ParserOptions());
            $user = vfUser()->getUserVO();

            $pagedao = new PageDAO();
            $page =& $pagedao->findByPageID( $page_id );

            if($page->getStatus($presID) != 'ended')
            {
                throw new Exception('Results are available only for finished surveys');
            }
            if($par == 'xls')
            {
                $extra = '';
                if($surveyid)
                {
                    $extra = 'q_'.$surveyid;
                }
                if($wgRequest->getCheck('quiz'))
                {
                    $extra = '_quiz';
                }

                $writer = new ExcelWrite('Survnvote_'.$page->getTypeName().'_'.$page_id.'_pres_'.$presID.$extra.'.xls');

                if($wgRequest->getCheck('quiz'))
                {
                    $data = new QuizResultsData($page, $presID);
                    $writer->addSource($data);
                }
                else
                {
                    $surveys =& $page->getSurveys();
                    $colorindex = 1;
                    foreach ($surveys as $survey)
                    {
                        /* @var $survey SurveyVO */
                        if($surveyid && $surveyid != $survey->getSurveyID())
                            continue;
                        $votescount = VoteDAO::getNumVotes($page, $presID);
                        if(!$votescount)
                            throw new Exception('Invalid presentation ID');
                        $data = new SurveyVotesData($survey, $votescount, $parser, $colorindex);
                        $writer->addSource($data);
                    }
                }
                $writer->write();
                $wgOut->disable();
            }
        }
        catch(Exception $e)
        {
            $wgOut->addHTML(vfErrorBox('Error: '.$e->getMessage()));
            $wgOut->returnToMain();
        }
        wfProfileOut( __METHOD__ );
    }
}

