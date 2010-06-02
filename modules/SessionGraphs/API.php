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

require_once('core/ModuleAPI.php');

class ModuleSessionGraphsAPI extends CoreModuleAPI {
	static private $instance = null;
	/**
	 * Returns the singleton ModuleSessionGraphsAPI
	 *
	 * @return ModuleSessionGraphsAPI
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
	
    function getSessionDataField($session_date, $field) {
        $valid_fields = array('distance','speed','heartrate',
                              'altitude','power','temperature',
                              'cadence');

        // Make sure field is a valid field
        if (!in_array($field, $valid_fields)) {
            return;
        }

    	// Get time in seconds since the start of the session
        $sql = 'SELECT 
                    (extract(EPOCH from "time") * 1000) AS "time",
                    '.$field.'
                FROM 
                    t_exercise_data
                WHERE 
                    userid       = :userid  AND
                    session_date = :session_date
                ORDER BY
                    "time"     DESC';
        $stmt = $this->dbQueries->dbh->prepare($sql);

        $stmt->bindParam(':userid',       $_SESSION['userid'], PDO::PARAM_STR);
        $stmt->bindParam(':session_date', $session_date,       PDO::PARAM_STR);

        $stmt->execute();

        /* TODO: Do this in a more generic way */
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $rows = array();
        foreach ($data as $row) {
            for ($i = 0; $i < count($row); $i++) {
                $myrow[$i] = doubleval($row[$i]);
            }
            $rows[] = $myrow;
        }
        return $rows;
    }


    function getGPXData($session_date) {
    	// Get time in seconds since the start of the session
        $sql = 'SELECT 
                    latitude as lat,
                    longitude as lon
                FROM 
                    t_exercise_data
                WHERE 
                    userid       = :userid  AND
                    session_date = :session_date
                ORDER BY
                    "time"     DESC';
        $stmt = $this->dbQueries->dbh->prepare($sql);

        $stmt->bindParam(':userid',       $_SESSION['userid'], PDO::PARAM_STR);
        $stmt->bindParam(':session_date', $session_date,       PDO::PARAM_STR);

        $stmt->execute();

        return $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>
