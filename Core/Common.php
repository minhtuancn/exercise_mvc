<?php
/**
 * Core - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Common.php 1818 2010-01-31 04:23:06Z vipsoft $
 *
 * @category Core
 * @package Core
 */

/**
 * Static class providing functions used by both the CORE of Core and the visitor Tracking engine.
 *
 * This is the only external class loaded by the /piwik.php file.
 * This class should contain only the functions that are used in
 * both the CORE and the piwik.php statistics logging engine.
 *
 * @package Core
 */
class Core_Common
{
	/**
	 * Const used to map the referer type to an integer in the log_visit table
	 */
	const REFERER_TYPE_DIRECT_ENTRY		= 1;
	const REFERER_TYPE_SEARCH_ENGINE	= 2;
	const REFERER_TYPE_WEBSITE			= 3;
	const REFERER_TYPE_CAMPAIGN			= 6;

	/**
	 * Flag used with htmlspecialchar
	 * See php.net/htmlspecialchars
	 */
	const HTML_ENCODING_QUOTE_STYLE		= ENT_COMPAT;

	/**
	 * Returns the path and query part from a URL.
	 * Eg. http://piwik.org/test/index.php?module=CoreHome will return /test/index.php?module=CoreHome
	 *
	 * @param string $url either http://piwik.org/test or /
	 * @return string
	 */
	static function getPathAndQueryFromUrl($url)
	{
		$parsedUrl = parse_url( $url );
		$result = '';
		if(isset($parsedUrl['path']))
		{
			$result .= substr($parsedUrl['path'], 1);
		}
		if(isset($parsedUrl['query']))
		{
			$result .= '?'.$parsedUrl['query'];
		}
		return $result;
	}

	/**
	 * ending WITHOUT slash
	 * @return string
	 */
	static public function getPathToRoot()
	{
		return realpath( dirname(__FILE__). "/.." );
	}

	/**
	 * Returns the value of a GET parameter $parameter in an URL query $urlQuery
	 *
	 * @param string $urlQuery result of parse_url()['query'] and htmlentitied (& is &amp;) eg. module=test&amp;action=toto or ?page=test
	 * @param string $param
	 *
	 * @return string|bool Parameter value if found (can be the empty string!), false if not found
	 */
	static public function getParameterFromQueryString( $urlQuery, $parameter)
	{
		$nameToValue = self::getArrayFromQueryString($urlQuery);
		if(isset($nameToValue[$parameter]))
		{
			return $nameToValue[$parameter];
		}
		return false;
	}

	/**
	 * Returns an URL query string in an array format
	 * The input query string should be htmlspecialchar'ed
	 *
	 * @param string urlQuery
	 * @return array array( param1=> value1, param2=>value2)
	 */
	static public function getArrayFromQueryString( $urlQuery )
	{
		if(strlen($urlQuery) == 0)
		{
			return array();
		}
		if($urlQuery[0] == '?')
		{
			$urlQuery = substr($urlQuery, 1);
		}

		$separator = '&';

		$urlQuery = $separator . $urlQuery;
		//		$urlQuery = str_replace(array('%20'), ' ', $urlQuery);
		$refererQuery = trim($urlQuery);

		$values = explode($separator, $refererQuery);

		$nameToValue = array();

		foreach($values as $value)
		{
			if( false !== strpos($value, '='))
			{
				$exploded = explode('=',$value);
				$name = $exploded[0];

				// if array without indexes
				if( substr($name,-2,2) == '[]' )
				{
					$name = substr($name, 0, -2);
					if( isset($nameToValue[$name]) == false || is_array($nameToValue[$name]) == false )
					{
						$nameToValue[$name] = array();
					}
					array_push($nameToValue[$name],$exploded[1]);
				}
				else
				{
					$nameToValue[$name] = $exploded[1];
				}
			}
		}
		return $nameToValue;
	}

	/**
	 * Create directory if permitted
	 *
	 * @param string $path
	 * @param int $mode (in octal)
	 * @param bool $denyAccess
	 */
	static public function mkdir( $path, $mode = 0755, $denyAccess = true )
	{
		if(!is_dir($path))
		{
			$directoryParent = self::realpath(dirname($path));
			if( is_writable($directoryParent) )
			{
				mkdir($path, $mode, true);
			}
		}

		if($denyAccess)
		{
			self::createHtAccess($path);
		}
	}

