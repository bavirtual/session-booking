<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Add BA Virtual specific fields and course groups
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @category   event
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

 define('LOCAL_BOOKING_BAV', 'BA Virtual');
 define('LOCAL_BOOKING_PRIMARYSIMULATOR', 'simulator');
 define('LOCAL_BOOKING_PRIMARYSIMULATORLABEL', 'Primary Flight Simulator');
 define('LOCAL_BOOKING_SECONDARYSIMULATOR', 'simulator2');
 define('LOCAL_BOOKING_SECONDARYSIMULATORLABEL', 'Secondary Flight Simulator');
 define('LOCAL_BOOKING_SUPPORTEDSIMULATORS', 'MSFS XP11 P3D5 P3D4 FSX');
 define('LOCAL_BOOKING_DEFAULTSIMULATOR', 'MSFS');
 define('LOCAL_BOOKING_CALLSIGN', 'callsign');
 define('LOCAL_BOOKING_CALLSIGNLABEL', 'Callsign');

/**
 * Add BAV custom fields.
 *
 * @return bool
 */
function xmldb_local_booking_install() {
    global $DB;

    // Look for BAV category and add to the end if doesn't exist
    $bavcategoryid = 0;
    $sortorder = 0;
    $bavcategory = $DB->get_record('user_info_category', array('name'=>LOCAL_BOOKING_BAV));
    if (empty($bavcategory)) {
        // get next sort order
        $categories = $DB->get_records('user_info_category', null, 'sortorder DESC', '*', 0, 1);
        $lastcategory = array_shift($categories);
        $sortorder = $lastcategory->sortorder + 1;

        // insert BAV category
        $bavcategoryobj = new \stdClass();
        $bavcategoryobj->name       = LOCAL_BOOKING_BAV;
        $bavcategoryobj->sortorder  = $sortorder;

        $bavcategoryid = $DB->insert_record('user_info_category', $bavcategoryobj);
    }

    // get next sort order
    $customfields = $DB->get_records('user_info_field', null, 'sortorder DESC', '*', 0, 1);
    $lastcustomfield = array_shift($customfields);
    $fieldsortorder = !empty($lastcustomfield) ? $lastcustomfield->sortorder + 1 : 1;

    // Add primary simulator field under the BAV category if doesn't exist
    $primarysimfield = $DB->get_record('user_info_field', array('shortname'=>LOCAL_BOOKING_PRIMARYSIMULATOR));
    if (empty($primarysimfield)) {

        // insert BAV category
        $primarysimobj = new \stdClass();
        $primarysimobj->shortname   = LOCAL_BOOKING_PRIMARYSIMULATOR;
        $primarysimobj->name        = LOCAL_BOOKING_PRIMARYSIMULATORLABEL;
        $primarysimobj->datatype    = 'menu';
        $primarysimobj->categoryid  = $bavcategoryid;
        $primarysimobj->sortorder   = $fieldsortorder;
        $primarysimobj->defaultdata = LOCAL_BOOKING_DEFAULTSIMULATOR;

        $bavcategoryid = $DB->insert_record('user_info_field', $primarysimobj);
        $fieldsortorder++;

    } else { $fieldsortorder = $primarysimfield->sortorder + 1 ;}

    // Add secondary simulator field under the BAV category if doesn't exist
    $secondarysimfield = $DB->get_record('user_info_field', array('shortname'=>LOCAL_BOOKING_SECONDARYSIMULATOR));
    if (empty($secondarysimfield)) {

        // insert BAV category
        $secondarysimobj = new \stdClass();
        $secondarysimobj->shortname     = LOCAL_BOOKING_SECONDARYSIMULATOR;
        $secondarysimobj->name          = LOCAL_BOOKING_SECONDARYSIMULATORLABEL;
        $secondarysimobj->datatype      = 'menu';
        $secondarysimobj->categoryid    = $bavcategoryid;
        $secondarysimobj->sortorder     = $fieldsortorder;
        // $secondarysimobj->defaultdata   = '';

        $bavcategoryid = $DB->insert_record('user_info_field', $secondarysimobj);
        $fieldsortorder++;

    } else { $fieldsortorder = $secondarysimfield->sortorder + 1 ;}

    // Add callsign field if doesn't exist
    $callsignfield = $DB->get_record('user_info_field', array('shortname'=>LOCAL_BOOKING_CALLSIGN));
    if (empty($callsignfield)) {

        // insert BAV category
        $callsignfieldobj = new \stdClass();
        $callsignfieldobj->shortname     = LOCAL_BOOKING_CALLSIGN;
        $callsignfieldobj->name          = LOCAL_BOOKING_CALLSIGNLABEL;
        $callsignfieldobj->datatype      = 'text';
        $callsignfieldobj->categoryid    = $bavcategoryid;
        $callsignfieldobj->sortorder     = $fieldsortorder;
        // $callsignfieldobj->defaultdata   = '';

        $bavcategoryid = $DB->insert_record('user_info_field', $callsignfieldobj);
    }

    return true;
}
