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

class Module_UserManagement_API extends Core_ModuleAPI 
{
    static private $instance = null;
    /**
     * Returns the singleton ModuleUserManagementAPI
     *
     * @return ModuleUserManagementAPI
     */
    static public function getInstance()
    {
        if (self::$instance == null)
        {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    function getUser($userid = "") 
    {
        if ($userid === "") {
            $userid = Core_Common::getCurrentUserLogin();
        }

        $db = Zend_Registry::get('db');
        $select = $db->select()
                     ->from('t_users',
                             array('userid',
                                   'coach',
                                   'athlete',
                                   'superuser',
                                   'dob'=>'TO_CHAR(dob, \'DD/MM/YYYY\')',
                                   'max_heartrate',
                                   'resting_heartrate',
                                   'rider_weight',
                                   'bike_weight'))
                     ->where('userid = ?', $userid);

        $stmt = $db->query($select);
        $users = $stmt->fetchAll();
        return $users[0];
    }

    function updateSetting($id, $value)
    {
        $db = Zend_Registry::get('db');

        $valid_fields = array('coach',
                              'athlete',
                              'max_heartrate',
                              'resting_heartrate',
                              'rider_weight',
                              'bike_weight',
                              'password',
                              'dob');
        if (!in_array($id, $valid_fields)) {
            return;
        }

        if ($id == 'password') {
            /* Need to combine with salt etc */
            $password_salt = Core_Common::getRandomString(64);
            $password_hash = sha1($value . $password_salt);

            $db->update('t_users',
                    array('password_hash' => $password_hash,
                          'password_salt' => $password_salt),
                    'userid = \''.Core_Common::getCurrentUserLogin().'\'');
        } else {
            $db->update('t_users',
                    array($id => $value),
                    'userid = \''.Core_Common::getCurrentUserLogin().'\'');
        }
    }

    function getUsers() 
    {
        if (!Core_Access::isSuperUser()) {
            throw exception('You need to be super user to perform this action');
        }

        $db = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('t_users',
                             array('userid',
                                   'coach',
                                   'athlete',
                                   'superuser'))
                     ->order('userid DESC');
        $stmt = $db->query($select);

        return $stmt->fetchAll();
    }

    function createUser($userid, $password, $email, $coach = 'f', $athlete = 't', $superuser = 'f') 
    {
        $password_salt = Core_Common::getRandomString(64);
        $password_hash = sha1($password . $password_salt);
        $db   = Zend_Registry::get('db');
        $auth = Zend_Registry::get('auth');
        $auth_token = $auth->getHashTokenAuth($userid, $password_hash);

        if (!Core_Access::isSuperUser()) {
            //throw new Exception('You need to be super user to perform this action');
            $superuser = 'f';
        }

        $db->insert('t_users',
                array('userid'        => $userid,
                      'password_hash' => $password_hash,
                      'password_salt' => $password_salt,
                      'email'         => $email,
                      'coach'         => $coach,
                      'athlete'       => $athlete,
                      'superuser'     => $superuser,
                      'token'         => $auth_token));
    }

    // TODO: Convert this into user groups
    function getExerciseTypes() 
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('t_training_types',
                             array('type_short',
                                   'type'))
                     ->order('type_short');
        $stmt = $db->query($select);

        $types = $stmt->fetchAll();

        /* Convert into a nice display table */
        $exercise_types = array();
        foreach ($types as $type) {
            $description = $type['type_short'] . ' - ' . $type['type'];
            $exercise_types[$type['type_short']] = $description;
        }

        return $exercise_types;
    }

    public function getTokenAuth($login, $password)
    {
        $user = $this->getUser($login);
        /* TODO: */
        return $user['token'];
    }

    public function getBikes()
    {
        $userid = Core_Common::getCurrentUserLogin();
        $db     = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('t_users_bikes',
                             array('userid',
                                   'id',
                                   'name',
                                   'type',
                                   'description',
                                   'created'))
                     ->where('userid = ?', $userid)
                     ->order(array('type', 'name', 'description'));
        $stmt = $db->query($select);
        $bikes = $stmt->fetchAll();

        return $bikes;
    }

    function getBikeData($bike_id)
    {
        $userid = Core_Common::getCurrentUserLogin();
        $db     = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('t_users_bikes_parts',
                             array('userid',
                                   'bike_id',
                                   'id',
                                   'part',
                                   'description',
                                   'replaced_km',
                                   'replaced_date',
                                   'withdrawn_km',
                                   'withdrawn_date'))
                     ->where('userid  = ?', $userid)
                     ->where('bike_id = ?', $bike_id);
        $stmt = $db->query($select);
        $bike = $stmt->fetchAll();

        return $bike;
    }
}

?>
