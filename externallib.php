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

use local_booking\local\slot\data_access\slot_vault;
use local_booking\local\slot\entities\slot;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_booking_external extends external_api {

    // Availability slots table name for.
    const DB_SLOTS = 'local_booking_slots';

    /**
     * Retrieve instructor's booking.
     *
     * @param int $courseid The course id for context.
     * @param int $categoryid The category id for context.
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function get_bookings_view($courseid) {
        global $PAGE;

        // Parameter validation.
        $params = self::validate_parameters(self::get_bookings_view_parameters(), array(
                'courseid' => $courseid,
                )
            );

        $context = \context_course::instance($courseid);
        self::validate_context($context);
        $PAGE->set_url('/local/booking/');

        list($data, $template) = get_bookings_view($courseid);

        return $data;
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     * @since Moodle 2.5
     */
    public static function get_bookings_view_parameters() {
        return new external_function_parameters(
            array(
                'courseid'  => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function get_bookings_view_returns() {
        return \local_booking\external\bookings_exporter::get_read_structure();
    }

    /**
     * Save booked slots. Delete existing ones for the user then update
     * any existing slots if applicable with slot values
     *
     * @param {object} $bookedslot array containing booked slots.
     * @param int $exerciseid The exercise the session is for.
     * @param int $studentid The student id assocaited with the slot.
     * @param int $refslotid The session slot associated.
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function save_booking($slottobook, $courseid, $exerciseid, $studentid) {

        // Parameter validation.
        $params = self::validate_parameters(self::save_booking_parameters(), array(
                'bookedslot' => $slottobook,
                'courseid'   => $courseid,
                'exerciseid' => $exerciseid,
                'studentid'  => $studentid
                )
            );

        $warnings = array();

        return array(
            'result' => save_booking($params),
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     * @since Moodle 2.5
     */
    public static function save_booking_parameters() {
        return new external_function_parameters(
            array(
                'bookedslot'  => new external_single_structure(
                        array(
                            'starttime' => new external_value(PARAM_INT, 'booked slot start time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'endtime' => new external_value(PARAM_INT, 'booked slot end time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'year' => new external_value(PARAM_INT, 'booked slot year', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'week' => new external_value(PARAM_INT, 'booked slot week', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                        ), 'booking'),
                'courseid'    => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'exerciseid'  => new external_value(PARAM_INT, 'The exercise id', VALUE_DEFAULT),
                'studentid'   => new external_value(PARAM_INT, 'The student id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function save_booking_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Cancel an instructor's booking.
     *
     * @param int $bookingid
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function cancel_booking($bookingid) {

        // Parameter validation.
        $params = self::validate_parameters(self::cancel_booking_parameters(), array('bookingid' => $bookingid,)
        );

        $warnings = array();
        $result = cancel_booking($bookingid);

        return array(
            'result' => $result,
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function cancel_booking_parameters() {
        return new external_function_parameters(array(
                'bookingid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function cancel_booking_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Get data for the weekly calendar view.
     *
     * @param   int     $year       The year to be shown
     * @param   int     $week       The week to be shown
     * @param   int     $time       The timestamp of the first day in the week to be shown
     * @param   int     $courseid   The course to be included
     * @param   int     $categoryid The category to be included
     * @param   string  $action     The action to be pefromed if in booking view
     * @param   string  $view       The view to be displayed if user or all
     * @param   int     $studentid  The student id the action is performed on
     * @param   int     $exercise   The exercise id the action is associated with
     * @return  array
     */
    public static function get_weekly_view($year, $week, $time, $courseid, $categoryid, $action, $view, $studentid, $exerciseid) {
        global $USER, $PAGE;

        // Parameter validation.
        $params = self::validate_parameters(self::get_weekly_view_parameters(), [
            'year'      => $year,
            'week'      => $week,
            'time'      => $time,
            'courseid'  => $courseid,
            'categoryid'=> $categoryid,
            'action'    => $action,
            'view'      => $view,
            'studentid' => $studentid,
            'exerciseid'=> $exerciseid,
        ]);

        $context = \context_user::instance($USER->id);
        self::validate_context($context);
        $PAGE->set_url('/local/booking/');

        $calendar = \calendar_information::create($time, $params['courseid'], $params['categoryid']);
        self::validate_context($calendar->context);

        $actiondata = [
            'action'    => $action,
            'studentid' => $studentid == null ? 0 : $studentid,
            'exerciseid'=> $exerciseid == null ? 0 : $exerciseid,
        ];

        list($data, $template) = get_weekly_view($calendar, $actiondata, $view);

        return $data;
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function get_weekly_view_parameters() {
        return new external_function_parameters(
            [
                'year' => new external_value(PARAM_INT, 'Year to be viewed', VALUE_REQUIRED),
                'week' => new external_value(PARAM_INT, 'Week to be viewed', VALUE_REQUIRED),
                'time' => new external_value(PARAM_INT, 'Timestamp of the first day of the week to be viewed', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'Course being viewed', VALUE_DEFAULT, SITEID, NULL_ALLOWED),
                'categoryid' => new external_value(PARAM_INT, 'Category being viewed', VALUE_DEFAULT, null, NULL_ALLOWED),
                'action' => new external_value(PARAM_RAW, 'The action being performed view or book', VALUE_DEFAULT, 'view', NULL_ALLOWED),
                'view' => new external_value(PARAM_RAW, 'The action being performed view or book', VALUE_DEFAULT, 'view', NULL_ALLOWED),
                'studentid' => new external_value(PARAM_INT, 'The user id the slots belongs to', VALUE_DEFAULT, 0, NULL_ALLOWED),
                'exerciseid' => new external_value(PARAM_INT, 'The exercise id the slots belongs to', VALUE_DEFAULT, 0, NULL_ALLOWED),
            ]
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     */
    public static function get_weekly_view_returns() {
        return \local_booking\external\week_exporter::get_read_structure();
    }

    /**
     * Save availability slot.
     *
     * @param array $slots A list of slots to create.
     * @param int $courseid the course id associated with the slot.
     * @param int $year the year in which the slot occur.
     * @param int $week the week in which the slot occur.
     * @return array array of slots created.
     * @since Moodle 2.5
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function save_slots($slots, $courseid, $year, $week) {
        global $DB;

        // Parameter validation.
        $params = self::validate_parameters(self::save_slots_parameters(), array(
                'slots'     => $slots,
                'courseid'  => $courseid,
                'year'      => $year,
                'week'      => $week)
            );

        $vault = new slot_vault();
        $warnings = array();

        // remove all week's slots for the user to avoid updates
        $transaction = $DB->start_delegated_transaction();
        $result = $vault->delete_slots($courseid, $year, $week);

        if ($result) {
            foreach ($params['slots'] as $slot) {
                $slotobj = new slot(0, 0,
                    $courseid,
                    $slot['starttime'],
                    $slot['endtime'],
                    $year,
                    $week,
                );

                // add each slot.
                $result = $result && $vault->save($slotobj);
            }
        }

        if ($result) {
            $transaction->allow_commit();
            \core\notification::success(get_string('slotssavesuccess', 'local_booking'));
        } else {
            $transaction->rollback(new moodle_exception(get_string('slotssaveunable', 'local_booking')));
            \core\notification::warning(get_string('slotssaveunable', 'local_booking'));
        }

        return array(
            'result' => $result,
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function save_slots_parameters() {
        // Userid is always current user, so no need to get it from client.
        return new external_function_parameters(
            array('slots' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'starttime' => new external_value(PARAM_INT, 'slot start time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                        'endtime' => new external_value(PARAM_INT, 'slot end time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                    ), 'slot')
                ),
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'year' => new external_value(PARAM_INT, 'The slot year', VALUE_DEFAULT),
                'week' => new external_value(PARAM_INT, 'The slot week', VALUE_DEFAULT)
            )
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function save_slots_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Delete availability slot.
     *
     * @param array $events A list of slots to create.
     * @return array array of slots created.
     * @since Moodle 2.5
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function delete_slots($courseid, $year, $week) {

        // Parameter validation.
        $params = self::validate_parameters(self::delete_slots_parameters(), array(
                'courseid' => $courseid,
                'year' => $year,
                'week' => $week)
            );

        $vault = new slot_vault();
        $warnings = array();

        // remove all week's slots for the user to avoid updates
        $result = $vault->delete_slots($courseid, $year, $week);

        if ($result) {
            \core\notification::success(get_string('slotsdeletesuccess', 'local_booking'));
        } else {
            \core\notification::warning(get_string('slotsdeleteunable', 'local_booking'));
        }

        return array(
            'result' => $result,
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     * @since Moodle 2.5
     */
    public static function delete_slots_parameters() {
        // Userid is always current user, so no need to get it from client.
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'year' => new external_value(PARAM_INT, 'The slot year', VALUE_DEFAULT),
                'week' => new external_value(PARAM_INT, 'The slot week', VALUE_DEFAULT)
            )
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function delete_slots_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }
}