	/**
	 * Create .htaccess file in specified directory
	 *
	 * Apache-specific; for IIS @see web.config
	 *
	 * @param string $path without trailing slash
	 */
	static public function createHtAccess( $path )
	{
		@file_put_contents($path . '/.htaccess', 'Deny from all');
	}

	/**
	 * Get canonicalized absolute path
	 * See http://php.net/realpath
	 *
	 * @param string $path
	 * @return string canonicalized absolute path
	 */
	static public function realpath($path)
	{
		if (file_exists($path))
		{
		    return realpath($path);
		}
	    return $path;
	}

	/**
	 * Returns true if the string is a valid filename
	 * File names that start with a-Z or 0-9 and contain a-Z, 0-9, underscore(_), dash(-), and dot(.) will be accepted.
	 * File names beginning with anything but a-Z or 0-9 will be rejected (including .htaccess for example).
	 * File names containing anything other than above mentioned will also be rejected (file names with spaces won't be accepted).
	 *
	 * @param string filename
	 * @return bool
	 *
	 */
	static public function isValidFilename($filename)
	{
		return (0 !== preg_match('/(^[a-zA-Z0-9]+([a-zA-Z_0-9.-]*))$/', $filename));
	}

	/**
	 * Returns true if the string passed may be a URL.
	 * We don't need a precise test here because the value comes from the website
	 * tracked source code and the URLs may look very strange.
	 *
	 * @param string $url
	 * @return bool
	 */
	static function isLookLikeUrl( $url )
	{
		return preg_match('~^(ftp|news|http|https)?://(.*)$~', $url, $matches) !== 0
				&& strlen($matches[2]) > 0;
	}

	/**
	 * Returns the variable after cleaning operations.
	 * NB: The variable still has to be escaped before going into a SQL Query!
	 *
	 * If an array is passed the cleaning is done recursively on all the sub-arrays.
	 * The array's keys are filtered as well!
	 *
	 * How this method works:
	 * - The variable returned has been htmlspecialchars to avoid the XSS security problem.
	 * - The single quotes are not protected so "Core's amazing" will still be "Core's amazing".
	 *
	 * - Transformations are:
	 * 		- '&' (ampersand) becomes '&amp;'
	 *  	- '"'(double quote) becomes '&quot;'
	 * 		- '<' (less than) becomes '&lt;'
	 * 		- '>' (greater than) becomes '&gt;'
	 * - It handles the magic_quotes setting.
	 * - A non string value is returned without modification
	 *
	 * @param mixed The variable to be cleaned
	 * @return mixed The variable after cleaning
	 */
	static public function sanitizeInputValues($value)
	{
		if(is_numeric($value))
		{
			return $value;
		}
		elseif(is_string($value))
		{
			$value = self::sanitizeInputValue($value);

			// Undo the damage caused by magic_quotes; deprecated in php 5.3 but not removed until php 6
			if ( version_compare(phpversion(), '6') === -1
				&& get_magic_quotes_gpc())
			{
				$value = stripslashes($value);
			}
		}
		elseif (is_array($value))
		{
			foreach (array_keys($value) as $key)
			{
				$newKey = $key;
				$newKey = self::sanitizeInputValues($newKey);
				if ($key != $newKey)
				{
					$value[$newKey] = $value[$key];
					unset($value[$key]);
				}

				$value[$newKey] = self::sanitizeInputValues($value[$newKey]);
			}
		}
		elseif( !is_null($value)
			&& !is_bool($value))
		{
			throw new Exception("The value to escape has not a supported type. Value = ".var_export($value, true));
		}
		return $value;
	}

