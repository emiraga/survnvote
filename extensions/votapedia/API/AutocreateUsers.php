<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package MediaWikiInterface
 */

if(class_exists('ApiBase'))
{
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
            return array( 'secretkey' => '','name' => '','password' => '','realname' => '','email' => '' );
        }
        /**
         * Get Version.
         *
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
            for($i=0; $i<=5; $i++)
            {
                $time = time() - $i;
                if( $this->getParameter('secretkey') == sha1($wgSecretKey.'-'.$time) )
                {
                    $this->addUser();
                    return;
                }
            }
            $apiResult = $this->getResult();
            $apiResult->addValue( array(), 'error', array('title' => 'Invalid secretkey') );
        }
        /**
         * From the API call, add user to the database.
         */
        private function adduser()
        {
            $name     = $this->getParameter('name');
            $password = $this->getParameter('password');
            $realname = $this->getParameter('realname');
            $email    = $this->getParameter('email');

            $success = vpAutocreateUsers::addToDatabase($name, $password, $realname, $email);

            $apiResult = $this->getResult();
            if( $success )
                $apiResult->addValue( array(), 'success', array() );
            else
                $apiResult->addValue( array(), 'error', array('title' => 'username null or user already exists') );
        }

        /**
         * Add new user to the mediawiki database.
         *
         * @param String $name
         * @param String $password
         * @param String $realname
         * @param String $email
         * @return Boolean true on success otherwise false
         */
        static public function addToDatabase($name, $password, $realname, $email)
        {
            $u = User::newFromName( $name, 'creatable' );
            if ( is_null( $u ) || 0 != $u->idForName() )
                return false;

            $u->setName($name);

            $u->addToDatabase();
            $u->setPassword( $password );
            $u->setEmail( $email );
            $u->setRealName( $realname );
            $u->setToken();

            global $wgAuth;
            $wgAuth->initUser( $u, false );

            $u->setOption('rememberpassword', 0);

            if( User::isValidEmailAddr($email) )
            {
                $u->setEmailAuthenticationTimestamp(time());
            }
            $u->saveSettings();

            # Update user count
            $ssUpdate = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
            $ssUpdate->doUpdate();

            return true;
        }
    }
}//if class_exists('ApiBase')

/**
 * Class used for creating a new users.
 * This class can be used in two scenarios:
 *  1. From a mediawiki extension, in which case it will be directly added.
 *  2. Can be used from a PHP files which is not an extension, in that case
 *     HTTP subrequest will be made to the /api.php and that will perform
 *     the operation of addition. This process is transparent to the caller.
 *
 * @package MediaWikiInterface
 */
class AutocreateUsers
{
    /**
     * Add user.
     *
     * @param String $name
     * @param String $password
     * @param String $realname
     * @param String $email
     * @return Boolean true on success otherwise false
     */
    static public function create($name, $password, $realname, $email)
    {
        global $wgAuth;
        if(class_exists('User') && class_exists('ApiBase') && isset($wgAuth))
        {
            return vpAutocreateUsers::addToDatabase($name, $password, $realname, $email);
        }
        else
        {
            global $wgServer, $wgScriptPath, $wgScriptExtension, $wgSecretKey;

            $secretkey = sha1($wgSecretKey.'-'.time());

            $url = "{$wgServer}{$wgScriptPath}/api$wgScriptExtension?action=vpAutoUser";
            $url .= "&secretkey=".$secretkey;
            $url .= "&format=php";
            $url .= "&name=".urlencode($name);
            $url .= "&password=".urlencode($password);
            $url .= "&realname=".urlencode($realname);
            $url .= "&email=".urlencode($email);

            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $url );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec ($ch);
            curl_close ($ch);

            $data = unserialize( $data );
            return isset($data['success']);
        }
    }
}

