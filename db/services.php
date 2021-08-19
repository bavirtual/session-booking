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
 * Core external functions and service definitions.
 *
 * The functions and services defined on this file are
 * processed and registered into the Moodle DB after any
 * install or upgrade operation. All plugins support this.
 *
 * For more information, take a look to the documentation available:
 *     - Webservices API: {@link http://docs.moodle.org/dev/Web_services_API}
 *     - External API: {@link http://docs.moodle.org/dev/External_functions_API}
 *     - Upgrade API: {@link http://docs.moodle.org/dev/Upgrade_API}
 *
 * @package    local_booking
 * @category   webservice
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'local_booking_get_mybookings_view' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'get_mybookings',
        'description' => 'Retrieve intructor active bookings',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);

$services = array(
   'Session Booking retrieve instructor active bookings web service'  => array(
        'functions' => array('local_booking_get_mybookings_view'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 1,         // if 0, then token linked to this service won't work
        'restrictedusers' => 0,
        'shortname' => 'get_mybookings',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ),
);
