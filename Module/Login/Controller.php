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

class Module_Login_Controller extends Core_Controller 
{
    function index() 
    {
        $this->doLogin();
    }
    
    function doLogin($error_string = null) 
    {
        /*
        $currentUrl = Helper::getModule() == 'Login' ? 
                              Core_Url::getReferer() : 
                              'index.php' . Core_Url::getCurrentQueryString();
        */

        //self::checkForceSslLogin();

        /* Keep reference to the url, so we can redirect there later */
        $currentUrl = 'index.php' . Core_Url::getCurrentQueryString();
        $urlToRedirect = Core_Common::getRequestVar('form_url', $currentUrl,   'string');
        $urlToRedirect = htmlspecialchars_decode($urlToRedirect);

        $form = new Module_Login_LoginForm();
        if ($form->validate()) {
            $api = new Module_Login_API();
            $userid   = $form->getSubmitValue('form_login');
            $password = $form->getSubmitValue('form_password');

            $success = $api->checkLogin($userid, $password);
            if ($success) {
                $user_credentials = $api->getUser($userid);

                $user = new Zend_Session_Namespace('user');
                $user->userid    = $user_credentials['userid'];
                $user->coach     = $user_credentials['coach']     == 't';
                $user->athlete   = $user_credentials['athlete']   == 't';
                $user->superuser = $user_credentials['superuser'] == 't';
                $user->token     = $user_credentials['token'];

                $_SESSION['userid']    = $user_credentials['userid'];
                $_SESSION['coach']     = $user_credentials['coach']     == 't';
                $_SESSION['athlete']   = $user_credentials['athlete']   == 't';
                $_SESSION['superuser'] = $user_credentials['superuser'] == 't';

                /* We have sucessfully logged in, now lets 
                 * display the next page */
                if (!isset($redirect_module) || !isset($redirect_action)) {
                    $redirect_module = 'DashBoard';
                }

                Core_Url::redirectToUrl($urlToRedirect);
                return;
            } else {
                $error_string = 'Incorrect Login Details';
            }
        }

        $view = Core_View::factory('login');
        $view->urlToRedirect = $urlToRedirect;
        $view->addForm($form);
        $view->subTemplate = 'genericForm.tpl';
        $view->AccessErrorString = $error_string;
        echo $view->render();
    }

    /**
     * Clear the session information
     */
    static public function clearSession()
    {
        $authCookieName = Zend_Registry::get('config')->General->login_cookie_name;
        $cookie = new Core_Cookie($authCookieName);
        $cookie->delete();

        Zend_Session::expireSessionCookie();
        Zend_Session::regenerateId();
    }

    /**
     * Create a new user
     */
    function signup() 
    {
        $form = new Module_Login_SignUpForm();
        $view = Core_View::factory('signup');
        $view->errorMessage = "";

        if ($form->validate()) {
            $api      = new Module_Login_API();
            $user_api = new Module_UserManagement_API();

            $userid    = $form->getSubmitValue('form_login');
            $password  = $form->getSubmitValue('form_password');
            $password2 = $form->getSubmitValue('form_passwordconfirm');
            $email     = $form->getSubmitValue('form_email');

            /* Check the passwords match */
            try {
                /* Check if the username exists */
                if ($api->getUser($userid)) {
                    throw new Exception('The username is already taken');
                }

                /* Check the passwords */
                if ($password !== $password2) {
                    throw new Exception('The passwords do not match');
                }

                $user_api->createUser($userid, $password, $email);

                Core_Url::redirectToUrl('index.php');

            } catch (Exception $e) {
                $view->errorMessage = $e->getMessage();
            }
        }

        $view->addForm($form);
        $view->subTemplate = 'genericForm.tpl';
        echo $view->render();
    }

    /**
     * Lost Password
     */
    function lostPassword() 
    {
    }


    /**
     * Logout the current user
     */
    function logout() 
    {
        self::clearSession();
        Core_Helper::redirectToModule('DashBoard');
    }

    /**
     * Check force_ssl_login and redirect if connection isn't secure and not    using a reverse proxy
     *
     * @param none
     * @return void
     */
    protected function checkForceSslLogin()
    {
        $forceSslLogin = Zend_Registry::get('config')->General->force_ssl_login;
        if($forceSslLogin)
        {
            $reverseProxy = Zend_Registry::get('config')->General->reverse_proxy;
            if(!(Core_Url::getCurrentScheme() == 'https' || $reverseProxy))
            {
                $url = 'https://'
                    . Core_Url::getCurrentHost()
                    . Core_Url::getCurrentScriptName()
                    . Core_Url::getCurrentQueryString();
                Core_Url::redirectToUrl($url);
            }
        }
    }

}

?>
