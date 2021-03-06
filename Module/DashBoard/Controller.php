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

class Module_DashBoard_Controller extends Core_Controller 
{
    function getDefaultAction()
    {
        return 'index';
    }
   
    function index() {
        $this->view();
    }

    function view() {
        $view = Core_View::factory('dashboard');

        echo $view->render();
    }

    /*
    function redirectToDashBoardIndex()
    {
        $module='DashBoard';
        $action='index';
        
        parent::redirectToIndex($module,$action);
    }*/
}

?>
