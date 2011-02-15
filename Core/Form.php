<?php
/**
 * Core - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Form.php 1434 2009-08-23 15:55:35Z vipsoft $
 * 
 * @category Core
 * @package Core
 */

require_once('libraries/HTML/QuickForm.php');

/**
 * Parent class for forms to be included in Smarty
 * 
 * For an example, @see Login_Form
 * 
 * @package Core
 * @see HTML_QuickForm, libs/HTML/QuickForm.php
 * @link http://pear.php.net/package/HTML_QuickForm/
 */
abstract class Core_Form extends HTML_QuickForm
{
	protected $a_formElements = array();
	
	function __construct( $action = '' )
	{
		if(empty($action))
		{
			$action = Core_Url::getCurrentQueryString();
		}
		parent::HTML_QuickForm('form', 'POST', $action);
		
		$this->registerRule( 'checkEmail', 'function', 'Core_Form_isValidEmailString');
		$this->registerRule( 'fieldHaveSameValue', 'function', 'Core_Form_fieldHaveSameValue');
	
		$this->init();
	}
	
	abstract function init();
	
	function getElementList()
	{
		$listElements=array();
		foreach($this->a_formElements as $title =>  $a_parameters)
		{
			foreach($a_parameters as $parameters)
			{
				if($parameters[1] != 'headertext' 
					&& $parameters[1] != 'submit')
				{					
					// case radio : there are two labels but only record once, unique name
					if( !isset($listElements[$title]) 
					|| !in_array($parameters[1], $listElements[$title]))
					{
						$listElements[$title][] = $parameters[1];
					}
				}
			}
		}
		return $listElements;
	}
	
	function addElements( $a_formElements, $sectionTitle = '' )
	{
		foreach($a_formElements as $parameters)
		{
			call_user_func_array(array(&$this , "addElement"), $parameters );
		}
		
		$this->a_formElements = 
					array_merge(
							$this->a_formElements, 
							array( 
								$sectionTitle =>  $a_formElements
								)
						);
	}
	
	function addRules( $a_formRules)
	{
		foreach($a_formRules as $parameters)
		{
			call_user_func_array(array(&$this , "addRule"), $parameters );
		}
		
	}

	function setChecked( $nameElement )
	{
		foreach( $this->_elements as $key => $value)
		{
			if($value->_attributes['name'] == $nameElement)
			{
				$this->_elements[$key]->_attributes['checked'] = 'checked';
			}
		}
	}
}

function Core_Form_fieldHaveSameValue($element, $value, $arg) 
{
	$value2 = Core_Common::getRequestVar( $arg, '', 'string');
	$value2 = Core_Common::unsanitizeInputValue($value2);
	return $value === $value2;
}

function Core_Form_isValidEmailString( $element, $value )
{
	return Core_Helper::isValidEmailString($value);
}