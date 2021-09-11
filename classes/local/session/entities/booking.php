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

use local_booking\local\session\data_access\booking_vault;
use \local_booking\local\slot\entities\slot;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a course exercise booking.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking implements booking_interface {

    /**
     * @var int $courseid The course id of this bookng.
     */
    protected $courseid;

    /**
     * @var int $exercise The course exercise id of this bookng.
     */
    protected $exercisid;

    /**
     * @var int $instructorid The instructor user id of this booking.
     */
    protected $instructorid;

    /**
     * @var int $studentid The user id of the student of this booking.
     */
    protected $studentid;

    /**
     * @var slot $slot The booked slot.
     */
    protected $slot;

    /**
     * @var bool $confirmed The booking is confirmed.
     */
    protected $confirmed;

    /**
     * @var int $bookingdate The date timestamp of this booking.
     */
    protected $bookingdate;

    /**
     * Constructor.
     *
     * @param int       $exerciseid     The exercise id associated with the booking.
     * @param stdClass  $slot           The slot associated with this booking.
     * @param int       $studentid      The student id associated with this booking..
     * @param string    studentname     The student name.
     * @param int       $instructorid   The instructor id who made this booking.
     * @param string    $instructorname The instructor name.
     * @param bool      $confirmed      The confimration status of this booking.
     * @param int       $bookingdate    The booking timestamp.
     */
    public function __construct(
        $courseid,
        $exerciseid,
        $slot,
        $studentid,
        $bookingdate,
        $studentname    = '',
        $instructorid   = 0,
        $instructorname = '',
        $confirmed      = false
        ) {
        $this->courseid         = $courseid;
        $this->exerciseid       = $exerciseid;
        $this->slot             = $slot;
        $this->studentid        = $studentid;
        $this->bookingdate      = $bookingdate;
        $this->studentname      = $studentname;
        $this->instructorid     = $instructorid;
        $this->instructorname   = $instructorname;
        $this->confirmed        = $confirmed;
    }

    // Getter functions
    /**
     * Get the course id for the booking.
     *
     * @return int
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Get the exercis id for the booking.
     *
     * @return int
     */
    public function get_exerciseid() {
        return $this->exerciseid;
    }

    /**
     * Get the slot id for the booking.
     *
     * @return slot
     */
    public function get_slot() {
        return $this->slot;
    }

    /**
     * Get the instructor id for the booking.
     *
     * @return int
     */
    public function get_instructorid() {
        return $this->instructorid;
    }

    /**
     * Get the instructor name for the booking.
     *
     * @return string
     */
    public function get_instructorname() {
        return get_fullusername($this->instructorid);
    }

    /**
     * Get the student id for the booking.
     *
     * @return int
     */
    public function get_studentid() {
        return $this->studentid;
    }

    /**
     * Get the student name for the booking.
     *
     * @return string
     */
    public function get_studentname() {
        return get_fullusername($this->studentid);
    }

    /**
     * Get the booking confirmation.
     *
     * @return bool
     */
    public function confirmed() {
        return $this->confirmed;
    }

    /**
     * Get the booking date timestamp for the booking.
     *
     * @return int
     */
    public function get_bookingdate() {
        return $this->bookingdate;
    }

    /**
     * Get the booking date associated
     * with the exercise id.
     *
     * @return int
     */
    public function get_booked_exercise_date() {
        $vault = new booking_vault();

        $bookeddate = $vault->get_exercise_date($this->studentid, $this->exerciseid);

        return $bookeddate;
    }

    // Setter functions
    /**
     * Set the course exercise id for the booking.
     *
     * @return int
     */
    public function set_courseid(int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Set the course exercise id for the booking.
     *
     * @return int
     */
    public function set_exerciseid(int $exerciseid) {
        $this->exerciseid = $exerciseid;
    }

    /**
     * Set the id of booked slot.
     *
     * @return slot
     */
    public function set_slot(slot $slot) {
        $this->slot = $slot;
    }

    /**
     * Set the instructor user id of the booking.
     *
     * @return int
     */
    public function set_instructorid(int $instructorid) {
        $this->instructorid = $instructorid;
    }

    /**
     * Set the instructor name of the booking.
     *
     * @return string
     */
    public function set_instructorname(string $instructorname) {
        $this->instructorname = $instructorname;
    }

    /**
     * Set the studnet user id of the booking.
     *
     * @return int
     */
    public function set_studentid(int $studentid) {
        $this->studentid = $studentid;
    }

    /**
     * Set the studnet name of the booking.
     *
     * @return string
     */
    public function set_studentname(string $studentname) {
        $this->studentname = $studentname;
    }

    /**
     * Set the date array of the booking.
     *
     * @return array
     */
    public function set_bookingdate(int $bookingdate) {
        $this->bookingdate = $bookingdate;
    }
}
