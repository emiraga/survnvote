<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SmsIntegration
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/misc/Common.php");

if(class_exists('SpecialPage'))
{
    /**
     * Special page statistics of votapedia
     *
     * @author Emir Habul
     * @package SmsIntegration
     */
    class VotapediaStats extends SpecialPage
    {
        /**
         * Constructor for VotapediaStats
         */
        function __construct()
        {
            parent::__construct('VotapediaStats');
            wfLoadExtensionMessages('Votapedia');
            $this->includable( true );
            $this->setGroup('VotapediaStats', 'votapedia');
        }
        /**
         * Mandatory execute function for a Special Page
         *
         * @param String $par
         */
        function execute( $par = null )
        {
            global $wgOut, $wgRequest;
            $wgOut->setPageTitle( 'Votapedia statistics' );

            $cache =& wfGetMainCache();
            $contents =& $cache->get('vp:votapediastats');
            if(! $contents) $cache->set('vp:votapediastats', $contents =& $this->getFrontpage(), 60);
            $wgOut->addHTML($contents);
        }
        /**
         * Purge cache related to this special page.
         */
        static function purgeCache()
        {
            $cache =& wfGetMainCache();
            $cache->delete('vp:votapediastats');
        }
        /**
         * Get HTML code which is to be shown on the front page.
         *
         * @return String HTML code
         */
        private function &getFrontpage()
        {
            global $vgScript;

            $out = '<table><td>';
            $out .= 'Articles: '.SiteStats::articles().'<br>';
            $out .= 'Pages: '.SiteStats::pages().'<br>';
            $out .= 'Page edits: '.SiteStats::edits().'<br>';
            $out .= 'Registered users: '.SiteStats::users().'<br>';
            $out .= 'Active users: '.SiteStats::activeUsers().'<br>';
            $out .= 'Admins: '.SiteStats::numberingroup('sysop').'<br>';

            global $vgDB, $vgDBPrefix;
            $s = $vgDB->Execute("SELECT * FROM {$vgDBPrefix}stats");
            $out .= '<td>&nbsp;<td>';
            $out .= 'Surveys: '.$s->fields['pages'].'<br>';
            $out .= 'Survey runs: '.$s->fields['surveyruns'].'<br>';
            $out .= 'Crowds: '.$s->fields['crowds'].'<br>';
            $out .= 'Votes by SMS: '.$s->fields['votes_sms'].'<br>';
            $out .= 'Votes by phone: '.$s->fields['votes_call'].'<br>';
            $out .= 'Votes by web: '.$s->fields['votes_web'].'<br>';
            $out .= '</table>';
            return $out;
        }
    }
}

class VotapediaStatsUpdate
{
    static function addPage()
    {
        VotapediaStatsUpdate::updateCount('pages');
    }
    static function addCrowd()
    {
        VotapediaStatsUpdate::updateCount('crowds');
    }
    static function addWebVote()
    {
        VotapediaStatsUpdate::updateCount('votes_web');
    }
    static function addSmsVote()
    {
        VotapediaStatsUpdate::updateCount('votes_sms');
    }
    static function addCallVote()
    {
        VotapediaStatsUpdate::updateCount('votes_call');
    }
    static function addSurveyRun()
    {
        VotapediaStatsUpdate::updateCount('surveyruns');
    }
    static protected function updateCount($name)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->Execute("UPDATE {$vgDBPrefix}stats SET $name = $name + 1");
    }
}

