<?php
/**
 * Interface to MediaWiki classes. It consists of Adapter classes
 * which follow adapter design pattern.
 *
 * @package MediaWikiInterface
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/DAO/UserDAO.php");

/**
 * MediaWiki Adapter desing pattern.
 * Idea is to have all functions related to MediaWiki in one place
 *
 * @package MediaWikiInterface
 */
class MwAdapter
{
    /**
     * Purge the cache of a page with a given title
     *
     * @param String $title title of wiki page
     */
    function purgePage($title)
    {
        /*$params = new FauxRequest(array('action' => 'purge','titles' => $title));
            $api = new ApiMain($params, true);
            $api->execute();
            $data = & $api->getResultData();
            if(!isset($data['purge'][0]['purged']))
                    throw new Exception('Page purging has failed');
        */
        $article = new Article( Title::newFromText($title) );
        $article->doPurge(); // purge directly
    }
    /**
     * Get a list of subcategories of a category
     *
     * @param String $category Name of a category
     * @return Array with a list of categories
     */
    function getSubCategories($category) // = )

    {
        $params = new FauxRequest(array(
                        'cmtitle' => $category,
                        'action' => 'query',
                        'list' => 'categorymembers',
                        'cmprop' => 'title',
                //'cmsort' => 'timestamp',
        ));
        $api = new ApiMain($params);
        $api->execute();
        $data = $api->getResultData();
        $result = array();
        foreach($data['query']['categorymembers'] as $subcat)
        {
            $result[] = $subcat['title'];
        }
        return $result;
    }
    /**
     * Purge all members that belong to one category
     *
     * @param String $category name of category
     */
    function purgeCategoryMembers( $category )
    {
        //Purge all pages that have this survey included.
        $members = $this->getSubCategories( $category );
        foreach($members as $m)
        {
            $this->purgePage(Title::newFromText( $m )->getFullText());
        }
    }
    /**
     * Find user by email
     *
     * @param String $email
     * @return String username
     */
    function findByEmail($email)
    {
        $dbr = wfGetDB( DB_SLAVE );
        $res = $dbr->select( 'user', '*', array( 'user_email' => $email ), __METHOD__ );
        if($res == false)
            return false;
        $userArray = new UserArrayFromResult( $res );
        foreach ($userArray as $user)
        {
            /* @var $user User */
            if($user->getEmailAuthenticationTimestamp())
            {
                return $user->getName();
            }
        }
        return false;
    }
    /**
     * Get a path of uploaded file given it's name.
     *
     * @param String $name
     * @return String path
     */
    function filePath( $name )
    {
        /* @var $file File */
        $file = wfFindFile($name);
        if($file && $file->exists())
        {
            return $file->getFullUrl();
        }
        return false;
    }
    /**
     * Add javascript to your output. It will not add scripts that have
     * been already included. Also it supports links starting with http://
     *
     * @param String $path
     * @return Nothing
     */
    function addScript($path)
    {
        global $wgOut;
        if(strstr($wgOut->getScript(),$path) !== false)
            return;
        if(substr( $path, 0, 7 ) == 'http://')
        {
            global $wgJsMimeType;
            $wgOut->addScript( "<script type=\"{$wgJsMimeType}\" src=\"$path\"></script>\n" );
        }
        else
        {
            $wgOut->addScriptFile($path);
        }
    }
    /**
     * Detect if we are currently showing a page to the mobile client.
     *
     * @return Boolean
     */
    function isMobile()
    {
        if(!class_exists('MobileSkin'))
            return false;
        $m = new MobileSkin;
        return $m->isMobile();
    }
}

/**
 * Parser function adapter to MediaWiki Parser.
 * Adapter design pattern.
 *
 * @author Emir Habul
 * @package MediaWikiInterface
 */
class MwParser
{
    /** @var Parser */        private $parser;
    /** @var ParserOptions */ private $parserOptions;
    /** @var Title */         private $wikititle;
    /** @var Boolean */       private $isTag;

