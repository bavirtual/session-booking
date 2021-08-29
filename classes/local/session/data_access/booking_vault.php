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

namespace local_booking\local\session\data_access;

use local_booking\local\session\entities\booking;

class booking_vault implements booking_vault_interface {

    /** Bookings table name for the persistent. */
    const DB_BOOKINGS = 'local_booking_sessions';

    /** Availability Slots table name for the persistent. */
    const DB_SLOTS = 'local_booking_slots';

    /**
     * get booked sessions for the instructor
     *
     * @param bool                   $userid of the student in the booking.
     * @return array {Object}
     */
    public function get_bookings(bool $oldestfirst = false) {
        global $DB, $USER;

        $sql = 'SELECT b.id, b.userid, b.studentid, b.exerciseid, b.slotid, b.confirmed, b.timemodified
                FROM {' . static::DB_BOOKINGS. '} b
                INNER JOIN {' . static::DB_SLOTS . '} s on s.id = b.slotid
                WHERE b.userid = ' . $USER->id . '
                AND b.active = 1' .
                ($oldestfirst ? ' ORDER BY s.starttime' : '');

        return $DB->get_records_sql($sql);
    }

    /**
     * get booked sessions for a specific student
     *
     * @param int $userid
     * @return Object
     */
    public function get_booking($bookingid) {
        global $DB;

        return $DB->get_records(static::DB_BOOKINGS, ['id' => $bookingid]);
    }

    /**
     * get booked sessions for a specific student
     *
     * @param int $userid
     * @return Object
     */
    public function get_student_booking($userid) {
        global $DB;

        return $DB->get_records(static::DB_BOOKINGS, ['studentid' => $userid, 'active' => '1']);
    }

    /**
     * remove all bookings for a user for a
     *
     * @param string $username The username.
     * @return bool
     */
    public function set_booking_inactive($studentid, $exerciseid) {
        global $DB;

        $sql = 'UPDATE {' . static::DB_BOOKINGS . '}
                SET active = 0
                WHERE studentid = ' . $studentid . '
                AND exerciseid = ' . $exerciseid;

        return $DB->execute($sql);
    }
    /**
     * remove all bookings for a user for a
     *
     * @param string $username The username.
     * @return bool
     */
    public function delete_student_booking($studentid, $exerciseid) {
        global $DB;

        return $DB->delete_records(static::DB_BOOKINGS, [
            'studentid' => $studentid,
            'exerciseid'=> $exerciseid,
            'active'    => '1',
        ]);
    }

    /**
     * remove all bookings for a user for a
     *
     * @param string $username The username.
     * @return bool
     */
    public function delete_booking($bookingid) {
        global $DB;

        return $DB->delete_records(static::DB_BOOKINGS, ['id' => $bookingid]);
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
        $sessionrecord->slotid       = $booking->get_slot()->id;
        $sessionrecord->timemodified = time();

        return $DB->insert_record(static::DB_BOOKINGS, $sessionrecord);
    }

    /**
     * Confirm the passed book
     *
     * @param   int                 $studentid
     * @param   int                 $exerciseid
     * @return  bool                $result
     */
    public function confirm_booking(int $studentid, int $exerciseid) {
        global $DB;

        $sql = 'UPDATE {' . static::DB_BOOKINGS . '}
                SET confirmed = 1
                WHERE studentid = ' . $studentid . '
                AND exerciseid = ' . $exerciseid;

        return $DB->execute($sql);
    }

    /**
     * Get the date of the last booked session
     *
     * @param int $instructorid
     */
    public function get_last_booked_session(int $instructorid) {
        global $DB;

        $sql = 'SELECT timemodified as lastbookedsession
                FROM {' . static::DB_BOOKINGS. '}
                WHERE userid = ' . $instructorid . '
                ORDER BY timemodified DESC
                LIMIT 1';

        return $DB->get_record_sql($sql);
    }
}