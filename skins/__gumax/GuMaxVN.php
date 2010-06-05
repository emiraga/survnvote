<?php
/*
 * ----------------------------------------------------------------------------
 * 'GuMaxVN' style sheet for CSS2-capable browsers.
 *       Loosely based on the monobook style
 *
 * @Version 2.0
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
class SkinGuMaxVN extends SkinTemplate {
    /** Using GuMax */

	function initPage( OutputPage $out ) {
		parent::initPage( $out );
		$this->skinname  = 'gumaxvn';
		$this->stylename = 'gumaxvn';
		$this->template  = 'GuMaxVNTemplate';

	}

	function setupSkinUserCss( OutputPage $out ) {
		global $wgHandheldStyle;

		parent::setupSkinUserCss( $out );

		// Append to the default screen common & print styles...
		$out->addStyle( 'gumaxvn/gumax_main.css', 'screen' );
		if( $wgHandheldStyle ) {
			// Currently in testing... try 'chick/main.css'
			$out->addStyle( $wgHandheldStyle, 'handheld' );
		}

		$out->addStyle( 'monobook/IE50Fixes.css', 'screen', 'lt IE 5.5000' );
		$out->addStyle( 'monobook/IE55Fixes.css', 'screen', 'IE 5.5000' );
		$out->addStyle( 'monobook/IE60Fixes.css', 'screen', 'IE 6' );
		$out->addStyle( 'monobook/IE70Fixes.css', 'screen', 'IE 7' );

		$out->addStyle( 'monobook/rtl.css', 'screen', '', 'rtl' );
		
		$out->addStyle( 'gumaxvn/gumax_print.css', 'print' );
	}

}

/**
 * @todo document
 * @addtogroup Skins
 */
class GuMaxVNTemplate extends QuickTemplate {
	var $skin;
	/**
	 * Template filter callback for MonoBook skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		global $wgRequest;
		$this->skin = $skin = $this->data['skin'];
		$action = $wgRequest->getText( 'action' );

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
		<?php $this->html('csslinks') ?>

		<!--[if lt IE 7]><script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath') ?>/common/IEFixes.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"></script>
		<meta http-equiv="imagetoolbar" content="no" /><![endif]-->

		<?php print Skin::makeGlobalVariablesScript( $this->data ); ?>

		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"><!-- wikibits js --></script>
		<!-- Head Scripts -->
<?php $this->html('headscripts') ?>
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
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script>
<?php	}
		if($this->data['userjsprev']) { ?>
		<script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script>
<?php	}
		if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>
	</head>
<body<?php if($this->data['body_ondblclick']) { ?> ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
<?php if($this->data['body_onload']) { ?> onload="<?php $this->text('body_onload') ?>"<?php } ?>
 class="mediawiki <?php $this->text('dir') ?> <?php $this->text('pageclass') ?> <?php $this->text('skinnameclass') ?>">


<!-- ##### gumax-wrapper ##### -->
<div id="gumax-wrapper">

<!-- ===== gumax-page ===== -->
<div class="gumax-page"><a name="gumax-top"><!-- --></a>

	<!-- ///// gumax-header ///// -->
    <div id="gumax-header">
        <a name="top" id="contentTop"></a>

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
		<div class="visualClear"></div>
    </div>
	<!-- ///// end of gumax-header ///// -->

<div id="gumax-spacer"><!-- --></div>

<div id="contentHeadLeft"><div id="contentHeadRight"><div id="contentHeadCenter"></div></div></div>
	<div id="contentBodyLeft"><div id="contentBodyRight"><div id="contentBodyCenter">
		<div id="contentMain">
<!-- gumax-content -->
<div id="gumax-content">

	<table class="gumax-caption" width="100%"><tr><td>
		<!-- gumax-content-actions -->
		<div id="gumax-content-actions">
			<ul>
				<?php $lastkey = end(array_keys($this->data['content_actions']))
				?><?php foreach($this->data['content_actions'] as $key => $action) {
				?><li id="ca-<?php echo htmlspecialchars($key) ?>" <?php
					   if($action['class']) { ?>class="<?php echo htmlspecialchars($action['class']) ?>"<?php }
				?>><a href="<?php echo htmlspecialchars($action['href']) ?>"<?php echo $skin->tooltipAndAccesskey('ca-'.$key) ?>><?php
					   echo htmlspecialchars($action['text']) ?></a><?php // if($key != $lastkey) echo "&#8226;" ?></li>
				<?php } ?>
			</ul>
		</div>
		<!-- end of gumax-content-actions -->
	</td></tr></table>

	<div id="gumax-spacer"><!-- --></div>

	<table class="gumax-row" width="100%"><tr><td class="gumax-row-left" valign="top">

    <!-- Navigation Menu -->
    <div id="gumax-p-navigation">
        <?php foreach ($this->data['sidebar'] as $bar => $cont) { ?>
            <div class='gumax-portlet'>
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
<?php	
		$pageClasses = preg_split(" ", $this->data['pageclass']);
		$page_class = end( $pageClasses );  //echo end( $pageClasses );
		$file_ext_collection = array('.jpg', '.gif', '.png');
		$found = false;
		foreach ($file_ext_collection as $file_ext)
		{
			$gumax_article_picture_file = $this->data['stylepath'] . '/' . $this->data['stylename'] . '/images/pages/' . $page_class . $file_ext;
			if (file_exists( $_SERVER['DOCUMENT_ROOT'] . '/' .$gumax_article_picture_file)) {
				$found = true;
				break;
			} else {
				// $gumax_article_picture_file = $this->data['stylepath'] . '/' . $this->data['stylename'] . '/images/pages/' . 'page-Default.gif'; // default site logo
			}
		}
		if($found) { ?>
			<div id="gumax-article-picture">
				<a style="background-image: url(<?php echo $gumax_article_picture_file ?>);" <?php
					?>href="<?php echo htmlspecialchars( $GLOBALS['wgTitle']->getLocalURL() )?>" <?php
					?>title="<?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?>"></a>
			</div>
			<div class="gumax-article-picture-spacer"></div>
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

		<div id="bodyContent">
			<h3 id="siteSub"><?php $this->msg('tagline') ?></h3>
			<div id="contentSub"><?php $this->html('subtitle') ?></div>
			<?php if($this->data['undelete']) { ?><div id="contentSub2"><?php     $this->html('undelete') ?></div><?php } ?>
			<?php if($this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html('newtalk')  ?></div><?php } ?>
			<?php if($this->data['showjumplinks']) { ?><div id="jump-to-nav"><?php $this->msg('jumpto') ?> <a href="#column-one"><?php $this->msg('jumptonavigation') ?></a>, <a href="#searchInput"><?php $this->msg('jumptosearch') ?></a></div><?php } ?>
			<!-- start content -->
			<?php $this->html('bodytext') ?>
			<?php if($this->data['catlinks']) { $this->html('catlinks'); } ?>
			<!-- end content -->
			<?php if($this->data['dataAfterContent']) { $this->html ('dataAfterContent'); } ?>
			<div class="visualClear"></div>
		</div>


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

		wfRunHooks( 'GuMaxVNTemplateToolboxEnd', array( &$this ) );
		wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this ) );
?>

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
