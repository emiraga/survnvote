<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
if(!defined('DATABASE_SCHEMA_PAGE'))
{

define('DATABASE_SCHEMA_PAGE','true');

$wgExtensionFunctions[] = "wfExtensionSpecialDatabaseSchema";
//draw the list of columns
function RenderTableAttributes($strTableName, $strTableType){
	global $wgOut;
	$wgOut->addWikiText('=='.$strTableName.'==');

	$wgOut->addWikiText("''--Columns--''");
	$wgOut->addHTML("<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=Navy><TR><TD>Field Name</TD><TD>Nullable?</TD><TD>DataType/Width</TD></TR>");

	//get the list of columns from the database
	$xmlstrQuery = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='".$strTableName."' ORDER BY ORDINAL_POSITION FOR XML AUTO";

	$sx = simplexml_load_file("http://mobile.act.cmis.csiro.au/database/?sql=".urlencode($xmlstrQuery)."&root=root");

	//display the attributes of columns
	foreach($sx->children() as $table){
		$strFieldName=$table['COLUMN_NAME'];
		$strIsNullable= $table['IS_NULLABLE'];
		$strWidth = $table['DATA_TYPE'];
		//get the precision or width of the data type.
		if (! is_null($table['CHARACTER_MAXIMUM_LENGTH'])){
			$strWidth .= ' ('.$table['CHARACTER_MAXIMUM_LENGTH'].')';
		}else
		{
			$strWidth .= ' ('.$table['NUMERIC_PRECISION'].')';
		}

		$wgOut->addHTML("<TR><TD>$strFieldName</TD><TD>$strIsNullable</TD><TD>$strWidth</TD></TR>");
	}
	$wgOut->addHTML("</TABLE>");

	//Constraints
	$wgOut->addWikiText("''--Constraints--''");
	$wgOut->addHTML("<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=Navy><TR><TD>Constraint Type</TD><TD>Constraint Name</TD></TR>");

	//get the list of Constraints from the database
	$xmlstrQuery = "SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME='".$strTableName."' FOR XML AUTO";

	$sx = simplexml_load_file("http://mobile.act.cmis.csiro.au/database/?sql=".urlencode($xmlstrQuery)."&root=root");

	//display the attributes of columns
	foreach($sx->children() as $table){
		$strConstraintName=$table['CONSTRAINT_NAME'];
		$strConstraintType=$table['CONSTRAINT_TYPE'];

		$wgOut->addHTML("<TR><TD>$strConstraintName</TD><TD>$strConstraintType</TD></TR>");
	}
	$wgOut->addHTML("</TABLE><br />");

	//$wgOut->addWikiText($xml);
}
	
function wfExtensionSpecialDatabaseSchema() {
	global $IP, $wgMessageCache;
    require_once( "$IP/includes/SpecialPage.php" );

// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
// The part after '=>' is the default value of the title so again, using Special:Allpages as an example you would have...
// 'allpages' => 'All Pages';
// the part BEFORE the => must be all Lowercase.
	$wgMessageCache->addMessages(array('DatabaseSchema' => 'DatabaseSchema'));

class SpDatabaseSchemaPage extends SpecialPage {
	function SpDatabaseSchemaPage() {
		SpecialPage::SpecialPage( 'DatabaseSchema' );
		$this->includable( false );
	}

	//this function gets called when user opens Special:DatabaseSchema
	function execute( $par = null ) {
		global $wgOut;
		$wgOut->setArticleFlag(false);
		$wgOut->setPageTitle("Database Schema");
		$wgOut->addWikiText('The Tables below are dynamically generated and describe the database schema that is public to ALL users. To view the database schema that You are authorised go [http://mobile.act.cmis.csiro.au/usersdb/template/dbschema.xml?xsl=dbpage2.xsl here] (requires username/password).
	
	You can query all of these tables from your web browser or directly from an online application like this spreadsheet.');
	
		//getting the table list from the SQL server
		$xmlstrQuery = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE (TABLE_TYPE='BASE TABLE' OR TABLE_TYPE='VIEW') AND TABLE_NAME <> 'sysconstraints' AND TABLE_NAME <> 'syssegments' FOR XML AUTO";
	
		$sx = simplexml_load_file("http://mobile.act.cmis.csiro.au/database/?sql=".urlencode($xmlstrQuery)."&root=root");
	
		//display the list of tables and views
		$wgOut->addWikiText('==Tables==');
		foreach($sx->children() as $table){
			$strTableName=$table['TABLE_NAME'];
			$strTableType=$table['TABLE_TYPE'];
			if($strTableType=='BASE TABLE')
				$wgOut->addWikiText('*[['.'#'.$strTableName.'|'.$strTableName.']]');
		}
		$wgOut->addWikiText('==Views==');
		foreach($sx->children() as $table){
			$strTableName=$table['TABLE_NAME'];
			$strTableType=$table['TABLE_TYPE'];
			if($strTableType=='VIEW')
				$wgOut->addWikiText('*[['.'#'.$strTableName.'|'.$strTableName.']]');
		}
	
		$wgOut->addWikiText('
	
	
		\'\'Below is a list of columns in each table/view.\'\'');
	
		//render the table attributes
		foreach($sx->children() as $table){
			$strTableName=$table['TABLE_NAME'];
			$strTableType=$table['TABLE_TYPE'];
			if($strTableType=='BASE TABLE')
				RenderTableAttributes($strTableName, $strTableType);
		}
		foreach($sx->children() as $table){
			$strTableName=$table['TABLE_NAME'];
			$strTableType=$table['TABLE_TYPE'];
			if($strTableType=='VIEW')
				RenderTableAttributes($strTableName, $strTableType);
		}
	}//end function execute
}//end class SpDatabaseSchemaPage
	SpecialPage::addPage( new SpDatabaseSchemaPage );
}
}//define('DATABASE_SCHEMA_PAGE','true');
?>