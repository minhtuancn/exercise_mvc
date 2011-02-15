<?php
/**
 * Core - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Access.php 1834 2010-02-10 08:32:03Z vipsoft $
 *
 * @category Core
 * @package Core
 */

/**
 * Class to handle User Access:
 * - loads user access from the Core_Auth_Result object 
 * - provides easy to use API to check the permissions for the current (check* methods)
 * 
 * In Core there are mainly 4 access levels
 * - no access
 * - VIEW access
 * - ADMIN access
 * - Super admin access
 *
 * An access level is on a per website basis.
 * A given user has a given access level for a given website.
 * For example:
 * User Noemie has
 * 	- VIEW access on the website 1,
 *  - ADMIN on the website 2 and 4, and
 *  - NO access on the website 3 and 5
 *
 * There is only one Super User. He has ADMIN access to all the websites 
 * and he only can change the main configuration settings.
 *
 * @package Core
 * @subpackage Core_Access
 */
class Access
{	
	/**
	 * Login of the current user
	 *
	 * @var string
	 */
	protected $login = null;
	
	/**
	 * Defines if the current user is the super user
	 * @see isSuperUser()
	 * 
	 * @var bool
	 */
	protected $isSuperUser = false;

	/**
	 * List of available permissions in Core
	 *
	 * @var array
	 */
	static private $availableAccess = array('noaccess', 'view', 'admin', 'superuser');

	/**
	 * Authentification object (see Core_Auth)
	 *
	 * @var Core_Auth
	 */
	private $auth = null;
	
	/**
	 * Returns the list of the existing Access level.
	 * Useful when a given API method requests a given acccess Level.
	 * We first check that the required access level exists.
	 */
	static public function getListAccess()
	{
		return self::$availableAccess;
	}

	function __construct() 
	{
	}
	
	/**
	 * We bypass the normal auth method and give the current user Super User rights.
	 * This should be very carefully used.
	 */
	public function setSuperUser()
	{
		$this->reloadAccessSuperUser();
	}
	
	/**
	 * Returns true if the current user is logged in as the super user
	 *
	 * @return bool
	 */
	public function isSuperUser()
	{
		return $this->isSuperUser;
	}
	
	/**
	 * Returns the current user login
	 *
	 * @return string|null
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * Throws an exception if the user is not the SuperUser
	 * 
	 * @throws Exception
	 */
	public function checkUserIsSuperUser()
	{
		if($this->isSuperUser === false)
		{
			throw new Exception("You can't access this resource as it requires a 'superuser' access.");
		}
	}
}

/**
 *
 * Exception thrown when a user doesn't  have sufficient access.
 * 
 * @package Core
 * @subpackage Core_Access
 */
class Core_Access_NoAccessException extends Exception
{}
