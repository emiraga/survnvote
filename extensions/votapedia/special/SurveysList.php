<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SmsIntegration
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/DAO/PageDAO.php");
require_once("$vgPath/Survey/SurveyTimer.php");
require_once("$vgPath/DAO/CrowdDAO.php");

/**
 * Special page Latest incoming SMS and phone calls
 *
 * @author Emir Habul
 * @package SmsIntegration
 */
class SurveysList extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('SurveysList');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( true );
        $this->target = Skin::makeSpecialUrl('SurveysList');
        $this->setGroup('SurveysList', 'votapedia');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut, $wgRequest;
        $wgOut->setPageTitle( 'Surveys list' );

        if($par == 'frontpage')
        {
            $cache =& wfGetMainCache();
            $contents =& $cache->get('vp:surveyslist');
            if(! $contents) $cache->set('vp:surveyslist', $contents =& $this->getFrontpage(), 10);
            $wgOut->addHTML($contents);
        }
        else
        {
            $wgOut->addHTML($par);
        }
    }
    private function &getFrontpage()
    {
        global $vgScript, $vgEnableSMS;
        
        $out = '';
        
        $pagedao = new PageDAO();
        $surveys = $pagedao->getActiveSurveys();

        if(count($surveys)==0)
        {
            $out .= 'There are no active surveys at the moment.';
            return $out;
        }
        
        $p = new MwParser( new Parser() );
        $timer = new SurveyTimer();
        $timer->setRefresh(false);

        $out .= '<table style="width: 100%; margin: 0; background-color: rgb(245, 255, 250);" class="wikitable">';
        #$out .= '<caption>'.'Active surveys'.'</caption>';
        $out .= '<tr><th><th>Type<th>Title or question<th>Crowd<th>Started<th>Time left</tr>';

        foreach($surveys as $page)
        {
            /* @var $page PageVO */
            $wikipage = vfAdapter()->getSubCategories(wfMsg('cat-survey-name', $page->getPageID()));

            if(count($wikipage) == 0)
                $wikipage = Skin::makeSpecialUrl('ViewSurvey', 'id='.$page->getPageID());
            else
                $wikipage = Skin::makeUrl($wikipage[0]); // Show only first wiki page containing the survey

            $out .= '<tr>';
            $out .= '<td>';
            if($page->getPhoneVoting() != 'no')
            {
                $out .= "<img src=\"$vgScript/icons/phone.png\" title=\"Phone voting enabled\"/> ";
            }
            if($page->getWebVoting() != 'no')
            {
                $out .= "<img src=\"$vgScript/icons/laptop.png\" title=\"Web voting enabled\"/> ";
            }
            $out .= '<td>' . $page->getTypeName();
            $out .= '<td>';

            $out .= '<a href="'.$wikipage.'">'. $p->run($page->getTitle(), false) .'</a> ';

            $out .= '<td>';
            if($page->crowdID == 0)
            {
                $out .= 'Everyone';
            }
            else
            {
                $crdao = new CrowdDAO();
                $crowd = $crdao->findByID($page->crowdID);
                
                $showname = str_replace('_', ' ', $crowd->name);
                $out .= '<a href="'.Skin::makeSpecialUrlSubpage('Crowd', $crowd->name).'">'.$showname.'</a>';
            }
            $out .= '<td>'.vfPrettyDate($page->getStartTime());

            $timeleft = strtotime($page->getEndTime()) - time();
            $id='tl_'.$page->getPageID().'_'.rand();
            $out .= '<td>'.$timer->getJavascript($timeleft, $id);

            $out .= '</tr>';
        }

        $out .= '</table>';
        return $out;
    }
}

