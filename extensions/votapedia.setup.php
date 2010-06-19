<?php
if(!defined('MEDIAWIKI')) define('MEDIAWIKI',true);
define('VOTAPEDIA_SETUP',true);

@require_once('../LocalSettings.php');
@include_once("../AdminSettings.php");
//user/pass of a admin account for mysql that has priviledges for CREATE and DELETE of tables.
$vgDBUserName = $wgDBadminuser;
$vgDBUserPassword = $wgDBadminpassword;

if(! isset($wgSecretKey) || strlen($wgSecretKey) < 20 )
{
    die('$wgSecretKey is not specified or it is too short, votapedia requires this value to be '
            .'set at least 20 characters. http://www.mediawiki.org/wiki/Manual:$wgSecretKey');
}
if(! isset($wgScriptPath) || strlen($wgSecretKey) < 1 )
{
    die('$wgScriptPath is not specified or it is too short, votapedia requires this value to be '
            .'set. http://www.mediawiki.org/wiki/Manual:$wgScriptPath');
}
if(! isset($IP) || strlen($IP) < 1 )
{
    die('$IP is not specified or it is too short, votapedia requires this value to be '
            .'set. http://www.mediawiki.org/wiki/Manual:$IP');
}
if(! isset($wgDBTableOptions) )
{
    die('$wgDBTableOptions is not specified, votapedia requires this value to be '
            .'set. http://www.mediawiki.org/wiki/Manual:$wgDBTableOptions');
}

if(!defined('VOTAPEDIA_TEST'))
    echo '<html><head><title>votapedia installation</title></head><body>';

global $vgScript, $wgScriptPath;
if(defined('VOTAPEDIA_TEST') || isset($_POST['do_install']))
{
    try
    {
        vfDoSetup();
        if(!defined('VOTAPEDIA_TEST'))
            echo "<h1>votapedia installation is complete.</h1>\n";
    }
    catch(Exception $e)
    {
        echo "<p><b>Error: </b>".htmlspecialchars($e->getMessage())."</p>";
        echo "<h2>Alternative: Manual setup</h2>";
        echo "<p>Most likely username/password combination is wrong or you don't have sufficient priviledges. You can try again to enter correct values in setup file.</p>";
        echo "<p>If you cannot get super/master user account, then execute this SQL manually (using cPanel/phpMyAdmin or similar).</p>";
        echo "<pre style='background-color: #CCC;'>";
        vfDoSetup(true);
        echo "</pre>";
    }
    if(isset($_POST['do_install']))
    {
        echo "When you are done with installation, <u>please</u> delete the file <b>votapedia.setup.php</b> from extensions directory.<br>\n";
        echo "<p><a href='$wgScriptPath'>Return to MediaWiki</a></p>";
    }
}
else
{

    echo <<<END_HTML
<h1>Welcome to votapedia installation.</h1>
<p>This script <b>votapedia.setup.php</b> is very dangerous and must be deleted after installation is complete.</p>
<p>Do not run this script if you have already installed votapedia, it will <b>truncate</b> tables from database.</p>
Installation Steps:
<ol>
<li>Make sure that MediaWiki is working properly.</li>
<li>Rename <code>extensions/votapedia/config.sample</code> to <code>config.php</code>, and configure votapedia settings.</li>
<li>Add following line to <a href="http://www.mediawiki.org/wiki/Manual:LocalSettings.php"><b>LocalSettings.php</b></a><br />
    <code><br>require_once("\$IP/extensions/votapedia/votapedia.php");<br><br></code></li>
<li>Set <a href="http://www.mediawiki.org/wiki/Manual:\$wgDBadminuser">\$wgDBadminuser</a>
    and <a href="http://www.mediawiki.org/wiki/Manual:\$wgDBadminpassword">\$wgDBadminpassword</a> in
    <a href="http://www.mediawiki.org/wiki/Manual:Maintenance_scripts"><b>AdminSettings.php</b></a></li>
<li>Open this script in browser (you are doing it right now)</li>
<li>
<form action="$vgScript/../votapedia.setup.php" method="POST">
<input type=submit name=do_install value="Install" />
</form>
</li>
</ol>
END_HTML;
}

