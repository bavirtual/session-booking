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
 define('LOCAL_BOOKING_SUPPORTEDSIMULATORS', 'MSFS\nXP11\nP3D5\r\nP3D4\n\rFSX');
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
    $bavcategory = $DB->get_record('user_info_category', array('name'=>LOCAL_BOOKING_BAV));
    $bavcategoryid = 0;
    $sortorder = 0;
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
    } else { $bavcategoryid = $bavcategory->id; }

    // get next sort order
    $customfields = $DB->get_records('user_info_field', null, 'sortorder DESC', '*', 0, 1);
    $lastcustomfield = array_shift($customfields);
    $fieldsortorder = !empty($lastcustomfield) ? $lastcustomfield->sortorder + 1 : 1;

    // Add primary simulator field under the BAV category if doesn't exist
    $primarysimfield = $DB->get_record('user_info_field', array('shortname'=>LOCAL_BOOKING_PRIMARYSIMULATOR));
    if (empty($primarysimfield)) {

        // insert BAV category
        $primarysimobj = new \stdClass();
        $primarysimobj->name        = LOCAL_BOOKING_PRIMARYSIMULATORLABEL;
        $primarysimobj->shortname   = LOCAL_BOOKING_PRIMARYSIMULATOR;
        $primarysimobj->description = '';
        $primarysimobj->descriptionformat = 1;
        $primarysimobj->visible     = 2;
        $primarysimobj->datatype    = 'menu';
        $primarysimobj->categoryid  = $bavcategoryid;
        $primarysimobj->sortorder   = $fieldsortorder;
        $primarysimobj->defaultdata = LOCAL_BOOKING_DEFAULTSIMULATOR;
        $primarysimobj->param1      = LOCAL_BOOKING_SUPPORTEDSIMULATORS;

        $DB->insert_record('user_info_field', $primarysimobj);
        $fieldsortorder++;

    } else { $fieldsortorder = $primarysimfield->sortorder + 1 ;}

    // Add secondary simulator field under the BAV category if doesn't exist
    $secondarysimfield = $DB->get_record('user_info_field', array('shortname'=>LOCAL_BOOKING_SECONDARYSIMULATOR));
    if (empty($secondarysimfield)) {

        // insert BAV category
        $secondarysimobj = new \stdClass();
        $secondarysimobj->name          = LOCAL_BOOKING_SECONDARYSIMULATORLABEL;
        $secondarysimobj->shortname     = LOCAL_BOOKING_SECONDARYSIMULATOR;
        $secondarysimobj->description   = '';
        $secondarysimobj->descriptionformat = 1;
        $secondarysimobj->visible       = 2;
        $secondarysimobj->datatype      = 'menu';
        $secondarysimobj->categoryid    = $bavcategoryid;
        $secondarysimobj->sortorder     = $fieldsortorder;
        $secondarysimobj->param1        = LOCAL_BOOKING_SUPPORTEDSIMULATORS;

        $DB->insert_record('user_info_field', $secondarysimobj);
        $fieldsortorder++;

    } else { $fieldsortorder = $secondarysimfield->sortorder + 1 ;}

    // Add callsign field if doesn't exist
    $callsignfield = $DB->get_record('user_info_field', array('shortname'=>LOCAL_BOOKING_CALLSIGN));
    if (empty($callsignfield)) {

        // insert BAV category
        $callsignfieldobj = new \stdClass();
        $callsignfieldobj->name          = LOCAL_BOOKING_CALLSIGNLABEL;
        $callsignfieldobj->shortname     = LOCAL_BOOKING_CALLSIGN;
        $callsignfieldobj->description   = '';
        $callsignfieldobj->descriptionformat = 1;
        $callsignfieldobj->visible       = 2;
        $callsignfieldobj->datatype      = 'text';
        $callsignfieldobj->categoryid    = $bavcategoryid;
        $callsignfieldobj->sortorder     = $fieldsortorder;

        $bavcategoryid = $DB->insert_record('user_info_field', $callsignfieldobj);
    }

    return true;
}