    /**
     * Construct MwParser
     *
     * @param Parser $parser
     * @param ParserOptions $options
     * @param Title $title
     */
    public function __construct(Parser &$parser, ParserOptions &$options = NULL, Title $title = NULL)
    {
        $this->parser =& $parser;
        $this->options =& $options;
        $this->wikititle = $title;
        $this->isTag = false;
    }
    /**
     * Set that this parser is running within execute "tag"
     */
    public function setTag()
    {
        $this->isTag = true;
    }
    /**
     * Parse the wiki text while removing untrusted tags from the code
     *
     * @param String $text
     */
    public function run($text, $linestart = false)
    {
        global $vgAllowedTags;
        $text = strip_tags($text, $vgAllowedTags);
        // do the parsing inside a tag
        if($this->isTag)
            return $this->parser->recursiveTagParse($text);
        global $wgUser, $wgTitle;
        //default values
        if(! $this->parserOptions)
            $this->parserOptions = ParserOptions::newFromUser($wgUser);
        //default values
        if(! $this->wikititle)
            $this->wikititle =& $wgTitle;
        //parse for normal view
        return $this->parser->parse( $text, $this->wikititle, $this->parserOptions, $linestart, true )->getText();
    }
    /**
     * Disable cache of parser.
     */
    public function disableCache()
    {
        $this->parser->disableCache();
    }
}

/**
 * Interface to MediaWiki User object.
 *
 * Adapter design pattern.
 * @package MediaWikiInterface
 */
class MwUser
{
    /** @var String */ private $name;

    /**
     * Construct a MwUser object. Try to get username of anonymous users from cookie.
     */
    public function  __construct()
    {
        global $wgUser, $wgCookiePrefix;
        if(! isset($wgUser))
            throw new Exception('MwUser::__construct global variable $wgUser not found.');

        if($this->isAnon())
        {
            // Track anonymous users with cookies
            $randnum = rand(10, 2000000000);
            $needcookie = true;
            $cookiename = 'vp_anonUser';

            if(isset($_COOKIE[$wgCookiePrefix.$cookiename]))
                $cookieval = $_COOKIE[$wgCookiePrefix.$cookiename];

            // Is there a previous cookie?
            if(isset($cookieval))
            {
                list($ip, $num)  = preg_split('/-/', $cookieval);
                if(intval($num) > 0 && $wgUser->getName() == $ip)
                {
                    $randnum = intval($num);
                    $needcookie = false;
                }
            }
            $this->name = $wgUser->getName()."-".$randnum;
            // Need to set a cookie?
            if($needcookie)
            {
                global $wgRequest;
                //Set for cookie three month, per Privacy policy
                setcookie( $wgCookiePrefix.$cookiename, $this->name, time() + 60*60*24*30*3, '/');
            }
        }
        else
        {
            $this->name = $wgUser->getName();
        }
    }
    /**
     * Get escaped edit token for current user.
     *
     * @return String edit token to prevent XSRF
     */
    public function editToken()
    {
        global $wgUser;
        return htmlspecialchars( $wgUser->editToken() );
    }
    /**
     * Is provided edit token valid?
     *
     * @return Boolean is edit token valid
     */
    public function checkEditToken()
    {
        global $wgUser, $wgRequest;
        return $wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );
    }
    /**
     * Return username as it is written in database of votapedia
     *
     * @return String username or IP.rand() for anonymous users
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Get username without extra information
     *
     * @return String username of ip of anonymous users
     */
    public function getDisplayName()
    {
        global $wgUser;
        return $wgUser->getName();
    }
    /**
     * Is current user anonymous
     *
     * @return Boolean
     */
    public function isAnon()
    {
        global $wgUser;
        return $wgUser->isAnon();
    }
    /**
     * Is this user admin?
     *
     * @return Boolean
     */
    public function isAdmin()
    {
        global $wgUser;
        return $wgUser->isLoggedIn() && in_array("sysop", $wgUser->getGroups());
    }
    /**
     * Get user preferences and options
     *
     * @param String $option option name
     * @return String
     */
    public function getOption($option)
    {
        global $wgUser;
        return $wgUser->getOption($wgUser);
    }
    /**
     * Remove unnecessary information from anonymous usernames
     *
     * @param String $name name of user
     * @return String prepare name of user to be displayed
     */
    static function convertDisplayName($name)
    {
        $val = preg_split('/-/', $name);
        if(count($val) == 1)
            return $name;
        $ip = $val[0];
        $num = $val[1];
        if(intval($num) > 0 && User::isIP($ip))
            return $ip;
        return $name;
    }
    /**
     * Get user ID from the votapedia database.
     * @return Integer
     */
    function userID()
    {
        return $this->getUserVO()->userID;
    }
    /**
     * @return UserVO
     */
    function getUserVO()
    {
        if(isset($this->userVO))
            return $this->userVO;

        $dao = new UserDAO();
        $user = $dao->findByName( $this->getName() );
        if($user == false)
        {
            $user = new UserVO();
            $user->username = $this->getName();
            $user->password = '';
            $user->isAnon = $this->isAnon();
            $dao->insert($user);
        }
        $user->isAdmin = $this->isAdmin();
        return $this->userVO = $user;
    }
}

