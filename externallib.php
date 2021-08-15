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

use local_booking\local\session\data_access\booking_vault;
use local_booking\local\session\entities\booking;
use local_availability\local\slot\entities\slot;

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

    // Availability slots table name for.
    const DB_SLOTS = 'local_availability_slots';

    /**
     * Save booked slots. Delete existing ones for the user then update
     * any existing slots if applicable with slot values
     *
     * @param {object} $bookedslot Object containing booked slots.
     * @param int $slots A list of slots to create.
     * @param int $slots A list of slots to create.
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function save_booking($bookedslot, $exerciseid, $studentid, $slot) {
        global $DB, $COURSE, $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::save_booking_parameters(), array(
                'bookedslot' => $bookedslot,
                'exerciseid' => $exerciseid,
                'userid'     => $studentid
                )
            );

        $vault = new booking_vault();
        $warnings = array();

        $transaction = $DB->start_delegated_transaction();

        // remove all week's slots for the user to avoid updates
        $result = $vault->delete_booking($studentid);

        // add booking to the database.
        if ($result) {
            $result = $vault->save_booking(new booking($exerciseid, $slot, $studentid));
        }

        // update existing availability slots.
        if ($result) {
            $slotobj = new slot(
                $studentid,
                $COURSE->id,
                $slot[0],
                $slot[1],
                $slot[2],
                $slot[3],
                'tentative',
                $exerciseid,
                $USER->id
            );
            $result = $DB->insert_record(self::DB_SLOTS, $slotobj);
        }

        // send emails to both student and instructor

        if ($result) {
            $transaction->allow_commit();
            \core\notification::success(get_string('bookingsavesuccess', 'local_booking'));
        } else {
            $transaction->rollback();
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
                'bookedslot'  => new external_value(PARAM_RAW, 'The booked slot object array', VALUE_DEFAULT),
                'exerciseid'  => new external_value(PARAM_INT, 'The exercise id', VALUE_DEFAULT),
                'userid'      => new external_value(PARAM_INT, 'The student id', VALUE_DEFAULT),
                'slotid'      => new external_value(PARAM_INT, 'The availability slot id', VALUE_DEFAULT),
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