	/**
	 * Sanitize a single input value
	 *
	 * @param string $value
	 * @return string sanitized input
	 */
	static public function sanitizeInputValue($value)
	{
		return htmlspecialchars($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');
	}

	/**
	 * Unsanitize a single input value
	 *
	 * @param string $value
	 * @return string unsanitized input
	 */
	static public function unsanitizeInputValue($value)
	{
		return htmlspecialchars_decode($value, self::HTML_ENCODING_QUOTE_STYLE);
	}

	/**
	 * Returns a sanitized variable value from the $_GET and $_POST superglobal.
	 * If the variable doesn't have a value or an empty value, returns the defaultValue if specified.
	 * If the variable doesn't have neither a value nor a default value provided, an exception is raised.
	 *
	 * @see sanitizeInputValues() for the applied sanitization
	 *
	 * @param string $varName name of the variable
	 * @param string $varDefault default value. If '', and if the type doesn't match, exit() !
	 * @param string $varType Expected type, the value must be one of the following: array, int, integer, string
	 *
	 * @exception if the variable type is not known
	 * @exception if the variable we want to read doesn't have neither a value nor a default value specified
	 *
	 * @return mixed The variable after cleaning
	 */
	static public function getRequestVar($varName, $varDefault = null, $varType = null, $requestArrayToUse = null)
	{
		if(is_null($requestArrayToUse))
		{
			$requestArrayToUse = $_GET + $_POST;
		}
		$varDefault = self::sanitizeInputValues( $varDefault );
		if($varType == 'int')
		{
			// settype accepts only integer
			// 'int' is simply a shortcut for 'integer'
			$varType = 'integer';
		}

		// there is no value $varName in the REQUEST so we try to use the default value
		if(empty($varName)
			|| !isset($requestArrayToUse[$varName])
			|| (	!is_array($requestArrayToUse[$varName])
				&& strlen($requestArrayToUse[$varName]) === 0
				)
		)
		{
			if( is_null($varDefault))
			{
				throw new Exception("The parameter '$varName' isn't set in the Request, and a default value wasn't provided.");
			}
			else
			{
				if( !is_null($varType)
					&& in_array($varType, array('string', 'integer', 'array'))
				)
				{
					settype($varDefault, $varType);
				}
				return $varDefault;
			}
		}

		// Normal case, there is a value available in REQUEST for the requested varName
		$value = self::sanitizeInputValues( $requestArrayToUse[$varName] );

		if( !is_null($varType))
		{
			$ok = false;

			if($varType == 'string')
			{
				if(is_string($value)) $ok = true;
			}
			elseif($varType == 'integer')
			{
				if($value == (string)(int)$value) $ok = true;
			}
			elseif($varType == 'float')
			{
				if($value == (string)(float)$value) $ok = true;
			}
			elseif($varType == 'array')
			{
				if(is_array($value)) $ok = true;
			}
			else
			{
				throw new Exception("\$varType specified is not known. It should be one of the following: array, int, integer, float, string");
			}

			// The type is not correct
			if($ok === false)
			{
				if($varDefault === null)
				{
					throw new Exception("The parameter '$varName' doesn't have a correct type, and a default value wasn't provided.");
				}
				// we return the default value with the good type set
				else
				{
					settype($varDefault, $varType);
					return $varDefault;
				}
			}
		}
		return $value;
	}

	/**
	 * Unserialize (serialized) array
	 *
	 * @param string
	 * @return array or original string if not unserializable
	 */
	public static function unserialize_array( $str )
	{
		// we set the unserialized version only for arrays as you can have set a serialized string on purpose
		if (preg_match('/^a:[0-9]+:{/', $str)
			&& !preg_match('/(^|;|{|})O:[0-9]+:"/', $str)
			&& strpos($str, "\0") === false)
		{
			if( ($arrayValue = @unserialize($str)) !== false
				&& is_array($arrayValue) )
			{
				return $arrayValue;
			}
		}

		// return original string
		return $str;
	}

	/**
	 * Returns a 32 characters long uniq ID
	 *
	 * @return string 32 chars
	 */
	static public function generateUniqId()
	{
		return md5(uniqid(rand(), true));
	}

	/**
	 * Generate random string
	 *
	 * @param string $length string length
	 * @param string $alphabet characters allowed in random string
	 * @return string random string with given length
	 */
	public static function getRandomString($length = 16, $alphabet = "abcdefghijklmnoprstuvwxyz0123456789")
	{
		$chars = $alphabet;
		$str = '';

		list($usec, $sec) = explode(" ", microtime());
		$seed = ((float)$sec+(float)$usec)*100000;
		mt_srand($seed);

		for($i = 0; $i < $length; $i++)
		{
			$rand_key = mt_rand(0, strlen($chars)-1);
			$str  .= substr($chars, $rand_key, 1);
		}
		return str_shuffle($str);
	}

	/**
	 * Returns true if PHP was invoked from command-line interface (shell)
	 *
	 * @since added in 0.4.4
	 * @return bool true if PHP invoked as a CGI or from CLI
	 */
	static public function isPhpCliMode()
	{
		return	PHP_SAPI == 'cli' ||
				(substr(PHP_SAPI, 0, 3) == 'cgi' && @$_SERVER['REMOTE_ADDR'] == '');
	}
}