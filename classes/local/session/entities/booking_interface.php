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

namespace local_booking\local\session\entities;

use \local_booking\local\slot\entities\slot;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface for a booking class.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface booking_interface {

    /**
     * Loads this booking from the database.
     *
     * @return bool
     */
    public function load();

    /**
     * Saves this booking to database.
     *
     * @return bool
     */
    public function save();

    /**
     * Deletes this booking from database.
     *
     * @return bool
     */
    public function delete();

    /**
     * Persists booking confirmation to the database.
     *
     * @param string    Confirmation message
     * @return bool
     */
    public function confirm(string $confirmationmsg);

    /**
     * Get the booking id for the booking.
     *
     * @return int
     */
    public function get_id();

    /**
     * Get the course id for the booking.
     *
     * @return int
     */
    public function get_courseid();

    /**
     * Get the course exercise id for the booking.
     *
     * @return int
     */
    public function get_exerciseid();

    /**
     * Get the instructor user id of the booking.
     *
     * @return int
     */
    public function get_instructorid();

    /**
     * Get the instructor name of the booking.
     *
     * @return string
     */
    public function get_instructorname();

    /**
     * Get the studnet user id of the booking.
     *
     * @return int
     */
    public function get_studentid();

    /**
     * Get the studnet name of the booking.
     *
     * @return string
     */
    public function get_studentname();

    /**
     * Get the slot object of booking.
     *
     * @return slot
     */
    public function get_slot();

    /**
     * Get the status of the booking Confirmed or Tentative.
     *
     * @return string
     */
    public function confirmed();

    /**
     * Get the booking active status.
     *
     * @return bool
     */
    public function active();

    /**
     * Get the date timestamp of the booking.
     *
     * @return int
     */
    public function get_bookingdate();

    /**
     * Get the date of the last booked session
     *
     * @param int $courseid
     * @param int $userid
     * @param int $isinstructor
     */
    public static function get_last_session(int $courseid, int $userid, bool $isinstructor = false);

    /**
     * Get the last booking date associated
     * with the course and exercise id for the student.
     *
     * @param int $courseid      The associated course
     * @param int $studentid     The student id conducted the session
     * @param int $exerciseid    The exercise id for the session
     * @return DateTime $exercisedate The date of last session for that exercise     */
    public static function get_exercise_date(int $courseid, int $studentid, int $exerciseid);

    /**
     * Set the course  id for the booking.
     *
     * @param int
     */
    public function set_courseid(int $courseid);

    /**
     * Set the course exercise id for the booking.
     *
     * @param int
     */
    public function set_exerciseid(int $exerciseid);

    /**
     * Set the instructor user id of the booking.
     *
     * @param int
     */
    public function set_instructorid(int $instructorid);

    /**
     * Set the instructor name of the booking.
     *
     * @param string
     */
    public function set_instructorname(string $instructorname);

    /**
     * Set the studnet user id of the booking.
     *
     * @param int
     */
    public function set_studentid(int $studentid);

    /**
     * Set the studnet name of the booking.
     *
     * @param string
     */
    public function set_studentname(string $studentname);

    /**
     * Set the slot object of booking.
     *
     * @param slot
     */
    public function set_slot(slot $slot);

    /**
     * Set the date timestamp of the booking.
     *
     * @param int
     */
    public function set_bookingdate(int $bookingdate);
}
