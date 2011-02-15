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
require_once "Module/Sessions/FITElement.php";

class FITLap extends FITElement {
    var $timestamp;
    var $start_time;
    var $start_position_lat;
    var $start_position_long;
    var $end_position_lat;
    var $end_position_long;
    var $total_elapsed_time;
    var $total_timer_time;
    var $total_distance;
    var $swc_lat;
    var $swc_long;
    var $message_index;
    var $total_calories;
    var $avg_speed;
    var $max_speed;
    var $total_ascent;
    var $total_descent;
    var $event;
    var $event_type;
    var $avg_heart_rate;
    var $max_heart_rate;
    var $intensity;
    var $lap_trigger;
    var $sport;

}


function parseLaps($xml_laps) {
    $lap_array = array();

    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $xml_laps, $values, $tags);
    xml_parser_free($parser);

    // loop through the structures
    foreach ($tags as $key => $value) {
        if ($key == "lap") {
            $molranges = $value;
            // each contiguous pair of array entries are the 
            // lower and upper range for each molecule definition
            for ($i=0; $i < count($molranges); $i+=2) {
                $offset = $molranges[$i] + 1;
                $len = $molranges[$i + 1] - $offset;
                $lap_array[] = parseLap(array_slice($values, $offset, $len));
            }
        } else {
            continue;
        }
    }
    return $lap_array;
}

function parseLap($lap_values) 
{
    for ($i=0; $i < count($lap_values); $i++) {
        $lap[$lap_values[$i]["tag"]] = $lap_values[$i]["value"];
    }
    return new FITLap($lap);
}

?>