if(!defined('VOTAPEDIA_TEST'))
    echo '</body></html>';

/**
 * function vfDoSetup:
 * 
 * Perform actions of votapedia setup
 *
 * @param $justprint Boolean
 */
function vfDoSetup($justprint = false)
{
    global $vgPath, $vgDB, $vgDBPrefix, $wgDBTableOptions;
    require_once("$vgPath/Common.php");

    //which data type to use for specific fields
    //@todo optimize these values
    $tPageID = "INT unsigned";
    $tSurveyID = "INT unsigned";
    $tPresID = "TINYINT unsigned";
    $tChoiceID = "TINYINT unsigned";
    $tUserID = "INT unsigned";
    $tVoteID = "INT unsigned";
    $tPhoneID = "INT unsigned";
    $tCrowdID = "INT unsigned";
    
    //new data types
    $tBoolean = "tinyint(1) unsigned";
    
    $sql = <<<END_SQL
DROP TABLE IF EXISTS {$vgDBPrefix}page;
DROP TABLE IF EXISTS {$vgDBPrefix}presentation;
DROP TABLE IF EXISTS {$vgDBPrefix}survey;
DROP TABLE IF EXISTS {$vgDBPrefix}choice;
DROP TABLE IF EXISTS {$vgDBPrefix}used_receivers;
DROP TABLE IF EXISTS {$vgDBPrefix}phone;
DROP TABLE IF EXISTS {$vgDBPrefix}names;
DROP TABLE IF EXISTS {$vgDBPrefix}user;
DROP TABLE IF EXISTS {$vgDBPrefix}crowd;
DROP TABLE IF EXISTS {$vgDBPrefix}crowd_member;
DROP TABLE IF EXISTS {$vgDBPrefix}crowd_log;
DROP TABLE IF EXISTS {$vgDBPrefix}vote;
DROP TABLE IF EXISTS {$vgDBPrefix}vote_details;

--
-- Table structure for table page
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}page (
  pageID                $tPageID     NOT NULL AUTO_INCREMENT,
  title                 text         NOT NULL,
  startTime             datetime     NOT NULL,
  endTime               datetime     NOT NULL,
  duration              int          NOT NULL,
  author                $tUserID     NOT NULL,
  createTime            timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  smsRequired           $tBoolean    NOT NULL DEFAULT '0',
  showGraphEnd          $tBoolean    NOT NULL DEFAULT '0',
  displayTop            tinyint(4)   NOT NULL DEFAULT '0',
  surveyType            tinyint(4)   NOT NULL DEFAULT '1',
  votesAllowed          tinyint(8)   NOT NULL DEFAULT '1',
  subtractWrong         $tBoolean    NOT NULL DEFAULT '0',
  privacy               tinyint(4)   NOT NULL DEFAULT '1',
  phonevoting           varchar(5)   NOT NULL DEFAULT 'anon',
  webvoting             varchar(5)   NOT NULL DEFAULT 'anon',
  receivers_released    $tBoolean    NOT NULL DEFAULT '0',
  bgimage               varchar(255) NOT NULL DEFAULT 'Defaultbg.jpg',
  PRIMARY KEY (pageID)
) $wgDBTableOptions;

--
-- Table structure for table survey
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}survey (
  pageID        $tPageID    NOT NULL,
  surveyID      $tSurveyID  NOT NULL AUTO_INCREMENT,
  question      text        NOT NULL,
  answer        tinyint(8)  NOT NULL DEFAULT '0',
  points        tinyint(8)  NOT NULL DEFAULT '0',
  PRIMARY KEY (surveyID),
  KEY pageID (pageID)
) $wgDBTableOptions;

--
-- Table structure for table choice
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}choice (
  pageID      $tPageID      NOT NULL,
  surveyID    $tSurveyID    NOT NULL,
  choiceID    $tChoiceID    NOT NULL DEFAULT '1',
  choice      text          NOT NULL,
  points      tinyint(8)    NOT NULL DEFAULT '0',
  receiver    varchar(20)   DEFAULT NULL,
  SMS         varchar(20)   DEFAULT NULL,
  finished    $tBoolean     NOT NULL DEFAULT '0',
  KEY pageID (pageID),
  KEY surveyID (surveyID)
) $wgDBTableOptions;

