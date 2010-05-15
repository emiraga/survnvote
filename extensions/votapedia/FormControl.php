<?php
if (!defined('MEDIAWIKI')) die();

/**
 * Class used for drawing tabbed form similar to "Preferences" form in MediaWiki
 * 
 * @author Emir Habul
 *
 */
class FormControl
{
	private $values;
	private $items;
	
	/**
	 * Constructor of FormControl
	 * 
	 * @param $items associative array of form items
	 */
	public function __construct(&$items)
	{
		$this->items = &$items;
		$this->values = array();
	}
	
	/**
	 * Helper function for user information panel
	 * @param $td1 label for an item
	 * @param $td2 item or null
	 * @param $td3 optional help or null
	 * @return xhtml block
	 */
	private function TableRow( $td1, $td2 = null, $td3 = null ) {

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
	/**
	 * Check whether values in form pass the tests.
	 * 
	 * @return error string if any error
	 */
	public function Validate()
	{
		global $wgRequest;
		$error = '';
		foreach($this->items as $id => &$element)
		{
			if(isset($element['valid']))
				if(! $element['valid']($this->values[ $id ], $element, false))
					$error .= '<li>Incorrect value for <u>'.$element['name'].'</u> field.</li>';
		}
		return $error;
	}
	/**
	 * From $wgRequest read the values of form
	 */
	public function loadValuesFromRequest()
	{
		global $wgRequest;
		foreach($this->items as $id => &$element)
		{
			if($wgRequest->getCheck($id))
			{
				$this->setValue( $id, $wgRequest->getVal($id) );
				if(isset($element['process'])) //@todo there is a bug here
					$this->setValue( $id , $element['process']( $this->getValue( $id ) ));
			}
		}
	}
	/**
	 * Set the value in form with given key(name)
	 * @param $name the key
	 * @param $value
	 */
	public function setValue($name, $value)
	{
		$this->values[$name] = $value;
	}
	/**
	 * Read the value of form with given key(name)
	 * 
	 * @param $name the key
	 * @return values[$name]
	 */
	public function getValue($name)
	{
		return $this->values[$name];
	}
	/**
	 * Returns an entire associative array of form values
	 * @return array
	 */
	public function getValuesArray()
	{
		return $this->values;
	}
	/**
	 * Adds a new tab to the output form
	 * 
	 * @param $title tab name
	 * @param $add_items names of form items to be shown in this tab
	 */
	public function AddPage($title, $add_items)
	{
		global $wgOut, $gvScript, $wgScriptPath;
		
		//opentab tab
		$wgOut->addHTML(
			Xml::fieldset( $title ) .
			Xml::openElement( 'table' ) // , array('height' => '300px') 
				// . vpFormFunc::TableRow( Xml::element( 'h2', null, $page['title'] ) )
		);
		
		foreach($add_items as $id)
		{
			$item = $this->items[$id];
			
			if(isset($this->values[$id]))
				$value = $this->values[$id];
			elseif( isset ($item['default']) )
				$value = $item['default'];
			else
				$value = '';

			$value = htmlspecialchars($value);
			if($item['type'] == 'input')
			{
				if(!isset($item['width']))
					$item['width'] = 70;
				$form_element = Xml::input( $id, $item['width'], $value , array( 'id' => $id) );
			}
			elseif($item['type'] == 'select')
			{
				$select = new XMLSelect( $id, $id, $value );
				foreach($item['options'] as $name => $optval )
					$select->addOption( $name, $optval );
				$form_element = $select->getHTML();
			}
			elseif($item['type'] == 'radio')
			{
				$form_element = '';
				foreach($item['options'] as $name => $optval )
					$form_element .= Xml::radioLabel($name, $id, $optval, 
						$id.'-'.$optval, $optval == $value).'<br />';
			}
			elseif($item['type'] == 'textarea')
			{
				$value = str_replace("\r", "\n", $value);
				$value = str_replace("\n\n", "\r", $value);
				$value = htmlspecialchars_decode($value);
				$form_element = Xml::textarea( $id, $value, 5, 5, array( 'id' => $id ) );
			}
			elseif($item['type'] == 'null')
			{
				$form_element = '';
				$item['name'] = $id = '';
			}
			elseif($item['type'] == 'infobox')
			{
				$form_element = '';
				$item['name'] = $id = '';
			}
			elseif($item['type'] == 'checkbox')
			{
				if(isset($item['checklabel']))
					$form_element = Xml::checkLabel( $item['checklabel'] , $id, $id, $value );
				else
					$form_element = Xml::check( $id, $value );
			}
			else
			{
				die('error in FormControl::AddPage');
			}
			if(isset($item['textbefore']))
				$form_element = $item['textbefore'] . $form_element;
			
			if(isset($item['textafter']))
				$form_element .= $item['textafter'];

			if($item['learn_more'])
			{
				$morepage = Title::newFromText($item['learn_more']);
				$item['explanation'] .=' &nbsp; <span><a href="'.$morepage->escapeLocalURL()
					.'"><img src="'.$gvScript.'/images/info.gif">Learn more</a></span>';
			}
			
			if($item['type'] == 'infobox')
				$item['explanation'] = vfSuccessBox($item['explanation']);
			
			if($item['name'])
				$item['name'] .= ':';
			
			$label = 	Xml::label( $item['name'], $id );
			if(isset($item['icon']))
				$label = "<img src='$item[icon]'> ".$label;
			
			$wgOut->addHTML(
				$this->TableRow(
					$label,
					$form_element,
					Xml::tags('div', array( 'class' => 'prefsectiontip' ),
						$item['explanation'] )
				)
			);
			if(isset($item['html']))
				$wgOut->addHTML('<tr><td colspan=2>'. $item['html'] .'</tr>');
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
	/**
	 * Start drawing the form
	 * 
	 * @param $action target of a HTML form
	 * @param $id id inside HTML of form
	 */
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
	/**
	 * End drawing the form
	 * 
	 * @param $submit value of submit button in the form
	 */
	public function EndForm($submit)
	{
		global $wgOut,$wgUser;
		$token = htmlspecialchars( $wgUser->editToken() );
		$wgOut->addHTML( "
	<table id='prefsubmit' cellpadding='0' width='100%' style='background:none;'><tr>
		<td><input type='submit' name='wpSubmit' class='btnSavePrefs' value=\"" . $submit . "\" />
		</td>
		<td></td>
	</tr></table>
	<input type='hidden' name='wpEditToken' value=\"{$token}\" />" );
		$wgOut->addHTML('</div></form>');
	}
	
	/**
	 * Remove Special Character from a string
	 * 
	 * @param $str
	 * @return string
	 */
	public static function RemoveSpecialChars($str)
	{
		$invalidChars  = array('&','#','+','<','>','[',']','|','{','}','/');
		return trim(str_replace($invalidChars, " ", $str));
	}
}
?>