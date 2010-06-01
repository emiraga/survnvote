<?php
/**
 * Interface to MediaWiki classes. It consists of Adapter classes
 * which follow adapter design pattern.
 *
 * @package Interface
 */

/**
 * MediaWiki Adapter desing pattern.
 * Idea is to have all functions related to MediaWiki in one place
 *
 */
class MwAdapter
{
    /**
     * Purge the cache of a page with a given title
     *
     * @param $title string title of wiki page
     * @deprecated
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

        //$article = new Article( Title::newFromText($title) );
        //$article->doPurge(); // Directly purge and skip the UI part of purge().
        debug_print_backtrace();
        does_not_work_an();
        sdfsd();
    }
    /**
     * Get a list of subcategories of a category
     *
     * @param $category Name of a category
     * @return array with a list of categories
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
     * @param $category name of category
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
}

/**
 * Parser function adapter to MediaWiki Parser.
 * Adapter design pattern.
 *
 * @author Emir Habul
 *
 */
class MwParser
{
    /** @var Parser */        private $parser;
    /** @var ParserOptions */ private $parserOptions;
    /** @var Title */         private $wikititle;
    /** @var Boolean */       private $isTag;

    /**
     * @param $parser Parser
     * @param $options ParserOptions
     * @param $title Title
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
     * @param $text String
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
 */
class MwUser
{
    /** @var String */ private $name;

    /**
     * Construct a MwUser object. Try to get username of anonymous users from cookie.
     *
     * @global $wgUser User
     */
    public function  __construct()
    {
        global $wgUser;
        if(! isset($wgUser))
            throw new Exception('MwUser::__construct global variable $wgUser not found.');
        if($this->isAnon())
        {
            // Track anonymous users with cookies
            $randnum = rand(10, 2000000000);
            $needcookie = true;
            if(isset($_COOKIE['vcName']))
            {
                // Is there a previous cookie?
                $name = $_COOKIE['vcName'];
                list($ip, $num)  = preg_split('/-/', $name);
                if(intval($num) > 0 && $wgUser->getName() == $ip)
                {
                    $randnum = intval($num);
                    $needcookie = false;
                }
            }
            $this->name = $wgUser->getName()."-".$randnum;
            // Need to set a cookie?
            if($needcookie)
                setcookie('vcName', $this->name, time() + 60*60*24*365); // A year of validity
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
     * Get user preferences and options
     *
     * @param $option String option name
     * @return <type>
     */
    public function getOption($option)
    {
        global $wgUser;
        return $wgUser->getOption($wgUser);
    }
    /**
     * Is this user author of PageVO
     *
     * @return Boolean
     */
    public function isAuthor(&$page)
    {
        return $page->getAuthor() == $this->getName();
    }
    /**
     * Can current user create surveys?
     *
     * @return Boolean
     */
    public function canCreateSurveys()
    {
        global $vgAnonSurveyCreation;
        return $vgAnonSurveyCreation || !$this->isAnon();
    }
    /**
     * Can current user control survey?
     *
     * @return Boolean
     */
    function canControlSurvey(&$page)
    {
        return $this->isAuthor($page);
    }
    /**
     * Remove unnecessary information from anonymous usernames
     *
     * @param $name String name of user
     * @return prepare name of user to be displayed
     */
    static function displayName($name)
    {
        list($ip, $num)  = split('-', $name);
        if(intval($num) > 0 && User::isIP($ip))
            return $ip;
        return $author;
    }
}
/*
class CustomUser extends MwUser
{
    public function __construct($name)
    {
        $this->name = $name;
        //don't call parent
    }
    public function getDisplayName()
    {
        return $this->name;
    }
    public function isAnon()
    {
        throw new Exception("I don't know.");
    }
}
*/