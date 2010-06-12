<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package MediaWikiInterface
 */

/**
 * API class for creating new users.
 *
 * @package MediaWikiInterface
 */
class vpAutocreateUsers extends ApiBase
{
    /**
     * Allowed parameters in API
     * @return Array
     */
    protected function getAllowedParams()
    {
        return array( 'secretkey' => '','name' => '','password' => '','realname' => '' );
    }
    /**
     * Version.
     * @return Integer
     */
    public function getVersion()
    {
        return 1;
    }
    /**
     * Execute API action of adding new user.
     */
    public function execute()
    {
        global $wgSecretKey;
        if( $this->getParameter('secretkey') != sha1($wgSecretKey))
            die('Invalid secretkey');
        
        $name     = $this->getParameter('name');
        $password = $this->getParameter('password');
        $realname = $this->getParameter('realname');

        $apiResult = $this->getResult();
        $u = User::newFromName( $name, 'creatable' );
        if ( is_null( $u ) )
        {
            $apiResult->addValue( array(), 'error', array('title' => 'username null') );
            return;
        }
        if ( 0 != $u->idForName() )
        {
            $apiResult->addValue( array(), 'error', array('title' => 'username exists') );
            return;
        }
        $u->setName($name);

        $u->addToDatabase();
        $u->setPassword( $password );
        $u->setEmail( '' );
        $u->setRealName( $realname );
        $u->setToken();

        global $wgAuth;
        $wgAuth->initUser( $u, false );

        $u->setOption('rememberpassword', 0);
        $u->saveSettings();

        # Update user count
        $ssUpdate = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
        $ssUpdate->doUpdate();

        $apiResult->addValue( array(), 'success', array() );
    }
}

