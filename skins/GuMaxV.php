<?php
/*
 * ----------------------------------------------------------------------------
 * 'GuMaxV' style sheet for CSS2-capable browsers.
 *       Loosely based on the monobook style
 *
 * @Version 1.0
 * @Author Paul Y. Gu, <gu.paul@gmail.com>
 * @Copyright paulgu.com 2007 - http://www.paulgu.com/
 * @License: GPL (http://www.gnu.org/copyleft/gpl.html)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 * ----------------------------------------------------------------------------
 */

if( !defined( 'MEDIAWIKI' ) )
    die( -1 );

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @addtogroup Skins
 */
class SkinGuMaxV extends SkinTemplate {
    /** Using GuMax */
    function initPage( &$out ) {
        SkinTemplate::initPage( $out );
        $this->skinname  = 'gumaxv';
        $this->stylename = 'gumaxv';
        $this->template  = 'GuMaxVTemplate';
    }
}

/**
 * @todo document
 * @addtogroup Skins
 */
class GuMaxVTemplate extends QuickTemplate {
    /**
     * Template filter callback for GuMaxV skin.
     * Takes an associative array of data set from a SkinTemplate-based
     * class, and a wrapper for MediaWiki's localization database, and
     * outputs a formatted page.
     *
     * @access private
     */
    function execute() {
		global $wgUser;
		$skin = $wgUser->getSkin();

        // Suppress warnings to prevent notices about missing indexes in $this->data
        wfSuppressWarnings();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="<?php $this->text('xhtmldefaultnamespace') ?>" <?php
    foreach($this->data['xhtmlnamespaces'] as $tag => $ns) {
        ?>xmlns:<?php echo "{$tag}=\"{$ns}\" ";
    } ?>xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
<head>
    <meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
    <?php $this->html('headlinks') ?>
    <title><?php $this->text('pagetitle') ?></title>
    <style type="text/css" media="screen,projection">/*<![CDATA[*/
		@import "<?php $this->text('stylepath') ?>/common/shared.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";
		@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/gumax_main.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";
	/*]]>*/</style>
    <link rel="stylesheet" type="text/css" <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> href="<?php $this->text('stylepath') ?>/common/commonPrint.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
	<link rel="stylesheet" type="text/css" <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/gumax_print.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
    <!--[if lt IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE50Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
    <!--[if IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE55Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
    <!--[if IE 6]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE60Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
    <!--[if IE 7]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE70Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
    <!--[if lt IE 7]><script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath') ?>/common/IEFixes.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"></script>
    <meta http-equiv="imagetoolbar" content="no" /><![endif]-->

    <?php print Skin::makeGlobalVariablesScript( $this->data ); ?>

    <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"><!-- wikibits js --></script>
    <?php    if($this->data['jsvarurl'  ]) { ?>
        <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl'  ) ?>"><!-- site js --></script>
    <?php    } ?>
    <?php    if($this->data['pagecss'   ]) { ?>
        <style type="text/css"><?php $this->html('pagecss'   ) ?></style>
    <?php    }
        if($this->data['usercss'   ]) { ?>
        <style type="text/css"><?php $this->html('usercss'   ) ?></style>
    <?php    }
        if($this->data['userjs'    ]) { ?>
        <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script>
    <?php    }
        if($this->data['userjsprev']) { ?>
        <script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script>
    <?php    }
    if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>
    <!-- Head Scripts -->
    <?php $this->html('headscripts') ?>
</head>

<body <?php if($this->data['body_ondblclick']) { ?>ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
<?php if($this->data['body_onload'    ]) { ?>onload="<?php     $this->text('body_onload')     ?>"<?php } ?>
 class="mediawiki <?php $this->text('nsclass') ?> <?php $this->text('dir') ?> <?php $this->text('pageclass') ?>">

<!-- ##### gumax-wrapper ##### -->
<div id="gumax-wrapper">

<!-- ===== gumax-page ===== -->
<div class="gumax-page"><a name="gumax-top"><!-- --></a>

	<!-- ///// gumax-header ///// -->
    <div id="gumax-header">
        <a name="top" id="contentTop"></a>
        <!-- gumax-p-logo -->
        <div id="gumax-p-logo">
            <div id="p-logo">
            <a style="background-image: url(<?php $this->text('logopath') ?>);" <?php
                ?>href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>" <?php
                ?>title="<?php $this->msg('mainpage') ?>"></a>
            </div>
        </div>
		<script type="<?php $this->text('jsmimetype') ?>"> if (window.isMSIE55) fixalpha(); </script>
        <!-- end of gumax-p-logo -->
    </div>
	<!-- ///// end of gumax-header ///// -->

<div id="gumax-spacer"><!-- --></div>

<div id="contentHeadLeft"><div id="contentHeadRight"><div id="contentHeadCenter"></div></div></div>
	<div id="contentBodyLeft"><div id="contentBodyRight"><div id="contentBodyCenter">
		<div id="contentMain">
<!-- gumax-content -->
<div id="gumax-content">

	<table class="gumax-caption" width="100%"><tr><td>
		<!-- Search -->
		<div id="gumax-p-search">
			<div id="gumax-searchBody" class="gumax-pBody">
				<form action="<?php $this->text('searchaction') ?>" id="searchform"><div>
					<input id="searchInput" name="search" type="text"<?php echo $skin->tooltipAndAccesskey('search');
						if( isset( $this->data['search'] ) ) {
							?> value="<?php $this->text('search') ?>"<?php } ?> />
					<input type='submit' name="go" class="searchButton" id="searchGoButton"	value="<?php $this->msg('searcharticle') ?>" />&nbsp;
					<input type='submit' name="fulltext" class="searchButton" id="mw-searchButton" value="<?php $this->msg('searchbutton') ?>" />
				</div></form>
			</div>
		</div>
		<!-- end of Search -->		
		</td><td width="100%">
        <!-- Login -->
        <div id="gumax-p-login">
            <ul>
              <?php $lastkey = end(array_keys($this->data['personal_urls'])) ?>
              <?php foreach($this->data['personal_urls'] as $key => $item) /* if($this->data['loggedin']==1) */ {
              ?><li id="gumax-pt-<?php echo htmlspecialchars($key) ?>"><a href="<?php
               echo htmlspecialchars($item['href']) ?>"<?php
              if(!empty($item['class'])) { ?> class="<?php
               echo htmlspecialchars($item['class']) ?>"<?php } ?>><?php
               echo htmlspecialchars($item['text']) ?></a>
               <?php //if($key != $lastkey) echo "|" ?></li>
             <?php } ?>
            </ul>
        </div>
        <!-- end of Login -->
	</td></tr></table>

	<div id="gumax-spacer"><!-- --></div>

	<table class="gumax-row" width="100%"><tr><td class="gumax-row-left" valign="top">

    <!-- Navigation Menu -->
    <div id="gumax-p-navigation-wrapper">
        <?php foreach ($this->data['sidebar'] as $bar => $cont) { ?>
            <div class='gumax-portlet' id='gumax-p-navigation'>
                <h5><?php $out = wfMsg( $bar ); if (wfEmptyMsg($bar, $out)) echo $bar; else echo $out; ?></h5>
                    <ul>
                        <?php foreach($cont as $key => $val) { ?>
                            <li id="<?php echo htmlspecialchars($val['id']) ?>"<?php
                            if ( $val['active'] ) { ?> class="active" <?php }
                            ?>><a href="<?php echo htmlspecialchars($val['href']) ?>"><?php echo htmlspecialchars($val['text']) ?></a></li>
                        <?php } ?>
                    </ul>
            </div>
        <?php } ?>
    </div>
    <!-- end of Navigation Menu -->
	</td><td class="gumax-row-right" valign="top">
	<!-- gumax-article-picture -->
	<?php $str1 = $this->data['pageclass']; $str2 = str_replace("page-", "", $str1); $pagename = strtolower($str2) ?> <!-- Get page name -->
	<?php
		$file_ext_collection = array('.gif', '.png', '.jpg');
		$found = false;
		foreach ($file_ext_collection as $file_ext)
		{
			$filename = $_SERVER['DOCUMENT_ROOT'] . '/' . $this->data['stylepath'] . '/' . $this->data['stylename'] . '/images/header/' . $pagename . $file_ext;
			if (file_exists($filename)) {
				$sitelogo = $this->data['stylepath'] . '/' . $this->data['stylename'] . '/images/header/' . $pagename . $file_ext;
				$found = true;
				break;
			} else {
				// $sitelogo = $this->data['stylepath'] . '/' . $this->data['stylename'] . '/images/header/' . 'default.gif'; // default site logo
			}
		}
		if($found) { ?>
			<div id="gumax-article-picture">
				<a style="background-image: url(<?php echo $sitelogo ?>);" <?php
					?>href="<?php echo htmlspecialchars( $GLOBALS['wgTitle']->getLocalURL() )?>" <?php
					?>title="<?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?>"></a>
			</div>
		<?php }
	?>
    <!-- end of gumax-article-picture -->

	<!-- gumax-content-body -->
	<div id="gumax-content-body">
	<!-- content -->
	<div id="content">
		<a name="top" id="top"></a>
		<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
		<h1 class="firstHeading"><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?></h1>

		<!-- gumax-content-actions -->
		<?php //if($this->data['loggedin']==1) { ?>
		<div id="gumax-content-actions" class="gumax-content-actions-top">
			<ul>
				<?php $lastkey = end(array_keys($this->data['content_actions']))
				?><?php foreach($this->data['content_actions'] as $key => $action) {
				?><li id="ca-<?php echo htmlspecialchars($key) ?>" <?php
					   if($action['class']) { ?>class="<?php echo htmlspecialchars($action['class']) ?>"<?php }
				?>><a href="<?php echo htmlspecialchars($action['href']) ?>"<?php echo $skin->tooltipAndAccesskey('ca-'.$key) ?>><?php
					   echo htmlspecialchars($action['text']) ?></a><?php // if($key != $lastkey) echo "&#8226;" ?></li>
				<?php } ?>
				<li id="gumax-ca-jump"><a href="#gumax-bottom" title="Jump to bottom">&#62;&#62;&#62;</a></li>
			</ul>
		</div>
		<?php //} ?>
		<!-- end of gumax-content-actions -->

		<div id= "bodyContent" class="gumax-bodyContent">
			<h3 id="siteSub"><?php $this->msg('tagline') ?></h3>
			<div id="contentSub"><?php $this->html('subtitle') ?></div>
			<?php if($this->data['undelete']) { ?><div id="contentSub2"><?php $this->html('undelete') ?></div><?php } ?>
			<?php if($this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html('newtalk')  ?></div><?php } ?>
			<?php if($this->data['showjumplinks']) { ?><div id="jump-to-nav"><?php $this->msg('jumpto') ?> <a href="#column-one"><?php $this->msg('jumptonavigation') ?></a>, <a href="#searchInput"><?php $this->msg('jumptosearch') ?></a></div><?php } ?>
			<!-- start content -->
			<?php $this->html('bodytext') ?>
			<?php if($this->data['catlinks']) { ?><div id="catlinks"><?php $this->html('catlinks') ?></div><?php } ?>
			<!-- end content -->
			<div class="visualClear"></div>
		</div>

		<!-- gumax-content-actions -->
		<?php if($this->data['loggedin']==1) { ?>
		<div id="gumax-content-actions" class="gumax-content-actions-bottom">
			<ul>
				<?php $lastkey = end(array_keys($this->data['content_actions']))
				?><?php foreach($this->data['content_actions'] as $key => $action) {
				?><li id="ca-<?php echo htmlspecialchars($key) ?>" <?php
					   if($action['class']) { ?>class="<?php echo htmlspecialchars($action['class']) ?>"<?php }
				?>><a href="<?php echo htmlspecialchars($action['href']) ?>"<?php echo $skin->tooltipAndAccesskey('ca-'.$key) ?>><?php
					   echo htmlspecialchars($action['text']) ?></a><?php // if($key != $lastkey) echo "&#8226;" ?></li>
				<?php } ?>
				<li id="gumax-ca-jump"><a href="#gumax-top" title="Jump to top">&#60;&#60;&#60;</a></li>
			</ul>
		</div>
		<?php } ?>
		<!-- end of gumax-content-actions -->

	</div>
	<!-- end of content -->
	</div>
	<!-- end of gumax-content-body -->
	</td></tr></table>

	<div id="gumax-spacer"><!-- --></div>

	<!-- ///// gumax-footer ///// -->
	<div id="gumax-footer">
		<!-- personal tools  -->
		<div id="gumax-personal-tools">
			<ul>
	<?php
		//if($this->data['loggedin']==1) {
		if($this->data['notspecialpage']) { ?>
				<li id="t-whatlinkshere"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href'])
				?>"><?php $this->msg('whatlinkshere') ?></a></li>
	<?php
			if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
				<li id="t-recentchangeslinked"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href'])
				?>"><?php $this->msg('recentchangeslinked') ?></a></li>
	<?php 		}
		}
		if(isset($this->data['nav_urls']['trackbacklink'])) { ?>
			<li id="t-trackbacklink"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href'])
				?>"><?php $this->msg('trackbacklink') ?></a></li>
	<?php 	}
		if($this->data['feeds']) { ?>
			<li id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) {
					?><span id="feed-<?php echo htmlspecialchars($key) ?>"><a href="<?php
					echo htmlspecialchars($feed['href']) ?>"><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;</span>
					<?php } ?></li><?php
		}

		foreach( array('contributions', 'blockip', 'emailuser', 'upload', 'specialpages') as $special ) {

			if($this->data['nav_urls'][$special]) {
				?><li id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href'])
				?>"><?php $this->msg($special) ?></a></li>
	<?php		}
		}

		if(!empty($this->data['nav_urls']['print']['href'])) { ?>
				<li id="t-print"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href'])
				?>"><?php $this->msg('printableversion') ?></a></li><?php
		}

		if(!empty($this->data['nav_urls']['permalink']['href'])) { ?>
				<li id="t-permalink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href'])
				?>"><?php $this->msg('permalink') ?></a></li><?php
		} elseif ($this->data['nav_urls']['permalink']['href'] === '') { ?>
				<li id="t-ispermalink"><?php $this->msg('permalink') ?></li><?php
		}
		//} // if loggedin
		wfRunHooks( 'GuMaxVTemplateToolboxEnd', array( &$this ) ); ?>
			</ul>
		</div>
		<!-- end of personal tools  -->

		<?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
	</div>
	<!-- ///// end of gumax-footer ///// -->

	</div>
	<!-- end of gumax-content -->
		</div>
	</div></div></div>
<div id="contentFootLeft"><div id="contentFootRight"><div id="contentFootCenter"></div></div></div>

	<div id="gumax-spacer"><!-- --></div><a name="gumax-bottom"><!-- --></a>

	<!-- gumax-f-list -->
	<div id="gumax-f-list">
		<ul>
			<?php
					$footerlinks = array(
						'numberofwatchingusers', 'credits',
						'privacy', 'about', 'disclaimer', 'tagline',
					);
					foreach( $footerlinks as $aLink ) {
						if( isset( $this->data[$aLink] ) && $this->data[$aLink] ) {
			?>				<li id="<?php echo$aLink?>"><?php $this->html($aLink) ?></li>
			<?php 		}
					}
			?>
			<li id="f-poweredby"><a href="http://mediawiki.org">Powered by MediaWiki</a></li>
			<li id="f-designby"><a href="http://www.paulgu.com">Design by Paul Gu</a></li>
		</ul>
	</div>
	<!-- end of gumax-f-list -->

	<!-- gumax-f-message -->
	<div id="gumax-f-message">
		<?php if($this->data['lastmod']) { ?><span id="f-lastmod"><?php $this->html('lastmod') ?></span>
		<?php } ?><?php if($this->data['viewcount']) { ?><span id="f-viewcount"><?php  $this->html('viewcount') ?> </span>
		<?php } ?>
	</div>
	<!-- end of gumax-f-message -->

</div>
<!-- ===== end of gumax-page ===== -->

</div> <!-- ##### end of gumax-wrapper ##### -->

<?php $this->html('reporttime') ?>
<?php if ( $this->data['debug'] ): ?>
<!-- Debug output:
<?php $this->text( 'debug' ); ?>

-->
<?php endif; ?>
</body></html>
<?php
	wfRestoreWarnings();
	} // end of execute() method
} // end of class
?>