--
-- Table structure for table presentation
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}presentation (
  pageID             $tPageID     NOT NULL,
  presentationID     $tPresID     NOT NULL,
  name               varchar(50)  NOT NULL,
  startTime          datetime     NOT NULL,
  endTime            datetime     NOT NULL,
  active             $tBoolean    NOT NULL DEFAULT '0',
  KEY(pageID)
) $wgDBTableOptions;

--
-- Table structure for table user
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}user (
  userID       $tUserID     NOT NULL AUTO_INCREMENT,
  username     varchar(255) NOT NULL,
  password     varchar(20)  NOT NULL DEFAULT '',
  isAnon       $tBoolean    NOT NULL,
  PRIMARY KEY  (userID),
  KEY          (username),
  UNIQUE       (username)
) $wgDBTableOptions;

--
-- Table structure for table crowd
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}crowd (
    crowdID       $tCrowdID    NOT NULL AUTO_INCREMENT,
    name          varchar(50)  NOT NULL,
    description   TEXT         NOT NULL DEFAULT '',
    ownerID       $tUserID     NOT NULL,
    no_members    $tUserID   NOT NULL,
    PRIMARY KEY   (crowdID),
    KEY           (name),
    UNIQUE        (name),
    KEY           (ownerID)
) $wgDBTableOptions;

--
-- Table structure for table crowd_member
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}crowd_member (
    crowdID        $tCrowdID    NOT NULL,
    userID         $tUserID     NOT NULL,
    isManager      $tBoolean    NOT NULL,
    show_password  $tBoolean    NOT NULL,
    date_added     datetime     NOT NULL,
    KEY            (crowdID),
    KEY            (userID)
) $wgDBTableOptions;
--
-- Table structure for table crowd_log
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}crowd_log (
    crowdID        $tCrowdID     NOT NULL,
    date_added     datetime      NOT NULL,
    log            varchar(255)  NOT NULL,
    KEY            (crowdID),
    KEY            (userID)
) $wgDBTableOptions;

--
-- Table structure for table vote
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}vote (
  voteID         $tVoteID       NOT NULL AUTO_INCREMENT,
  userID         $tUserID       NOT NULL,
  pageID         $tPageID       NOT NULL,
  surveyID       $tSurveyID     NOT NULL,
  presentationID $tPresID       NOT NULL,
  choiceID       $tChoiceID     NOT NULL,
  PRIMARY KEY    (voteID)
) $wgDBTableOptions;

--
-- Table structure for table vote_details
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}vote_details (
  voteID         $tVoteID    NOT NULL,
  voteDate       datetime    NOT NULL,
  voteType       varchar(6)  NOT NULL,
  comments       varchar(50) NOT NULL,
  PRIMARY KEY    (voteID)
) $wgDBTableOptions;

--
-- Table structure for table used_receivers
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}used_receivers (
  receiver  varchar(20)  NOT NULL,
  UNIQUE(receiver)
) $wgDBTableOptions;

--
-- Table structure for table phone
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}phone
(
  phoneID      $tPhoneID     NOT NULL AUTO_INCREMENT,
  userID       $tUserID      NOT NULL,
  phonenumber  varchar(20)   NOT NULL,
  dateadded    datetime      NOT NULL,
  status       tinyint(4)    NOT NULL default 0,
  confirmcode  varchar(20),
  confirmsent  datetime,
  PRIMARY KEY  (phoneID)
) $wgDBTableOptions;

--
-- Table structure for table names
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}names
(
  name    varchar(20)  NOT NULL,
  taken   $tBoolean    NOT NULL default 0,
  UNIQUE(name)
) $wgDBTableOptions;

