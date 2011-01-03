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
require_once('core/View.php');
require_once('modules/Sessions/UploadForm.php');
require_once('modules/Sessions/FITLap.php');
require_once('modules/Sessions/FITSession.php');
require_once('modules/Sessions/FITRecords.php');

class ModuleSessions extends CoreModule {
    var $module_description = array(
        'name'        => 'Session',
        'description' => 'View, create and edit exercise sessions',
        'version'     => '0.1',
        'author'      => 'Paul Archer',
    );

    static function _getHooks() {
        $hooks   = array();
        $hooks[] = array("hook"     => "navigator",
                         "category" => "Sessions", 
                         "name"     => "View Sessions", 
                         "module"   => "Sessions", 
                         "action"   => "view");
        /*
        $hooks[] = array("hook"     => "navigator",
                         "category" => "Sessions", 
                         "name"     => "New Session", 
                         "module"   => "Sessions", 
                         "action"   => "create");
        */

        if (isset($_SESSION['athlete']) && $_SESSION['athlete']) {
            $hooks[] = array("hook"     => "navigator",
                             "category" => "Sessions", 
                             "name"     => "File Upload", 
                             "module"   => "Sessions", 
                             "action"   => "viewUpload");
        }

        return $hooks;
    }
    
    function index() {
        $this->view();
    }

    function view() {
        $sessions = $this->api->getSessions();
        $view = CoreView::factory('sessions');
        $view->sessions = $sessions;
        $view->coach    = $_SESSION['coach'];

        echo $view->render();
    }

