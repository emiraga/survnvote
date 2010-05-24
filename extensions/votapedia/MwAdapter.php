<?php
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
        $article->doPurge(); // Directly purge and skip the UI part of purge().
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
        $data = & $api->getResultData();
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
 * Parser function adapter to MediaWiki Parser
 *
 * @author Emir Habul
 *
 */
class MwParser
{
    private $parser;
    private $parserOptions;
    private $wikititle;
    private $isTag;

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
            $this->parserOptions =& ParserOptions::newFromUser($wgUser);
        //default values
        if(! $this->wikititle)
            $this->wikititle =& $wgTitle;
        //parse for normal view
        return $this->parser->parse( $text, $this->wikititle, $this->parserOptions, $linestart, true )->getText();
    }
    public function disableCache()
    {
        $this->parser->disableCache();
    }
}

class MwUser
{
    /** @var String */ private $name;

    function  __construct()
    {
        global $wgUser;
        if($this->isAnon())
        {
            $randnum = rand(10, getrandmax());
            $needcookie = true;
            if(isset($_COOKIE['vcName']))
            {
                $name = $_COOKIE['vcName'];
                list($ip, $num)  = split('-', $name);
                if(intval($num) > 0 && $wgUser->getName() == $ip)
                {
                    $randnum = intval($num);
                    $needcookie = false;
                }
            }
            $this->name = $wgUser->getName()."-".$randnum;
            if($needcookie)
                setcookie('vcName', $this->name, time() + 60*60*24*365); // A year of validity
        }
        else
        {
            $this->name = $wgUser->getName();
        }
        echo "User: {$this->name}<br>\n";
    }
    /**
     * @return String edit token to prevent XSRF
     */
    function editToken()
    {
        global $wgUser;
        return htmlspecialchars( $wgUser->editToken() );
    }
    function checkEditToken()
    {
        global $wgUser, $wgRequest;
        return $wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );
    }
    function getName()
    {
        return $this->name;
    }
    function getDisplayName()
    {
        global $wgUser;
        return $wgUser->getName();
    }
    function isAnon()
    {
        global $wgUser;
        return $wgUser->isAnon();
    }
    private function isAuthor(&$page)
    {
        return $page->getAuthor() == $this->getName();
    }
    function canCreateSurveys()
    {
        global $vgAnonSurveyCreation;
        return $vgAnonSurveyCreation || !$this->isAnon();
    }
    function canControlSurvey(&$page)
    {
        return $this->isAuthor($page);
    }
    function displayName($name)
    {
        list($ip, $num)  = split('-', $name);
        if(intval($num) > 0 && User::isIP($ip))
            return $ip;
        return $author;
    }
}
