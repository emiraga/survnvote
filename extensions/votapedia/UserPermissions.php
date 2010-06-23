<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/DAO/UserDAO.php");
require_once("$vgPath/DAO/PageDAO.php");
require_once("$vgPath/DAO/CrowdDAO.php");

/**
 * Class for checking permission of users
 *
 * @package DataAccessObject
 */
class UserPermissions
{
    /** @var UserVO */ protected $user;

    public function __construct(UserVO &$user)
    {
        $this->user =& $user;
    }
    /**
     * Can current user create surveys?
     *
     * @return Boolean
     */
    public function canCreateSurveys()
    {
        global $vgAnonSurveyCreation;
        return $vgAnonSurveyCreation || (! $this->user->isAnon) || $this->user->isAdmin;
    }
    /**
     * Is this user author of PageVO
     *
     * @return Boolean
     */
    public function isAuthor(PageVO &$page)
    {
        return $page->getAuthor() == $this->user->userID;
    }
    /**
     * Can this user vote in this survey.
     * This function Assumes that survey is running.
     * 
     * @param PageVO $page
     * @param String $how either 'web' or 'phone'
     * @return Boolean
     */
    public function canVote(PageVO &$page, $how)
    {
        if($this->isAuthor($page))
        {
            return false;
        }
        if($how == 'web')
        {
            //web voting
            if( $page->getWebVoting() == 'no' || $this->user->isAnon && $page->getWebVoting() != 'anon')
                return false;
        }
        else
        {
            //phone voting
            if( $page->getPhoneVoting() == 'no' || $this->user->isAnon && $page->getPhoneVoting() != 'anon')
                return false;
        }
        $crdao = new CrowdDAO();
        return $page->crowdID == 0 || $crdao->isMember($page->crowdID, $this->user->userID);
    }
    /**
     * Can current user control survey?
     *
     * @return Boolean
     */
    function canControlSurvey(&$page)
    {
        return $this->isAuthor($page) || $this->user->isAdmin;
    }
}

