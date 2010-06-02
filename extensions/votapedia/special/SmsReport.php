<?php
if (!defined('MEDIAWIKI')) die();

global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/Sms.php");

/**
 * Special page Create Survey
 *
 * @author Emir Habul
 */
class SmsReport extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('SmsReport');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( false );
        $this->target = Skin::makeSpecialUrl('SmsReport');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param $par
     */
    function execute( $par = null )
    {
        global $wgUser;
        $gr = $wgUser->getGroups();
        $admin = in_array("bureaucrat", $gr) || in_array("sysop", $gr);

        global $wgOut;
        $wgOut->setPageTitle("SMS Report");
        $out = '';
        if($admin)
        {
            $out .="== Account Balance ==\n";
            $out .= '<font size="20" style="text-align:center">'.Sms::getLatestBalance()."</font>\n\n\n";
        }
        $out .= "== Delivery ==\n";
        $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"
! Status !! Number !! Date
";
        $pending = Sms::getPending();
        foreach($pending as $sms)
        {
            $number = $sms['number'];
            if(!$admin)
                $number = substr($number, 0, strlen($number) - 3) . "<font color=gray>XXX</font>";
            $out .= "|-\n";
            $out .= "| Pending || $number || $sms[date]";
            if($admin)
                $out.="|| $sms[text]";
            $out.="\n";
        }
        $report = Sms::getReport();
        foreach($report as $sms)
        {
            $number = $sms['number'];
            if(!$admin)
                $number = substr($number, 0, strlen($number) - 3) . "<font color=gray>XXX</font>";
            if( strstr($sms['status'], 'Error') )
                $statcolor = "style=\"background: #FFA0A0\" | ";
            elseif( strstr($sms['status'], 'OK') )
                $statcolor = "style=\"background: #A0FFA0\" | ";
            else
                $statcolor = '';
            $status = preg_replace("/([a-z])([A-Z])/", '$1 $2', $sms['status']);
            $status = preg_replace("/OK/", 'OK, ', $status);
            
            $out .= "|-\n";
            $out .= "| $statcolor $status || $number || $sms[date]";
            if($admin)
                $out.="|| $sms[text]";
            $out.="\n";
        }
        $out .= '|}';

        if($admin)
        {
            $out .="\n\n== Balance Reports ==\n";
            $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"
    ! Date !! Balance !! Text
    ";
            $bal = Sms::getBalanceReports();
            foreach($bal as $sms)
            {
                $out .= "|-\n";
                $out .= "| $sms[date] || $sms[balance] || $sms[text]\n";
            }
            $out .= '|}';
        }
        $wgOut->addWikiText($out);
    }
}

