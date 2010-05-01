<?php
/**
 * Adapted from www.mono-project.com
 *
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
if( !defined( 'MEDIAWIKI' ) )
	die( -1 );
require_once('includes/SkinTemplate.php');
/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class SkinCosmoChips extends SkinTemplate {
	/** Using cosmochips. */
	function initPage( &$out ) {
		SkinTemplate::initPage( $out );
		$this->skinname  = 'cosmochips';
		$this->stylename = 'cosmochips';
		$this->template  = 'CosmoChipsTemplate';
	}
}
/**
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class CosmoChipsTemplate extends QuickTemplate {
	/**
	 * Template filter callback for CosmoChips skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">

<head>
<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
<meta name="robots" content="index,follow" />
<?php $this->html('headlinks') ?>
<title><?php $this->text('pagetitle') ?></title>
<style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css"; /*]]>*/</style>
<link rel="stylesheet" type="text/css" <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> href="<?php $this->text('stylepath') ?>/common/commonPrint.css" />
<!--[if lt IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE50Fixes.css";</style><![endif]-->
<!--[if IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE55Fixes.css";</style><![endif]-->
<!--[if IE 6]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE60Fixes.css";</style><![endif]-->
<!--[if IE]><script type="text/javascript" src="/skins/common/IEFixes.js"></script>
<meta http-equiv="imagetoolbar" content="no" /><![endif]-->
<?php print Skin::makeGlobalVariablesScript( $this->data ); ?>
<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath') ?>/common/wikibits.js"></script>
<?php	if($this->data['jsvarurl']) { ?>
	<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl') ?>"><!-- site js --></script>
<?php	} ?>
<?php	if($this->data['pagecss']) { ?>
	<style type="text/css"><?php $this->html('pagecss') ?></style>
<?php	}
if($this->data['usercss']) { ?>
	<style type="text/css"><?php $this->html('usercss') ?></style>
<?php	}
if($this->data['userjs']) { ?>
	<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs') ?>"></script>
<?php	}
if($this->data['userjsprev']) { ?>
	<script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script>
<?php	}
if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>
<script type="text/javascript"> if (window.isMSIE55) fixalpha(); </script>
<!-- Head Scripts -->
<?php $this->html('headscripts') ?>
</head>

<body <?php if($this->data['body_ondblclick']) { ?>ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
<?php if($this->data['body_onload']) { ?>onload="<?php $this->text('body_onload') ?>"<?php } ?>
class="mediawiki <?php $this->text('nsclass') ?> <?php $this->text('dir') ?>">

<div id="globalWrapper">

<div id="bigWrapper">
<div class="portlet" id="p-logo">
	<a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>" <?php ?>title="<?php $this->msg('mainpage') ?>"></a>
</div>
<div class="portlet" id="p-nav">
	<h5>Navigation</h5>
	<div class="pBody">
	<ul>
	<li><a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>">Chips & Cosmophysique</a></li>
	</ul>
</div>
</div>

<div id="column-content">
<div id="content">
	<a name="top" id="top"></a>
	<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
	<h1 class="firstHeading"><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?></h1>
	<div id="bodyContent">
		<h3 id="siteSub"><?php $this->msg('tagline') ?></h3>
		<div id="contentSub"><?php $this->html('subtitle') ?></div>
		<?php if($this->data['undelete']) { ?>
		<div id="contentSub2"><?php     $this->html('undelete') ?></div><?php } ?>
		<?php if($this->data['newtalk'] ) { ?>
		<div class="usermessage"><?php $this->html('newtalk')  ?></div><?php } ?>
		<br /><br />
		<!-- start content -->
		<?php $this->html('bodytext') ?>
		<?php if($this->data['catlinks']) { ?>
		<div id="catlinks"><?php $this->html('catlinks') ?></div>
		<?php } ?>
		<!-- end content -->
		<div class="visualClear"></div>
	</div>
</div>
</div>

<div id="quicklinks">
	<ul>
	<li id="q-gallery"><a href="http://gallery.cosmochips.net" title="La vieille gallerie">Gallery</a></li>
	<li id="q-resources"><a href="http://resources.cosmochips.net" title="Les ressources de cosmochips.net">Resources</a></li>
	<li id="q-blog"><a href="http://blog.cosmochips.net" title="Le vieux blog">Blog</a></li>
	<li id="q-mono"><a href="http://www.mono-project.com/" title="The Mono Project">Mono</a></li>
	</ul>
</div>

<?php // start super test-bidouille de detection de login
foreach($this->data['personal_urls'] as $logtest => $variable_inutile) {
if (htmlspecialchars($logtest) == "logout") { ?>

<!-- start edit @ tools menu -->
<div id="column-one">
<div id="p-cactions" class="portlet">
	<h5><?php $this->msg('views') ?></h5><ul>
	<?php foreach($this->data['content_actions'] as $key => $tab) { ?>
	<li id="ca-<?php echo htmlspecialchars($key) ?>"<?php	if($tab['class']) { ?> class="<?php echo htmlspecialchars($tab['class']) ?>"<?php } ?>>
	<a href="<?php echo htmlspecialchars($tab['href']) ?>">
	<?php echo htmlspecialchars($tab['text']) ?>
	</a></li>
	<?php } ?>
	</ul>
</div>
<script type="<?php $this->text('jsmimetype') ?>"> if (window.isMSIE55) fixalpha(); </script>
<?php foreach ($this->data['sidebar'] as $bar => $cont) { ?>
<div class='portlet' id='p-<?php echo htmlspecialchars($bar) ?>'>
	<h5><?php $out = wfMsg( $bar ); if (wfEmptyMsg($bar, $out)) echo $bar; else echo $out; ?></h5>
	<div class='pBody'><ul>
	<?php foreach($cont as $key => $val) { ?>
	<li id="<?php echo htmlspecialchars($val['id']) ?>"<?php if ( $val['active'] ) { ?> class="active" <?php } ?>>
	<a href="<?php echo htmlspecialchars($val['href']) ?>">
	<?php echo htmlspecialchars($val['text']) ?>
	</a></li>
	<?php } ?>
	</ul></div>
</div>
<?php } ?>
<div class="portlet" id="p-tb">
	<h5><?php $this->msg('toolbox') ?></h5>
	<div class="pBody"><ul>
	<?php if($this->data['notspecialpage']) { ?>
	<li id="t-whatlinkshere"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href']) ?>"><?php $this->msg('whatlinkshere') ?></a></li>
	<?php if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
	<li id="t-recentchangeslinked"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href']) ?>"><?php $this->msg('recentchangeslinked') ?></a></li>
	<?php }}
	if(isset($this->data['nav_urls']['trackbacklink'])) { ?>
	<li id="t-trackbacklink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href']) ?>"><?php $this->msg('trackbacklink') ?></a></li>
	<?php }
	if($this->data['feeds']) { ?>
	<li id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) { ?>
	<span id="feed-<?php echo htmlspecialchars($key) ?>"><a href="<?php echo htmlspecialchars($feed['href']) ?>"><?php echo htmlspecialchars($feed['text'])?></a> </span>
	<?php } ?></li>
	<?php }
	foreach( array('contributions', 'blockip', 'emailuser', 'upload', 'specialpages') as $special ) {
	if($this->data['nav_urls'][$special]) {	?>
	<li id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href']) ?>"><?php $this->msg($special) ?></a></li>
	<?php }}
	if(!empty($this->data['nav_urls']['print']['href'])) { ?>
	<li id="t-print"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href']) ?>"><?php $this->msg('printableversion') ?></a></li>
	<?php }
	if(!empty($this->data['nav_urls']['permalink']['href'])) { ?>
	<li id="t-permalink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href']) ?>"><?php $this->msg('permalink') ?></a></li>
	<?php } elseif ($this->data['nav_urls']['permalink']['href'] === 'ooekei') { ?>
	<li id="t-ispermalink"><?php $this->msg('permalink') ?></li>
	<?php }
	wfRunHooks( 'CosmoChipsTemplateToolboxEnd', array( &$this ) ); ?>
	</ul></div>
</div>
<?php if( $this->data['language_urls'] ) { ?>
<div id="p-lang" class="portlet">
	<h5><?php $this->msg('otherlanguages') ?></h5>
	<div class="pBody"><ul>
	<?php foreach($this->data['language_urls'] as $langlink) { ?>
	<li class="<?php echo htmlspecialchars($langlink['class'])?>">
	<a href="<?php echo htmlspecialchars($langlink['href']) ?>"><?php echo $langlink['text'] ?></a></li>
	<?php } ?>
	</ul>
	</div>
	</div>
	<?php } ?>
</div>
<!-- end edit @ tools menu -->

<?php }
elseif (htmlspecialchars($logtest) == "login") { ?>

<!-- start user menu -->
<div id="column-one">
	<div class="portlet" id="p-tb">
	<h5><?php $this->msg('toolbox') ?></h5>
	<div class="pBody"><ul>
	<?php if($this->data['notspecialpage']) { ?>
	<li id="t-whatlinkshere"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href']) ?>"><?php $this->msg('whatlinkshere') ?></a></li>
	<?php } ?>
	<?php if(!empty($this->data['nav_urls']['print']['href'])) { ?>
	<li id="t-print"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href']) ?>"><?php $this->msg('printableversion') ?></a></li>
	<?php } ?>
	<?php foreach($this->data['content_actions'] as $key => $tab) { ?>
	<?php if (htmlspecialchars($key) == "viewsource") { ?>
	<li id="ca-<?php echo htmlspecialchars($key) ?>"<?php if($tab['class']) { ?> class="<?php echo htmlspecialchars($tab['class']) ?>"<?php } ?>>
	<a href="<?php echo htmlspecialchars($tab['href']) ?>">
	<?php echo htmlspecialchars($tab['text']) ?></a></li>
	<?php }} ?>
	</ul></div>
	</div>
</div>
<!-- end user menu -->

<?php }} // end super test-bidouille de detection de login ?>

<!-- end of the left (by default at least) column -->
<div class="visualClear"></div>
<div id="footer">
	<div id="p-search" class="portlet">
	<div id="searchBody" class="pBody">
	<form action="<?php $this->text('searchaction') ?>" id="searchform"><div>
	<input id="searchInput" name="search" type="text" <?php if($this->haveMsg('accesskey-search')) { ?>accesskey="<?php $this->msg('accesskey-search') ?>"<?php }
	if( isset( $this->data['search'] ) ) { ?> value="<?php $this->text('search') ?>"<?php } ?> />
	<input type='submit' name="go" class="searchButton" id="searchGoButton" value="<?php $this->msg('searcharticle') ?>" /> 
	</div></form>
	</div>
</div><br /><br />
<div id="p-personal" class="portlet">
	<div class="pBody">
	<?php foreach($this->data['personal_urls'] as $key => $item) { ?>
	<li id="pt-<?php echo htmlspecialchars($key) ?>"<?php
	if ($item['active']) { ?> class="active"<?php } ?>>
	<a href="<?php echo htmlspecialchars($item['href']) ?>"<?php
	if(!empty($item['class'])) { ?> class="<?php echo htmlspecialchars($item['class']) ?>"<?php } ?>><?php echo htmlspecialchars($item['text']) ?></a></li>
	<?php } ?>
	<br /><br />
	<?php
	// Generate additional footer links
	// removed 'viewcount', 'privacy', 'disclaimer'
	$footerlinks = array( 'lastmod', 'numberofwatchingusers', 'credits', 'copyright', 'about', 'tagline', );
	foreach( $footerlinks as $aLink ) {
	if( $this->data[$aLink] ) { ?>
	<?php $this->html($aLink) ?><br />
	<?php }} ?><A HREF="http://www.ipbwiki.com/forums/index.php?download=86">Design by Patrik Roy</A>
	</div>
</div>
<div id="p-personal" class="portlet">
	<div class="pBody">
	<?php if($this->data['poweredbyico']) { ?>
	<div id="f-poweredbyico"><?php $this->html('poweredbyico') ?></div>
	<?php } ?>
	</div></div>
</div>
<?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
</div>
<?php $this->html('reporttime') ?>
<?php if ( $this->data['debug'] ): ?>
<?php endif; ?>
</body>

</html>

<?php wfRestoreWarnings();
} // end of execute() method
} // end of class
?>
