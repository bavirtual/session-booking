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

use local_booking\local\session\data_access\booking_vault;
use local_booking\local\session\entities\booking;
use local_availability\local\slot\data_access\slot_vault;
use local_availability\local\slot\entities\slot;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/booking/lib.php');
require_once($CFG->dirroot . '/local/availability/lib.php');

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
    const DB_SLOTS = 'local_availability_slots';

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
    public static function save_booking($slottobook, $exerciseid, $studentid) {
        global $DB, $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::save_booking_parameters(), array(
                'bookedslot' => $slottobook,
                'exerciseid' => $exerciseid,
                'studentid'  => $studentid
                )
            );

        $result = false;
        $bookingvault = new booking_vault();
        $slotvault = new slot_vault();
        $warnings = array();
        $courseid = get_course_id($exerciseid);

        require_login($courseid, false);

        $transaction = $DB->start_delegated_transaction();

        // add a new tentatively booked slot for the student.
        $sessiondata = [
            'exercise'  => get_exercise_name($exerciseid),
            'instructor'=> get_fullusername($USER->id),
            'status'    => ucwords(get_string('statustentative', 'local_booking')),
        ];

        // remove all week's slots for the user to avoid updates first
        // add new booked slot for the user
        if ($bookingvault->delete_booking($studentid, $exerciseid)) {
            $slotobj = new slot(0,
                $studentid,
                $courseid,
                $slottobook['starttime'],
                $slottobook['endtime'],
                $slottobook['year'],
                $slottobook['week'],
                get_string('statustentative', 'local_booking'),
                get_string('bookinginfo', 'local_booking', $sessiondata)
            );
            $bookedslot = $slotvault->get_slot($slotvault->save($slotobj));

            // add new booking by the instructor.
            if (!empty($bookedslot)) {
                if ($bookingvault->save_booking(new booking($exerciseid, $bookedslot, $studentid, $slottobook['starttime']))) {
                    // send emails to both student and instructor
                    $sessiondate = new DateTime('@' . $slottobook['starttime']);
                    if (send_booking_notification($studentid, $exerciseid, $sessiondate)) {
                        $result = send_instructor_confirmation($studentid, $exerciseid, $sessiondate);
                    }
                }
            }
        }


        if ($result) {
            $transaction->allow_commit();
            \core\notification::success(get_string('bookingsavesuccess', 'local_booking'));
        } else {
            $transaction->rollback(new moodle_exception(get_string('bookingsaveunable', 'local_booking')));
            \core\notification::warning(get_string('bookingsaveunable', 'local_booking'));
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
}
