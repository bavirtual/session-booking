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
 * External student booking calendar APIs
 *
 * @package    local_booking
 * @category   external
 * @copyright  2012 Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.5
 */

use local_booking\local\slot\data_access\slot_vault;
use local_booking\local\slot\entities\slot;

defined('MOODLE_INTERNAL') || die;

//require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/booking/lib.php');

/**
 * Calendar external functions
 *
 * @package    local_booking
 * @category   external
 * @copyright  2012 Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.5
 */
class local_booking_external extends external_api {

    /**
     * Convert the specified dates into unix timestamps.
     *
     * @param   array $datetimes Array of arrays containing date time details, each in the format:
     *           ['year' => a, 'week' => b, 'day' => c,
     *            'hour' => d (optional), 'minute' => e (optional), 'key' => 'x' (optional)]
     * @return  array Provided array of dates converted to unix timestamps
     * @throws moodle_exception If one or more of the dates provided does not convert to a valid timestamp.
     */
    public static function get_timestamps($datetimes) {
        $params = self::validate_parameters(self::get_timestamps_parameters(), ['data' => $datetimes]);

        $type = \core_calendar\type_factory::get_calendar_instance();
        $timestamps = ['timestamps' => []];

        foreach ($params['data'] as $key => $datetime) {
            $hour = $datetime['hour'] ?? 0;
            $minute = $datetime['minute'] ?? 0;

            try {
                $timestamp = $type->convert_to_timestamp(
                    $datetime['year'], $datetime['week'], $datetime['day'], $hour, $minute);

                $timestamps['timestamps'][] = [
                    'key' => $datetime['key'] ?? $key,
                    'timestamp' => $timestamp,
                ];

            } catch (Exception $e) {
                throw new moodle_exception('One or more of the dates provided were invalid');
            }
        }

        return $timestamps;
    }

    /**
     * Describes the parameters for get_timestamps.
     *
     * @return external_function_parameters
     */
    public static function get_timestamps_parameters() {
        return new external_function_parameters ([
            'data' => new external_multiple_structure(
                new external_single_structure(
                    [
                        'key' => new external_value(PARAM_ALPHANUMEXT, 'key', VALUE_OPTIONAL),
                        'year' => new external_value(PARAM_INT, 'year'),
                        'week' => new external_value(PARAM_INT, 'week'),
                        'day' => new external_value(PARAM_INT, 'day'),
                        'hour' => new external_value(PARAM_INT, 'hour', VALUE_OPTIONAL),
                        'minute' => new external_value(PARAM_INT, 'minute', VALUE_OPTIONAL),
                    ]
                )
            )
        ]);
    }

    /**
     * Describes the timestamps return format.
     *
     * @return external_single_structure
     */
    public static function get_timestamps_returns() {
        return new external_single_structure(
            [
                'timestamps' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'key' => new external_value(PARAM_ALPHANUMEXT, 'Timestamp key'),
                            'timestamp' => new external_value(PARAM_INT, 'Unix timestamp'),
                        ]
                    )
                )
            ]
        );
    }

    /**
     * Get data for the weekly calendar view.
     *
     * @param   int     $year The year to be shown
     * @param   int     $week The week to be shown
     * @param   int     $time The timestamp of the first day in the week to be shown
     * @param   int     $courseid The course to be included
     * @param   int     $categoryid The category to be included
     * @param   bool    $includenavigation Whether to include navigation
     * @param   bool    $mini Whether to return the mini week view or not
     * @param   int     $day The day we want to keep as the current day
     * @return  array
     */
    public static function get_weekly_view($year, $week, $time, $courseid, $categoryid, $includenavigation, $mini) {
        global $USER, $PAGE;

        // Parameter validation.
        $params = self::validate_parameters(self::get_weekly_view_parameters(), [
            'year' => $year,
            'week' => $week,
            'time' => $time,
            'courseid' => $courseid,
            'categoryid' => $categoryid,
            'includenavigation' => $includenavigation,
            'mini' => $mini,
        ]);

        $context = \context_user::instance($USER->id);
        self::validate_context($context);
        $PAGE->set_url('/local/booking/');

        $type = \core_calendar\type_factory::get_calendar_instance();

        $calendar = \calendar_information::create($time, $params['courseid'], $params['categoryid']);
        self::validate_context($calendar->context);
        $view = "week";

        list($data, $template) = get_weekly_view($calendar, $view, $params['includenavigation']);

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
                'includenavigation' => new external_value(PARAM_BOOL, 'Whether to show course navigation', VALUE_DEFAULT, true, NULL_ALLOWED),
                'mini' => new external_value(PARAM_BOOL, 'Whether to return the mini week view or not', VALUE_DEFAULT, false, NULL_ALLOWED),
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
     * Save booking slot.
     *
     * @param array $events A list of slots to create.
     * @return array array of slots created.
     * @since Moodle 2.5
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function save_slots($slots, $courseid, $year, $week) {

        // Parameter validation.
        $params = self::validate_parameters(self::save_slots_parameters(), array(
                'slots' => $slots,
                'courseid' => $courseid,
                'year' => $year,
                'week' => $week)
            );

        $vault = new slot_vault();
        $warnings = array();

        // remove all week's slots for the user to avoid updates
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
            \core\notification::success(get_string('slotssavesuccess', 'local_booking'));
        } else {
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
     * @since Moodle 2.5
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
     * Delete booking slot.
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
