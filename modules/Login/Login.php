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

require_once('core/Module.php');
require_once('core/Helper.php');
require_once('core/View.php');
require_once('modules/Login/LoginForm.php');

class ModuleLogin extends CoreModule {
    var $module_description = array(
        'name'        => 'login',
        'description' => 'Performs login and logout operations',
        'version'     => '0.1',
        'author'      => 'Paul Archer',
    );

    static function _getHooks() {
        $hooks = array(
            array("hook"     => "navigator",
                  "category" => "User", 
                  "name"     => "Logout", 
                  "module"   => "Login", 
                  "action"   => "logout"),
        );

        return $hooks;
    }

    function index() {
        $this->doLogin();
    }
    
    function doLogin() {
        global $allocator;

        /*
        $currentUrl = Helper::getModule() == 'Login' ? 
                              Url::getReferer() : 
                              'index.php' . Url::getCurrentQueryString();
        */
        $currentUrl = 'index.php' . Url::getCurrentQueryString();
        $urlToRedirect = Common::getRequestVar('form_url', $currentUrl,   'string');
        $urlToRedirect = htmlspecialchars_decode($urlToRedirect);


        $form = new LoginForm();
        if ($form->validate()) {
            $user     = $form->getSubmitValue('form_login');
            $password = $form->getSubmitValue('form_password');

            $success = $this->api->checkLogin($user, $password);
            print_r($sucess);
            if ($success) {
                $_SESSION['userid'] = $user;

                /* We have sucessfully logged in, now lets 
                 * display the next page */
                if (!isset($redirect_module) || !isset($redirect_action)) {
                    $redirect_module = 'Sessions';
                }

                Url::redirectToUrl($urlToRedirect);
                return;
            }
        }

        $view = CoreView::factory('login');
        $view->urlToRedirect = $urlToRedirect;
        $view->linkTitle     = 'abc';
        $view->addForm($form);
        $view->subTemplate = 'genericForm.tpl';
        echo $view->render();
    }

    function logout() {
        session_unset();
        session_destroy();

        Helper::redirectToModule('Sessions');
    }
}

?>