    function viewUpload() {
        $form = new SessionUploadForm();
        if ($form->validate()) {
            $upload = $form->getSubmitValue('form_upload');

            exec('/usr/bin/fitdecode -s '.$upload['tmp_name'], $xml_session);
            $xml_session = implode("\n", $xml_session);
            $sessions    = parseSessions($xml_session);
    
            /* There should only be one session */
            if (is_array($sessions)) {
                $session = $sessions[0];
                unset($sessions);
            }

            /* Insert the session data into the database */
            $this->api->createSessionFull($session->start_time,
                                          'E1',
                                          'Untitled',
                                          $session->total_timer_time,
                                          $session->total_distance,
                                          $session->total_calories,
                                          $session->avg_heart_rate,
                                          $session->max_heart_rate,
                                          $session->avg_speed,
                                          $session->max_speed,
                                          $session->total_ascent,
                                          $session->total_descent,
                                          '');

            /* Find the seconds since epoch so we can do simple maths */
            $ftime = strptime($session->start_time, '%FT%T%z');
            $session_epoch = mktime($ftime['tm_hour'],
                                    $ftime['tm_min'],
                                    $ftime['tm_sec'],
                                    1 ,
                                    $ftime['tm_yday'] + 1,
                                    $ftime['tm_year'] + 1900); 
            $session_timestamp = $session->start_time;


            unset($session);
            unset($sessions);

            exec('/usr/bin/fitdecode -r '.$upload['tmp_name'], $xml_records);
            $xml_records = implode("\n", $xml_records);
            $records     = parseRecords($xml_records, $session_epoch);

            if (is_array($records)) {
                $record_prev = $records[0];
            }

            /* TODO: Get these from either the record OR
             * From the user settings */
            $rider_weight = 75;
            $bike_weight  = 9;

            /* Add the matching data points */
            foreach($records as $record) {
               /* Skip duplicates, they will cause issues in graphs */
                if (!isset($record_last) || 
                        $record_last->interval != $record->interval) {

                    if (!isset($record->power)) {
                        $record->power = $this->api->getPower($record->gradient,
                                                              $record->temperature,
                                                              $record->altitude,
                                                              $record->speed,
                                                              $record->speed    - $record_prev->speed,
                                                              $record->interval - $record_prev->interval,

                                                              $rider_weight,
                                                              $bike_weight);
                    }

                    $this->api->insertSessionData($session_timestamp,
                                                  $record->interval,
                                                  $record->distance,
                                                  $record->heart_rate,
                                                  $record->speed,
                                                  $record->position_lat,
                                                  $record->position_long,
                                                  $record->altitude,
                                                  $record->cadence,
                                                  $record->temperature,
                                                  $record->power,
                                                  $record->gradient);
                    $record_prev = $record;
                }
                $record_last = $record;
            }

            /* Calculate the climbs */
            $climbs = $this->api->getClimbCategories();

            $min_climb = $climbs[0];

            /* 500m with an average gradient of more than 3% (cat 5)*/
            /* 500m with more than 15m climb */
            /* Find the points that have a distance of 500m */
            $window_distance = 0;
            $window_altitude = 0;
            $cat             = -1;

            $climb = array();
            $climb['total_climbed'] = 0;
            $climb['max_gradient']  = 0;

            echo count($records);

            for ($front = 0, $back = 0; $front < count($records); $front++) {
                $window_distance += $records[$front]->delta_distance * 1000;
                $window_altitude += $records[$front]->delta_altitude;

                if ($window_distance > $min_climb['min_distance']) {
                    /* Check the category */
                    while ((($cat+1) < count($climbs)) && 
                            (($window_altitude/$window_distance) > $climbs[$cat+1]['min_gradient'])) {
                        if ($cat == -1) {
                            /* TODO: Go through and find the minimum height */
                            $climb['bottom']       = $records[$back]->timestamp;
                            $climb['min_altitude'] = $records[$back];
                        }

                        $cat++;
                        /* We are in a climb */
                        echo 'We are more than a '.$cat.$climbs[$cat]['cat']." climb<br />\n";
                        echo 'Gradient = '.($window_altitude/$window_distance).' min_gradient = '.$climbs[$cat]['min_gradient'].' front = '.$front."<br />\n";
                    }

                    /* Check we are in the same category */
                    if ($cat != -1 && 
                            (($window_altitude/$window_distance) > $climbs[$cat]['min_gradient'])) {
                        echo 'Still climbing at point '.$front."<br />\n";

                        /* Get the max gradient */
                        if ($climb['gradient_max'] < $records[$front]->gradient) {
                            $climb['gradient_max'] = $records[$front]->gradient;
                        }
                        /* TODO: Need to get the delta in the altitude */
                        $climb['total_climbed'] += 1; //$records[$front]->altitude;
                    } else if ($cat != -1) {
                        echo 'We have finished climbing the '.$climbs[$cat]['cat']."<br />\n";

                        /* Need to go back and find the maximum altitude */
                        $climb['top']            = $records[$front]->timestamp;
                        $climb['max_altitude']   = $records[$back];
                        $climb['total_distance'] = 0;
                        $climb['cat']            = $cat;

                        /* TODO: Insert it into the database */
                        print_r($climb);

                        $cat = -1;
                    }

                    /* Move the back up */
                    while ($window_distance > $min_climb['min_distance'] && $back < count($records)) {
                        $window_distance -= $records[$back]->delta_distance * 1000;
                        $window_altitude -= $records[$back]->delta_altitude;
                        $back++;
                        //echo 'back = '.$back.' front = '.$front." distance = $window_distance, altitude = $window_altitude, min_distance = ".$min_climb['min_distance']."<br />\n";
                    }
                }
            }

            /*
             * Bikes
             * userid
             * name
             * description
             * type, TT or Road
             * weight
             * picture?
             * Assign a bike to an exercise session at creation time?
             */

            unset($records);

            exec('/usr/bin/fitdecode -l '.$upload['tmp_name'], $xml_laps);
            $xml_laps = implode("\n", $xml_laps);
            $laps     = parseLaps($xml_laps);
            $lap_num = 1;
            foreach($laps as $lap) {
                $ftime = strptime($lap->start_time, '%FT%T%z');
                $start_epoch = mktime($ftime['tm_hour'],
                                      $ftime['tm_min'],
                                      $ftime['tm_sec'],
                                      1 ,
                                      $ftime['tm_yday'] + 1,
                                      $ftime['tm_year'] + 1900);

                $lap_start    = $start_epoch    - $session_epoch;
                $lap_duration = $lap->total_timer_time;

                $this->api->insertLap($session_timestamp,
                                      $lap_num,
                                      $lap_start,
                                      $lap->start_position_lat,
                                      $lap->start_position_long,
                                      $lap_duration,
                                      $lap->total_calories,
                                      $lap->avg_heart_rate,
                                      $lap->max_heart_rate,
                                      $lap->avg_speed,
                                      $lap->max_speed,
                                      $lap->total_ascent,
                                      $lap->total_descent,
                                      $lap->total_distance);
                $lap_num++;
            }
        }

        $view = CoreView::factory('sessionsfileupload');
        $view->addForm($form);
        $view->subTemplate = 'genericForm.tpl';
        echo $view->render();
    }
}

?>