END_SQL;

    $commands = preg_split('/;/', $sql);
    foreach($commands as $sql)
    {
        $sql = trim($sql);
        if($sql)
        {
            if(! $justprint)
                $vgDB->Execute($sql);
            else
                echo htmlspecialchars($sql) . ";\n";
        }
    }

    $names = array(
 'abaca', 'abiu', 'abyssinian', 'acerola', 'achiote', 'achira', 'actinidia', 'akee', 'allspice', 'almond', 'alpine', 'alupag', 'amazon',
 'ambarella', 'ambra', 'amra', 'amur', 'ananasnaja', 'andean', 'annatto', 'annona', 'anonilla', 'appalachian', 'apple', 'appleberry',
 'apricot', 'arctic', 'arkurbal', 'arrowart', 'arrowert', 'arrowken', 'arrowroot', 'arrowrot', 'artichoke', 'asiatic', 'atemoya',
 'autumn', 'avocado', 'azarole', 'babaco', 'bacae', 'bacuri', 'bacuripari', 'bacurypary', 'bael', 'baked', 'bakupari', 'bakuri',
 'banana', 'barbados', 'barberry', 'batoko', 'beach', 'bean', 'bear', 'bearss', 'beauty', 'belanda', 'belimb', 'belimbing', 'bell', 'bengal', 'berray',
 'berris', 'berrty', 'berry', 'betel', 'bignai', 'bignay', 'bilimbi', 'billy', 'biriba', 'biribay', 'black', 'blackberry', 'blackbert',
 'blackcap', 'blackwhite', 'blood', 'blooming', 'blue', 'bluebean', 'bluebell', 'blueberra', 'blueberry', 'bokhara', 'bower', 'boysen', 'boysenberry',
 'bramble', 'brazil', 'bread', 'breadfruit', 'breadknot', 'breadnut', 'breadroot', 'brier', 'brush', 'buah', 'buddhas', 'buffalo',
 'bullocks', 'bunch', 'bunchosia', 'buni', 'bunya', 'bunyabunya', 'burdekin', 'bush', 'butter', 'butternut', 'button', 'cabinet', 'cacao', 'cactus',
 'caimito', 'caimo', 'calabash', 'calamondin', 'california', 'calubura', 'camocamo', 'campo', 'camu', 'canary', 'candlenut', 'canistel',
 'cannonball', 'cape', 'caper', 'capulin', 'carambola', 'carissa', 'carob', 'carpathian', 'casana', 'cascara', 'cashew', 'cassabanana', 'cassava',
 'castilla', 'catal', 'catalina', 'cats', 'cattley', 'cereus', 'ceriman', 'ceylo', 'ceylon', 'ceylone', 'champedek', 'changshou', 'charicuela',
 'chaste', 'chayote', 'chempedale', 'cherapu', 'cheremai', 'cherimoya', 'cherry', 'cherryblo', 'cherryroot', 'chess', 'chessapple', 'chestav',
 'chestken', 'chestnull', 'chestnut', 'chia', 'chiaye', 'chicle', 'chico', 'chilean', 'china', 'chincopin', 'chinese', 'chinquapin', 'chitra',
 'chocolate', 'choke', 'chokecherry', 'chokey', 'chupa', 'chupachupa', 'ciku', 'cimarrona', 'cinnamen', 'cinnamon', 'ciruela', 'cirueler', 'ciruelo',
 'ciruet', 'citron', 'citront', 'clove', 'clover', 'clovet', 'cochin', 'cochingoraka', 'cocoa', 'cocona', 'coconut', 'cocoplum', 'coffee', 'colorado',
 'cometure', 'commercial', 'common', 'conch', 'coontie', 'cornelian', 'corosol', 'corozo', 'costa', 'cotopriz', 'country', 'coyo', 'crab',
 'crabap', 'crabapple', 'cranberry', 'cranbert', 'crato', 'cream', 'creeping', 'cuachilote', 'cuban', 'cucumber', 'cupu', 'cupuassu', 'currant',
 'curranter', 'curranton', 'curranty', 'current', 'curry', 'curuba', 'cushion', 'custar', 'custard', 'dalison', 'dalo', 'damser', 'damson',
 'dangleberry', 'darling', 'dasheen', 'date', 'datepalm', 'dateplum', 'david', 'davidsons', 'desert', 'dewberry', 'dogwood', 'downy', 'dragon',
 'dragons', 'duku', 'dulce', 'duria', 'durian', 'dwarf', 'early', 'east', 'ecuador', 'eddo', 'edible', 'eggfruit', 'elderbar', 'elderber',
 'elderberry', 'elderbert', 'elderbet', 'elepha', 'elephant', 'emblic', 'engkala', 'english', 'escobillo', 'ethiopian', 'etrog',
 'evergreen', 'false', 'farkleberry', 'feijoa', 'fiber', 'fijian', 'filbert', 'finger', 'flatwoods', 'florida', 'floridam', 'floridamia', 'floridan',
 'flower', 'flying', 'fragrant', 'french', 'fried', 'fruit', 'fukushu', 'galanga', 'galangale', 'galanger', 'galumpi', 'gamboge', 'gandaria', 'genip',
 'genipap', 'genipe', 'giant', 'ginger', 'ginkgo', 'ginseng', 'goat', 'goatnut', 'gold', 'golden', 'golder', 'gooseber', 'gooseberry', 'goumer',
 'goumi', 'goumill', 'governors', 'gram', 'granad', 'granada', 'granadera', 'granadia', 'granadiler', 'granadill', 'granadilla', 'granadillo',
 'granar', 'grande', 'grape', 'grapefruit', 'grapeleaved', 'grass', 'grauda', 'green', 'grose', 'grosell', 'grosella', 'groseller', 'ground', 'grugru',
 'grumichama', 'grumixameira', 'guabiro', 'guabiroba', 'guajilote', 'guama', 'guamo', 'guanaba', 'guanabana', 'guanabat', 'guanabell', 'guanaber',
 'guava', 'guavac', 'guavira', 'guayo', 'guiana', 'gumi', 'guyaba', 'habbel', 'hackberry', 'hackbert', 'hand', 'hardy', 'harendog',
 'hawthorn', 'hazel', 'hazelnut', 'heart', 'hedgeroot', 'hedgerose', 'hedgerot', 'hedgerow', 'herbert', 'hibiscus', 'highbush', 'hilama', 'hogger',
 'hoglum', 'hogmer', 'hogplum', 'hogum', 'hondapara', 'honey', 'honeyberry', 'honeycust', 'honeysuckle', 'horango', 'horned', 'horse', 'horserad',
 'horseradish', 'hotten', 'hottentot', 'huckleberry', 'husk', 'hybrid', 'ichan', 'ichang', 'ilama', 'ilang', 'imbe', 'imbu', 'india',
 'ironwood', 'island', 'jabotica', 'jaboticab', 'jaboticaba', 'jabotiken', 'jaboty', 'jack', 'jackfruit', 'jakfruit', 'jamaica',
 'jambell', 'jamberry', 'jambert', 'jambolan', 'jamfruit', 'japanese', 'java', 'javanese', 'jello', 'jelly', 'jerusalem', 'jicama', 'jojoba',
 'jostaberry', 'jujuba', 'jujube', 'juneberry', 'juniper', 'kaki', 'kalo', 'kangaroo', 'karanda', 'kashun', 'katmon', 'kava', 'kawa',
 'kawakawa', 'kenaf', 'kens', 'kepel', 'keppel', 'ketembilla', 'ketoepa', 'khirni', 'king', 'kitembilla', 'kivai', 'kiwano', 'kiwi', 'kiwifruit',
 'kokuwa', 'kola', 'kolomikta', 'koorkup', 'koshum', 'kuko', 'kumquat', 'kuwini', 'kwai', 'lady', 'lakoocha', 'langsat', 'lanzone', 'largo', 'leaf',
 'lemon', 'lettuce', 'liberian', 'lilly', 'lillypilly', 'lime', 'limeberry', 'limon', 'ling', 'lingaro', 'lingonberry', 'lipote', 'lipstick',
 'litchee', 'litchi', 'llama', 'locust', 'longan', 'loquat', 'louvi', 'lovilovi', 'lowbush', 'lucma', 'lucmo', 'lucuma',
 'lulita', 'lulo', 'luma', 'lychee', 'mabolo', 'mabulo', 'macadamia', 'madagascar', 'madrono', 'magnolia', 'maidehair', 'makopa', 'makrut',
 'malabar', 'malay', 'mamey', 'mammee', 'mamoncillo', 'mandarin', 'mangaba', 'mango', 'mangosteen', 'manis', 'manmohpan', 'mape', 'maprang',
 'marang', 'marany', 'marking', 'marmalade', 'marsh', 'martin', 'martinique', 'marula', 'marumi', 'marvala', 'matasano', 'mate', 'matrimony',
 'mauritius', 'mayan', 'mayhaw', 'maypop', 'medlar', 'meiwa', 'melathstome', 'meyer', 'michurin', 'miners', 'miracle', 'mississippi',
 'missouri', 'mocambo', 'mombin', 'monkey', 'monos', 'monstera', 'montesa', 'moosewood', 'mora', 'moreton', 'moringa', 'mountain', 'mowha',
 'mulberry', 'mundu', 'musk', 'myrobalan', 'myrtle', 'mysore', 'nagami', 'namnam', 'nance', 'nanking', 'naranjilla', 'natal', 'nauclea',
 'nectarine', 'neem', 'nervosa', 'night', 'nightblooming', 'nipa', 'nipple', 'nispero', 'note', 'nutmeg', 'ogeechee', 'okari', 'okra', 'olallie',
 'oleaster', 'olive', 'olosapo', 'orange', 'orangeberry', 'oregon', 'organpipe', 'oriental', 'oswego', 'otaheite', 'otaite', 'oval', 'oyster',
 'pacay', 'paco', 'pacura', 'palestine', 'palm', 'palmyra', 'pama', 'panama', 'pandang', 'pandanus', 'paniala', 'papache', 'papaya', 'para',
 'paradise', 'paraguay', 'passion', 'passiona', 'paterno', 'peach', 'peanut', 'pear', 'pecan', 'pedalai', 'pejibaye', 'pepino', 'pepper', 'pero',
 'persim', 'persimmon', 'phalsa', 'philippine', 'phillippine', 'pickle', 'pili', 'pilly', 'pimenta', 'pimento', 'pindo', 'pine',
 'pineapple', 'pinguin', 'pink', 'pinyon', 'pistachio', 'pitahaya', 'pitanga', 'pitaya', 'pitomba', 'plant', 'plantain', 'plantains', 'plum',
 'poha', 'pollia', 'polynesian', 'pomegranate', 'pond', 'poshte', 'potato', 'prairie', 'princess', 'prune', 'pudding', 'puerto', 'pulasan',
 'pummelo', 'purple', 'purpurea', 'puzzle', 'quandong', 'queen', 'queensland', 'quince', 'quincer', 'quinine', 'rabbiteye', 'raisin', 'rambai',
 'rambeh', 'rambutan', 'ramontchi', 'rangpur', 'raspberry', 'rata', 'rhodod', 'rhododendrom', 'rica', 'rican', 'rinon', 'river', 'riverflat',
 'robusta', 'rose', 'roselle', 'rough', 'round', 'roundleaf', 'rowan', 'rowanberry', 'ruffled', 'rukam', 'runealma', 'sago', 'salad',
 'sapote', 'sapoten', 'seeded', 'seedless', 'serviceberry', 'shell', 'shrub', 'snakework', 'soursop', 'spinach', 'spoon', 'strawberry', 'sunflower',
 'susu', 'sweet', 'tamarind', 'taro', 'thorn', 'tomato', 'tree', 'treegrape', 'turnip', 'utan', 'verde', 'vine', 'walker', 'walknot', 'walnull',
 'walnut', 'wampi', 'white', 'wild', 'wildgap', 'wildgrape', 'wine', 'winepalm', 'zealand' );
    $vnames = array();
    foreach($names as $name)
    {
        $vnames[] = "('$name')";
        if(count($vnames) > 50 || $name == 'zealand')
        {
            $sql = "INSERT INTO {$vgDBPrefix}names (name) VALUES".join(',',$vnames);
            if(! $justprint)
                $vgDB->Execute($sql);
            else
                echo htmlspecialchars($sql) . ";\n";
            $vnames = array();
        }
    }
    /* END OF function vfDoSetup */
}

