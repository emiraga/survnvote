<?php
if (!defined('MEDIAWIKI')) die();


class FormGenerator
{
	/**
	 * Helper function for user information panel
	 * @param $td1 label for an item
	 * @param $td2 item or null
	 * @param $td3 optional help or null
	 * @return xhtml block
	 */
	public function __construct($pages)
	{
		$this->pages = $pages;
	}
	
	private static function TableRow( $td1, $td2 = null, $td3 = null ) {

		if ( is_null( $td3 ) ) {
			$td3 = '';
		} else {
			$td3 = Xml::tags( 'tr', null,
				Xml::tags( 'td', array( 'class' => 'pref-label', 'colspan' => '2', 'valign'=>'top' ), $td3 )
			);
		}

		if ( is_null( $td2 ) ) {
			$td1 = Xml::tags( 'td', array( 'class' => 'pref-label', 'colspan' => '2', 'valign'=>'top' ), $td1 );
			$td2 = '';
		} else {
			$td1 = Xml::tags( 'td', array( 'class' => 'pref-label', 'valign'=>'top', 'style'=>'font-weight: bold;' ), $td1 );
			$td2 = Xml::tags( 'td', array( 'class' => 'pref-input', 'valign'=>'top' ), $td2 );
		}

		return Xml::tags( 'tr', null, $td1 . $td2 ). $td3 . "\n";
	}
	public function AddPages($form_pages)
	{
		foreach($this->pages as $page)
			$this->AddPage($page[1], $page[0]);
	}
	
	private function CalcID(&$element)
	{
		if(!isset($element['id']))
			$element['id'] = ereg_replace("[^A-Za-z0-9]", "", $element['name']);
	}
	
	public function Validate()
	{
		global $wgRequest;
		$error = '';
		foreach($this->pages as &$page)
		{
			foreach($page[1] as &$element)
			{
				$this->CalcID($element);
				if($wgRequest->getVal($element['id']))
					$value = $wgRequest->getVal($element['id']);
				else
					$value = '';
				
				if(isset($element['process']))
					$value = $element['process']( $value );
				
				if(isset($element['valid']))
					if(! $element['valid']($value, false))
						$error .= '<li>Incorrect value for <u>'.$element['name'].'</u> ('.$element['type'].') field</li>';
				//echo $element['name'].' = '.$value.'<br />';
			}
		}
		return $error;
	}

	public function getDefaultsFromRequest()
	{
		global $wgRequest;
		foreach($this->pages as &$page)
		{
			foreach($page[1] as &$element)
			{
				$this->CalcID($element);
				#if(!isset($element['id']))
				#	$element['id'] = ereg_replace("[^A-Za-z0-9]", "", $element['name']);
				if($wgRequest->getVal($element['id']))
					$element['default'] = $wgRequest->getVal($element['id']);
				else
					$element['default'] = '';
				#process
				if(isset($item['process']))
					$item['default'] = $item['process']( $item['default'] );
			}
		}
	}
	
	public function AddPage(&$page, $title)
	{
		global $wgOut;

		//opentab tab
		$wgOut->addHTML(
			Xml::fieldset( $title ) .
			Xml::openElement( 'table' ) // , array('height' => '300px') 
				// . vpFormFunc::TableRow( Xml::element( 'h2', null, $page['title'] ) )
		);
		
		foreach($page as $item)
		{
			//add form item
			$this->CalcID($item);
			#if(!isset($item['id']))
			#	$item['id'] = ereg_replace("[^A-Za-z0-9]", "", $item['name']);
			if(!isset($item['default']))
				$item['default'] = '';
			
			if(isset($item['process']))
				$item['default'] = $item['process']( $item['default'] );

			if($item['type'] == 'input')
			{
				if(!isset($item['width']))
					$item['width'] = 70;
				$form_element = Xml::input( $item['id'], $item['width'], $item['default'] , array( 'id' => $item['id'] ) );
			}
			elseif($item['type'] == 'select')
			{
				$select = new XMLSelect( $item['id'], $item['id'], $item['default'] );
				foreach($item['options'] as $name => $value )
					$select->addOption( $name, $value );
				$form_element = $select->getHTML();
			}
			elseif($item['type'] == 'textarea')
			{
				$form_element = Xml::textarea( $item['id'], $item['default'], 5, 5, array( 'id' => $item['id'] ) );
			}
			elseif($item['type'] == 'null')
			{
				$form_element = '';
				$item['name'] = $item['id'] = '';
			}
			elseif($item['type'] == 'checkbox')
			{
				if(isset($item['checklabel']))
					$form_element = Xml::checkLabel( $item['checklabel'] , $item['id'], $item['id'], $item['default'] );
				else
					$form_element = Xml::check( $item['id'], $item['default'] );
			}
			else
			{
				die('error');
			}
			if(isset($item['textbefore']))
				$form_element = $item['textbefore'] . $form_element;
			
			if(isset($item['textafter']))
				$form_element .= $item['textafter'];

			if($item['name'])
				$item['name'] .= ':';
			$wgOut->addHTML(
				FormGenerator::TableRow(
					Xml::label( $item['name'], $item['id'] ),
					$form_element,
					Xml::tags('div', array( 'class' => 'prefsectiontip' ),
						$item['explanation'] . 
							(isset($item['learn_more']) ?
							' &nbsp; <span><a href="'.$GLOBALS['wgScriptPath'].'/index.php?title='.$item['learn_more']
							.'"><img src="'.$GLOBALS['wgScriptPath'].'/extensions/votapedia/images/info.gif">Learn more</a></span>'
							: '')
					)
				)
			);
		}
		$wgOut->addHTML( Xml::closeElement( 'table' ) );

		/* next prev buttons
		$wgOut->addHTML( '<table id=\'prefsubmit\' cellpadding=\'0\' width=\'100%\' style=\'background:none;\'><tr>
		<td width=100%></td>
		<td align="left"><input type=button name=next_page onclick=\'javascript:alert( preftoc )\' value=\'Previous\'></td>
		<td align="right"><input type=button name=next_page onclick=\'javascript:alert(45)\' value=\'Next Page\'></td>
		</table>' );*/

		//close tab
		$wgOut->addHTML( Xml::closeElement( 'fieldset' ) );
	}
	
	public function StartForm($action, $id='')
	{
		global $wgOut;
		$wgOut->addHTML(
			Xml::openElement( 'form', array(
				'action' => $action,
				'method' => 'post',
				'id'     => $id,
			) ) .
			Xml::openElement( 'div', array( 'id' => 'preferences' ) )
		);
	}
	public function EndForm($submit)
	{
		global $wgOut,$wgUser;
		$token = htmlspecialchars( $wgUser->editToken() );
		$wgOut->addHTML( "
	<table id='prefsubmit' cellpadding='0' width='100%' style='background:none;'><tr>
		<td><input type='submit' name='wpSubmit' class='btnSavePrefs' value=\"" . $submit . "\" />
		</td>
		<td align='$rtl'></td>
	</tr></table>
	<input type='hidden' name='wpEditToken' value=\"{$token}\" />" );
		$wgOut->addHTML('</div></form>');
	}
	
	public static function RemoveSpecialChars($str)
	{
		$invalidChars  = array('&','#','+','<','>','[',']','|','{','}','/');
		return trim(str_replace($invalidChars, " ", $str));
	}
	
	public static function ConvertToInt($str)
	{
		return intval($str);
	}
}
?>