<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SmsIntegration
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/Sms.php");

/**
 * Special page Latest incoming SMS and phone calls
 *
 * @author Emir Habul
 * @package SmsIntegration
 */
class LatestIncoming extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('LatestIncoming');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( true );
        $this->target = Skin::makeSpecialUrl('LatestIncoming');
        $this->setGroup('LatestIncoming', 'votapedia');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut, $wgRequest;
        $wgOut->setPageTitle( wfMsg('latestincoming') );
        if($par == 'long')
        {
            $wgOut->addHTML($this->getContents(1));
        }
        else
        {
            $cache =& wfGetMainCache();
            $contents =& $cache->get('vp:latestincoming');
            if(! $contents) $cache->set('vp:latestincoming', $contents =& $this->getContents(0), 10);
            $wgOut->addHTML($contents);
        }
    }
    private function &getContents($long)
    {
        global $vgEnableSMS;
        if($long)
        {
            $isadmin = vfUser()->isAdmin();
        }
        $out = '';
        $out .= '<table style="width: 100%; margin: 0;" class="wikitable">';
        # $wgOut->addHTML('<caption>'.wfMsg('latestincoming').'</caption>');
        $out .= '<tr><th>Type<th>From<th></tr>';

        if($vgEnableSMS)
        {
            $in = Sms::getIncoming($long?30:5);
            foreach($in as $sms)
            {
                $out .= '<tr><td>SMS<td>'.
                        vfColorizePhone($sms['number'], false, !$long || !$isadmin)
                        .'<td>'
                        .vfPrettyDate($sms['date']);
                if($long && $isadmin)
                {
                    $out .= '<td width=500px>'.htmlspecialchars( $sms['text'] );
                }
                $out .= '</tr>';
            }
        }
        $out .= '</table>';
        return $out;
    }
}

