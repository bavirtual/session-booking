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
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/booking/lib.php');

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_booking_external extends external_api {

    /**
     * Retrieve instructor's booking.
     *
     * @param int $courseid The course id for context.
     * @param int $categoryid The category id for context.
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function get_mybookings($courseid, $categoryid) {
        global $PAGE;

        // Parameter validation.
        $params = self::validate_parameters(self::get_mybookings_parameters(), array(
                'courseid' => $courseid,
                'categoryid' => $categoryid,
                )
            );

        $context = \context_course::instance($courseid);
        self::validate_context($context);
        $PAGE->set_url('/local/booking/');

        list($data, $template) = get_bookings_view($courseid, $categoryid);

        return $data;
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     * @since Moodle 2.5
     */
    public static function get_mybookings_parameters() {
        return new external_function_parameters(
            array(
                'courseid'  => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'categoryid'   => new external_value(PARAM_INT, 'The category id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function get_mybookings_returns() {
        return \local_booking\external\bookings_exporter::get_read_structure();
    }
}
