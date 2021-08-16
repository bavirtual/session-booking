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
 * Contains event class for displaying the week view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\session\data_access;

use local_booking\local\session\entities\booking;

class booking_vault implements booking_vault_interface {

    /** Table name for the persistent. */
    const TABLE = 'local_booking';

    /**
     * get booked sessions for the instructor
     *
     * @param string $session
     * @return bool
     */
    public function get_bookings() {
        global $DB, $USER;

        $condition = [
            'userid' => $USER->id,
        ];

        return $DB->get_records(static::TABLE, $condition);
    }

    /**
     * get booked sessions for a specific student
     *
     * @param int $userid
     * @return bool
     */
    public function get_booking($userid) {
        global $DB, $USER;

        $condition = [
            'userid' => $USER->id,
            'studentid' => $userid,
        ];

        return $DB->get_records(static::TABLE, $condition);
    }

    /**
     * remove all bookings for a user for a
     *
     * @param string $username The username.
     * @return bool
     */
    public function delete_booking($userid) {
        global $DB, $USER;

        $condition = [
            'userid' => $USER->id,
            'studentid' => $userid,
        ];

        return $DB->delete_records(static::TABLE, $condition);
    }

    /**
     * save a booking
     *
     * @param {booking} $booking
     * @return bool
     */
    public function save_booking(booking $booking) {
        global $DB, $USER;

        $sessionrecord = new \stdClass();
        $sessionrecord->userid       = $USER->id;
        $sessionrecord->studentid    = $booking->get_studentid();
        $sessionrecord->exerciseid   = $booking->get_exerciseid();
        $sessionrecord->slotid       = ($booking->get_slot())->id();
        $sessionrecord->bookingdate  = time();

        return $DB->insert_record(static::TABLE, $sessionrecord);
    }
}