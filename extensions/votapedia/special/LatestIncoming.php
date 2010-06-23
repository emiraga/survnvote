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
        global $wgUser, $vgEnableSMS, $wgOut, $wgRequest;
        #$admin = vfUser()->isAdmin();
        
        $wgOut->setPageTitle( wfMsg('latestincoming') );
        $wgOut->addHTML('<table style="width: 100%;" class="wikitable">');
        $wgOut->addHTML('<caption>'.wfMsg('latestincoming').'</caption>');
        $wgOut->addHTML('<tr><th>Type<th>From<th></tr>');
        
        if($vgEnableSMS)
        {
            $in = Sms::getIncoming(5);
            foreach($in as $sms)
            {
                $wgOut->addHTML('<tr><td>SMS<td>'.
                        vfColorizePhone($sms['number'],false,true)
                        .'<td>'
                        .vfPrettyDate($sms['date'])
                        .'</tr>');
            }
        }
        $wgOut->addHTML('</table>');
    }
}

