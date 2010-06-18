<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package SmsIntegration
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/DAO/CrowdDAO.php");
require_once("$vgPath/FormControl.php");

/**
 * Special page Crowd
 *
 * @author Emir Habul
 * @package SmsIntegration
 */
class Crowd extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('Crowd');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( false );
        $this->setGroup('Crowd', 'votapedia');
        $this->target = Skin::makeSpecialUrl('Crowd');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        global $wgOut;
        # $wgOut->addWikiText( vfAdapter()->findByEmail('emiraga@gmail.com'));

        if(vfUser()->isAnon())
        {
            $wgOut->showErrorPage( 'crowdnologin', 'crowdnologin-desc', array($wgTitle->getPrefixedDBkey()) );
            return;
        }

        if($par)
        {
            $wgOut->addWikiText($par);
        }
        else
        {
            $this->showCrowdList();
            $this->newCrowdForm();
        }
        /*$this->initItems();
        try
        {
            $user = vfUser()->getUserVO();

            $this->dao = new UserphonesDAO($user);
            global $wgOut, $wgRequest;
            if($wgRequest->getVal('wpSubmit') == wfMsg('add-number'))
            {
                if(! vfUser()->checkEditToken())
                    die('Wrong edit token');
                $phone = vfProcessNumber( $wgRequest->getVal('newnumber') );
            }
        }*/
        /*catch(Exception $e)
        {
            $wgOut->setPageTitle("My Phones Error");
            $wgOut->addWikiText( vfErrorBox( $e->getMessage() ) );
            $wgOut->addReturnTo( Title::newFromText('Special:MyPhones') );
            return;
        }*/
    }
    function showCrowdList()
    {
        global $wgOut;
        $wgOut->setPageTitle("Crowd management");
        $out = '';
        $out .= "== My Crowds ==\nYou are member of:\n";
        $out .= "{| class=\"wikitable sortable\" style=\"width: 100%\"\n! Crowd Name !! Members !! Join Date || class=\"unsortable\" | \n";

        $user = vfUser()->getUserVO();
        
        $dao = new CrowdDAO();
        $crowds = $dao->getCrowdsOfUser($user->userID);
        
        foreach($crowds as $crowd)
        {
            /* @var $crowd CrowdVO */
            $out .= "|-\n";
            $out .= "| [[Special:Crowd/{$crowd->name}|{$crowd->name}]] <br />{$crowd->description} || {$crowd->no_members} || {$crowd->date_added} ||";
            if($crowd->isManager)
                $out .= "Manager";
            $out .= "\n";
        }
        $out .= '|}';
        $wgOut->addWikiText($out);
    }
    function newCrowdForm()
    {
        global $wgOut;
        $items = array(
            'name' => array(
                'type' => 'input',
                'name' => 'Name',
                'explanation' => 'Name of survey any characters other than alphanumberic or space.'
                ),
            'description' => array(
                'type' => 'textarea',
                'name' => 'Description',
                'explanation' => 'Brief description of this Crowd.'
                ),
        );
        $wgOut->addWikiText("== New Crowd ==");
        $form = new FormControl($items);
        $wgOut->addHTML($form->StartForm(Skin::makeSpecialUrl('ProcessCrowd'), '', false));
        $wgOut->addHTML($form->AddPage('Crowd information', array('name','description')));
        $wgOut->addHTML($form->EndForm(wfMsg('create-crowd'), false));
    }
}

