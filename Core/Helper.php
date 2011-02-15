<?php
/*
 *  Description: Display simple single digits of the current weather.
 *  Date:        02/06/2009
 *  
 *  Author:      Paul Archer <ptarcher@gmail.com>
 *
 * Copyright (C) 2009  Paul Archer
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.

 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Core/Common.php');
require_once('Core/Url.php');

class Helper
{
    static private $instance = null;

    static public function getInstance()
    {
        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    function __construct() 
    {
    }

    /**
     * Returns the current module read from the URL (eg. 'API', 'UserSettings', etc.)
     *
     * @return string
     */
    static public function getModule()
    {
        return Common::getRequestVar('module', '', 'string');
    }

    /**
     * Returns the current action read from the URL
     *
     * @return string
     */
    static public function getAction()
    {
        return Common::getRequestVar('action', '', 'string');
    }
/**
     * Redirect to module (and action)
     *
     * @param string $newModule
     * @param string $newAction
     * @return bool false if the URL to redirect to is already this URL
     */
    static public function redirectToModule( $newModule, $newAction = '' )
    {
        $currentModule = self::getModule();
        $currentAction = self::getAction();

        if($currentModule != $newModule
                ||  $currentAction != $newAction )
        {

            $newUrl = 'index.php' . Url::                                 getCurrentQueryStringWithParametersModified(
                    array('module' => $newModule, 'action' => $newAction)
                    );

            Url::redirectToUrl($newUrl);
        }
        return false;
    }


    function __destruct() 
    {
    }
}

?>
