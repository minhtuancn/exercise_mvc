<?php
/**
 * Core - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: iView.php 1420 2009-08-22 13:23:16Z vipsoft $
 * 
 * @category Core
 * @package Core
 */

/**
 * @package Core
 */
interface Core_iView
{
	/**
	 * Outputs the data.
	 * @return mixed (image, array, html...)
	 */
	function render();
}
