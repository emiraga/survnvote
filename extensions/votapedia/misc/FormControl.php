<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package HTML_Display
 */

/**
 * Class used for drawing tabbed form similar to "Preferences" form in MediaWiki
 *
 * @author Emir Habul
 * @package HTML_Display
 */
class FormControl
{
    private $values;
    private $items;
    private $onFormSubmit;

    /**
     * Constructor of FormControl
     *
     * @param Array $items associative array of form items
     */
    public function __construct(&$items)
    {
        $this->items = &$items;
        $this->values = array();
        $this->onFormSubmit = "return true;";
    }
    /**
     *
     * @return String javascript
     */
    public function getOnFormSubmit()
    {
        return $this->onFormSubmit;
    }
    /**
     *
     * @param String $code javascript
     */
    public function setOnFormSubmit($code)
    {
        $this->onFormSubmit = $code;
    }
    /**
     * Helper function for user information panel
     * @param String $td1 label for an item
     * @param String $td2 item or null
     * @param String $td3 optional help or null
     * @return String xhtml block
     */
    private function TableRow( $td1, $td2 = null, $td3 = null, $td4=null )
    {

        if ( is_null( $td3 ) )
        {
            $td3 = '';
        } else
        {
            $td3 = Xml::tags( 'tr', null,
                    Xml::tags( 'td', array( 'class' => 'pref-label', 'colspan' => '2', 'valign'=>'top' ), $td3 )
            );
        }

        if ( is_null( $td2 ) )
        {
            $td1 = Xml::tags( 'td', array( 'class' => 'pref-label', 'colspan' => '2', 'valign'=>'top' ), $td1 );
            $td2 = '';
        }
        else
        {
            if(is_null( $td4 ))
                $td4 = '';
            else
                $td4 = Xml::tags( 'td', array( 'class' => 'pref-label', 'colspan' => '1', 'valign'=>'top' ), $td4 );

            $td1 = Xml::tags( 'td', array( 'class' => 'pref-label', 'valign'=>'top', 'style'=>'font-weight: bold;' ), $td1 );
            $td2 = Xml::tags( 'td', array( 'class' => 'pref-input', 'valign'=>'top' ), $td2 );
        }

        return Xml::tags( 'tr', null, $td1 . $td2 . $td4 ). $td3 . "\n";
    }
    /**
     * Check whether values in form pass the tests.
     *
     * @return String error if any error
     */
    public function Validate()
    {
        global $wgRequest;
        $error = '';
        foreach($this->items as $id => &$element)
        {
            if(isset($element['valid']) && isset($this->values[$id]))
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
                if(isset($element['process']))
                {
                    $this->setValue( $id , $element['process']( $this->getValue( $id ) ));
                }
            }
            else
            {
                if($this->items[$id]['type'] == 'checkbox')
                {
                    $this->setValue( $id , false );
                }
            }
        }
    }
    /**
     * Set the value in form with given key(name)
     * @param String $name the key
     * @param String $value
     */
    public function setValue($name, $value)
    {
        $this->values[$name] = $value;
    }
    /**
     * Read the value of form with given key(name)
     *
     * @param String $name the key
     * @return String values[$name] or Boolean false if it is not defined.
     */
    public function getValue($name)
    {
        if(isset($this->values[$name]))
            return $this->values[$name];
        else
            return false;
    }
    /**
     * Returns an entire associative array of form values
     * @return Array
     */
    public function getValuesArray()
    {
        return $this->values;
    }
    /**
     *
     * @param String $id
     */
    public function showItem($id)
    {
        global $vgScript, $wgScriptPath;
        $output = '';
        $item = $this->items[$id];

        if(isset($this->values[$id]))
            $value = $this->values[$id];
        elseif( isset ($item['default']) )
            $value = $item['default'];
        else
            $value = '';

        //$value = htmlspecialchars($value);
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
            //$value = htmlspecialchars_decode($value);
            $cols = isset($item['cols'])?$item['cols']:5;
            $rows = isset($item['rows'])?$item['rows']:5;
            $form_element = Xml::textarea( $id, $value, $cols, $rows, array( 'id' => $id ) );
        }
        elseif($item['type'] == 'null')
        {
            $form_element = '';
            $item['name'] = $id = '';
        }
        elseif($item['type'] == 'html')
        {
            $form_element = $item['code'];
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
            throw new Exception('Error in FormControl::AddPage, unknown type.');
        }
        if(isset($item['textbefore']))
            $form_element = $item['textbefore'] . $form_element;

        if(isset($item['textafter']))
            $form_element .= $item['textafter'];

        if(isset($item['learn_more']))
        {
            $item['explanation'] .=' &nbsp; <span>'
                    .'<a href="'.Skin::makeUrl($item['learn_more']).'"><img src="'.$vgScript.'/icons/info.png"> Learn more</a></span>';
        }

        if($item['type'] == 'infobox')
            $item['explanation'] = vfSuccessBox($item['explanation']);

        if($item['name'])
            $item['name'] .= ':';

        $label = 	Xml::label( $item['name'], $id );
        if(isset($item['icon']))
            $label = "<img src='$item[icon]'> ".$label;

        if(isset($item['aftername']))
            $label .= $item['aftername'];

        if(isset($item['explanation']))
            $explanation = $item['explanation'];
        else
            $explanation = '';

        $output = 
                $this->TableRow(
                $label,
                $form_element,
                Xml::tags('div', array( 'class' => 'prefsectiontip', 'style' => 'width: 100%' ), $explanation ),
                isset($item['afterall'])?$item['afterall']:null
        );
        if(isset($item['html']))
            $output .= '<tr><td colspan=2>'. $item['html'] .'</tr>';
        return $output;
    }
    /**
     * Adds a new tab to the output form
     *
     * @param String $title tab name
     * @param Array $add_items of names of form items to be shown in this tab
     */
    public function AddPage($title, $add_items)
    {
        $output = Xml::openElement( 'table' );
        foreach($add_items as $id)
        {
            $output .= $this->showItem($id);
        }
        $output .= Xml::closeElement( 'table' );
        return $this->pageContents($title, $output);
    }
    /**
     * Returns a HTML code for one page in Form.
     *
     * @param String $title title of a page
     * @param String $contents contents of this page as HTML
     */
    public function pageContents($title, $contents)
    {
        $output = Xml::fieldset( $title );
        $output .= $contents;
        $output .= Xml::closeElement( 'fieldset' );
        return $output;
    }
    /**
     * Start drawing the form
     *
     * @param String $action target of a HTML form
     * @param Integer $id id inside HTML of form
     * @return String HTML code
     */
    public function StartForm($action='', $id='', $makediv = true)
    {
        $output = '';
        if($action)
        {
            $output .= Xml::openElement( 'form', array(
            'action' => $action,
            'method' => 'post',
            'id'     => $id,
            ) );
        }
        if($makediv)
            $output .= Xml::openElement( 'div', array( 'id' => 'preferences' ) );
        return $output;
    }
    /**
     * Start drawing the form
     *
     * @param String $action target of a HTML form
     * @param Integer $id id inside HTML of form
     * @return String HTML code
     */
    public function StartFormLite()
    {
        $output = '<div class="lite"><div id="preferences" style="margin: 0; padding: 0em; clear: both; background-color: transparent; border-style: solid none none none;">';
        return $output;
    }
    /**
     * End drawing the form
     *
     * @param String $submit value of submit button in the form
     * @return String HTML code
     */
    public function EndForm($submit = '', $makediv = true)
    {
        $output = '';
        if($submit)
        {
            $token = vfUser()->editToken();
            $output .= "
            <table id='prefsubmit' cellpadding='0' width='100%' style='background:none;'><tr>
                    <td><input type='submit' name='wpSubmit' class='btnSavePrefs' value=\"{$submit}\" onClick='{$this->onFormSubmit}' />
                    </td>
                    <td></td>
            </tr></table>
            <input type='hidden' name='wpEditToken' value=\"{$token}\" />";
        }
        if($makediv)
            $output .= '</div>';
        $output .= '</form>';
        return $output;
    }
    /**
     * End drawing the form
     * 
     * @return String
     */
    public function EndFormLite()
    {
        return '</div></div></form>';
    }
    /**
     * Adds needed scripts to the OutputPage $wgOut
     *
     * @param Boolean $jquery
     */
    function getScriptsIncluded($jquery = false)
    {
        global $vgScript;
        
        vfAdapter()->addScript($vgScript. '/survey.js');
        if($jquery)
        {
            vfAdapter()->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
        }
    }
}

